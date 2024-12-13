<?php

namespace App\Http\Controllers\Teacher;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Exports\AttendancesExport;
use App\Imports\AttendancesImport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;

class AttendanceController extends Controller
{
    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {

        return response()->json([
            'message' => 'Không có attendance nào!',
        ], 200);
    }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
        Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

        return response()->json([
            'message' => 'Lỗi không xác định!' . $th->getMessage()
        ], 500);
    }
    //  Hàm trả về thời gian bắt đầu ca học
    public function startTime($classCode)
    {
        $dateNow = Carbon::now()->toDateString();
        $sessions = Schedule::where('class_code', $classCode)
            ->whereDate('date', $dateNow)
            ->with('session')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $sessionValue = $sessions->first()->session->value ?? null;

        if (!$sessionValue || !is_string($sessionValue)) {
            return null;
        }

        $sessionData = json_decode($sessionValue, true);

        $start = $sessionData['start'] ?? null;
    
        return $start;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $teacherCode = $request->user()->user_code;
            // Giả sử user_code của giảng viên là 'TC969'
            // $teacher_code = 'TC969';

            // Lấy danh sách tất cả các lớp của giảng viên kèm theo lịch học
            $classrooms = Classroom::query()
                ->where('user_code', $teacher_code) // Lọc theo mã giảng viên
                ->with(['schedules.room', 'schedules.session']) // Load lịch học kèm thông tin phòng, ca học
                ->get(); // Lấy tất cả (nếu cần phân trang, thay `get()` bằng `paginate(10)`)

            // Kiểm tra danh sách có rỗng không
            if ($classrooms->isEmpty()) {
                return $this->handleInvalidId();
            }

            // Chỉ giữ lại các trường cần thiết
            $filteredClassrooms = $classrooms->map(function ($classroom) {
                return [
                    'id' => $classroom->id,
                    'class_code' => $classroom->class_code,
                    'class_name' => $classroom->class_name,
                    'is_active' => $classroom->is_active,
                    'subject_code' => $classroom->subject_code,
                    'schedules' => $classroom->schedules->map(function ($schedule) {
                        return [
                            'date' => $schedule->date,
                            'room' => [
                                'cate_name' => $schedule->room->cate_name ?? null, // Xử lý nếu room không tồn tại
                            ],
                            'session' => [
                                'cate_code' => $schedule->session->cate_code ?? null,
                                'cate_name' => $schedule->session->cate_name ?? null,
                                'value' => $schedule->session->value ?? null,
                            ],
                        ];
                    }),
                ];
            });

            // Trả về dữ liệu dạng JSON
            return response()->json($filteredClassrooms, 200);
        } catch (Throwable $th) {
            // Xử lý lỗi
            return $this->handleErrorNotDefine($th);
        }
    }

    public function showAttendanceByDate(Request $request, string $classCode, string $byDate)
    {
        try {
            $userCode = $request->user()->user_code;
            $byDateCopy = $byDate;
            // $date = Carbon::now()->toDateString(); // Lấy ngày hiện tại (YYYY-MM-DD)
            $attendances = Attendance::whereHas('classroom', function ($query) use ($userCode, $classCode) {
                $query->where('user_code', $userCode)->where('class_code', $classCode);
            })
                ->with([
                    'classroom' => function ($query) {
                        $query->select('class_code', 'class_name', 'subject_code', 'user_code');
                    },
                    'classroom.users' => function ($query) {
                        $query->select('full_name');
                    },
                    'classroom.subject' => function ($query) {
                        $query->select('subject_code', 'subject_name', 'semester_code');
                    },
                    'classroom.schedules.session' => function ($query) {
                        $query->select('cate_code', 'cate_name'); // Tải thêm session
                    }
                ])
                ->get(['id', 'student_code', 'class_code', 'status', 'noted', 'date']);

            $sessions = Schedule::where('class_code', $classCode)
                ->with('session')
                ->get()
                ->map(function ($session) {
                    return [
                        'session' => $session->session ? $session->session->value : null
                    ];
                });
            $sessionData = json_decode($sessions[0]['session'], true);
            $result = $attendances->groupBy('student_code')->map(function ($studentGroup) use ($byDateCopy, $sessionData) {
                $firstAttendance = $studentGroup->first();
                
                // Giữ nguyên giá trị của `byDateCopy` và trả lại nó mà không thay đổi
                return [
                    'byDate' => $byDateCopy,  // Trả về giá trị ban đầu của `byDateCopy`
                    'student_code' => $firstAttendance->student_code,
                    'full_name' => $firstAttendance->classroom->users->firstWhere('pivot.user_code', $firstAttendance->student_code)->full_name ?? null,
                    'attendance' => $studentGroup->map(function ($attendance) {
                        return [
                            'date' => Carbon::parse($attendance->date)->toDateString(),
                            'cate_name' => $attendance->classroom->schedules->firstWhere('date', Carbon::parse($attendance->date)->toDateString())->session->cate_name ?? null,
                            'status' => $attendance->status,
                            'noted' => $attendance->noted,
                        ];
                    })->values(),
                    'session' => $sessionData,
                ];
            })->filter()->values()->all();
            

            return response()->json($result, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }

    public function show(string $classCode)
{
    try {
        // Lấy danh sách lớp học và các lịch học của nó
        $attendances = Classroom::where('class_code', $classCode)
            ->with(['users', 'schedules.room', 'schedules.session', 'schedules.teacher'])
            ->get()
            ->map(function ($attendance) {
                $filteredSchedules = $attendance->schedules->map(function ($schedule) {
                    // Lấy thông tin ca học
                    $session = json_decode($schedule->session->value, true);

                    return [
                        'date' => $schedule->date,
                        'session_start' => $session['start'] ?? null, // Thời gian bắt đầu
                        'room_name' => $schedule->room->cate_name ?? null,
                        'session_name' => $schedule->session->cate_name ?? null,
                        'teacher' => $schedule->teacher->full_name ?? null,
                    ];
                });

                // Duyệt qua từng sinh viên và trả về dữ liệu điểm danh
                $attendanceRecords = $attendance->users->map(function ($user) use ($filteredSchedules, $attendance) {
                    return collect($filteredSchedules)->map(function ($schedule) use ($user, $attendance) {
                        $currentDateTime = now(); // Thời gian hiện tại
                        $scheduleDate = \Carbon\Carbon::parse($schedule['date']);
                        $sessionStartDateTime = \Carbon\Carbon::parse($schedule['date'] . ' ' . $schedule['session_start']);

                        // Tính toán thời gian giới hạn 15 phút sau khi bắt đầu
                        $timeLimit = $sessionStartDateTime->addMinutes(15);

                        // Lấy trạng thái mặc định từ attendance
                        $existingAttendance = Attendance::where('student_code', $user->user_code)
                            ->where('class_code', $attendance->class_code)
                            ->whereDate('date', $schedule['date'])
                            ->first();

                        $status = $existingAttendance ? $existingAttendance->status : 'present';

                        // Nếu ngày hôm nay:
                        if ($scheduleDate->isToday()) {
                            if (!$currentDateTime->greaterThan($timeLimit)) {
                                $status = 'pending'; // Sau thời gian giới hạn => 'pending'
                            }
                        }
                        // Nếu ngày sau ngày hôm nay:
                        if ($scheduleDate->isFuture()) {
                            $status = 'pending'; // Buổi học tương lai => null
                        }

                        return [
                            'student_code' => $user->user_code,
                            'full_name' => $user->full_name,
                            'class_code' => $attendance->class_code,
                            'date' => $schedule['date'],
                            'status' => $status,
                            'room_name' => $schedule['room_name'],
                            'session_name' => $schedule['session_name'],
                            'teacher' => $schedule['teacher'],
                        ];
                    });
                })->flatten(1);

                return $attendanceRecords;
            });

        return response()->json($attendances, 200);
    } catch (\Throwable $th) {
        return $this->handleErrorNotDefine($th);
    }
}

    public function store(StoreAttendanceRequest $request, string $classCode)
    {
        try {
            $attendances = $request->validated();
            // Log::info('Request Data:', $request->all());
            $startTime = Carbon::createFromFormat('H:i', $this->startTime($classCode));
            $currentTime = Carbon::now();

            if ($currentTime->diffInMinutes($startTime) <= 15) {
                // Kiểm tra nếu dữ liệu là mảng và có dữ liệu
                if (is_array($attendances) && count($attendances) > 0) {
                    foreach ($attendances as $atd) {
                        Attendance::create([
                            'student_code' => $atd['student_code'],
                            'class_code' => $atd['class_code'],
                            'date' => Carbon::now(),
                            'status' => $atd['status'],
                            'noted' => $atd['noted'],
                        ]);
                        // Attendance::create($atd);
                    }

                    return response()->json($attendances, 200);
                }
            } else {

                return response()->json([
                    'message' => 'Đã quá 15 phút',
                ]);
            }
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function edit(string $classCode)
    {
        try {
            $attendances = Attendance::where('class_code', $classCode)
                ->whereDate('date', Carbon::today())
                ->with('user')
                ->get()
                ->map(function ($attendance) {
                    return [
                        'student_code' => $attendance->student_code,
                        'full_name' => $attendance->user->full_name,
                        'date' => $attendance->date,
                        'status' => $attendance->status,
                        'noted' => $attendance->noted,
                    ];
                });
            if (!$attendances) {

                return $this->handleInvalidId();
            } else {

                return response()->json($attendances, 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function update(UpdateAttendanceRequest $request, string $classCode)
    {
        try {
            $userCode = $request->user()->user_code;
            $classroom = Classroom::where('user_code', $userCode)->where('class_code', $classCode)->first();
            if (!$classroom) {

                return response()->json([
                    'message' => 'Bạn không có quyền cập nhật điểm danh vào lớp này'
                ], 200);
            }
            $attendances = $request->validated();
            // Log::info('Request Data:', $request->all());
            $startTime = $this->startTime($classCode);
            $currentTime = Carbon::now(); // Lay gio hien tai
            // $currentTime = Carbon::createFromFormat('H:i', '18:00'); // Fix cung gio hien tai

            // if (1) {
            if ($currentTime->diffInMinutes($startTime) <= 15) {
                // Kiểm tra nếu dữ liệu là mảng và có dữ liệu
                if (is_array($attendances) && count($attendances) > 0) {
                    foreach ($attendances as $atd) {
                        $noted = $atd['noted'] ??  "";
                        Attendance::where('student_code', $atd['student_code'])->where('class_code', $atd['class_code'])->whereDate('date', '=', $currentTime)
                            ->update([
                                'student_code' => $atd['student_code'],
                                'class_code' => $atd['class_code'],
                                'date' => Carbon::now(),
                                'status' => $atd['status'],
                                'noted' => $noted,
                            ]);
                    }

                    return response()->json($attendances, 200);
                }
            } else {

                return response()->json([
                    'message' => 'Đã quá 15 phút',
                ]);
            }
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function showAllAttendance(Request $request, string $classCode)
    {
        try {
            $userCode = $request->user()->user_code;
            // $userCode = 'TC969';

            $attendances = Attendance::whereHas('classroom', function ($query) use ($userCode, $classCode) {
                $query->where('user_code', $userCode)->where('class_code', $classCode);
            })
                ->with([
                    'classroom' => function ($query) {
                        $query->select('class_code', 'class_name', 'subject_code', 'user_code');
                    },
                    'classroom.users' => function ($query) {
                        $query->select('full_name');
                    },
                    'classroom.subject' => function ($query) {
                        $query->select('subject_code', 'subject_name', 'semester_code');
                    },
                    'classroom.schedules.session' => function ($query) {
                        $query->select('cate_code', 'cate_name'); // Tải thêm session
                    }
                ])
                ->get(['id', 'student_code', 'class_code', 'status', 'noted', 'date']);
            // dd($attendances);
            $result = $attendances->groupBy('student_code')->map(function ($studentGroup) {
                $firstAttendance = $studentGroup->first();

                // Lấy `user_code` từ nhóm hiện tại
                $userCode = $firstAttendance->student_code;

                // Lấy `full_name` từ danh sách `users` dựa trên `user_code`
                $user = $firstAttendance->classroom->users->firstWhere('pivot.user_code', $userCode);
                $fullName = $user ? $user->full_name : null;

                // Lấy tất cả các lịch học từ lớp học
                $schedules = $firstAttendance->classroom->schedules;

                // Gộp dữ liệu điểm danh và lịch học
                $attendanceData = $studentGroup->map(function ($attendance) use ($schedules) {
                    // Lấy cate_code từ lịch học tương ứng (nếu có)
                    $schedule = $schedules->firstWhere('date', Carbon::parse($attendance->date)->toDateString());
                    return [
                        'date' => Carbon::parse($attendance->date)->toDateString(),
                        'cate_name' => $schedule->session->cate_name ?? null,
                        'status' => $attendance->status,
                        'noted' => $attendance->noted,
                    ];
                });

                $attendanceDates = $attendanceData->pluck('date')->toArray();

                // Duyệt qua các lịch học và lọc ra các lịch không có trong dữ liệu điểm danh
                $scheduleData = $schedules->filter(function ($schedule) use ($attendanceDates) {
                    return !in_array(Carbon::parse($schedule->date)->toDateString(), $attendanceDates);
                })->map(function ($schedule) {
                    return [
                        'date' => Carbon::parse($schedule->date)->toDateString(),
                        'full_name' => null,
                        'status' => null,
                        'noted' => 'Chưa điểm danh',
                    ];
                });

                // Kết hợp danh sách điểm danh và lịch học
                $finalData = $attendanceData->merge($scheduleData)->sortBy('date')->values();
                // Đếm số lần status là 'absent'
                $totalSchedule = $finalData->count();
                // dd($absentCount);
                $absentCount = $attendanceData->where('status', 'absent')->count();
                // dd($absentCount);
                return [
                    'student_code' => $userCode,
                    'full_name' => $fullName,
                    'total_schedule' => $totalSchedule,
                    'total_absent' => $absentCount,
                    'attendance' => $finalData,
                ];
            })->values()->all();


            return response()->json($result, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }

    public function exportAttendance(string $classCode)
    {
        $today = Carbon::today()->toDateString();
        $attendances = Attendance::whereHas('classroom', function ($query) {
            $query->where('class_code', $classCode);
        })
            ->with([
                'classroom' => function ($query) {
                    $query->select('class_code', 'class_name', 'subject_code', 'user_code');
                },
                'classroom.users' => function ($query) {
                    $query->select('users.user_code', 'users.full_name');
                },
                'classroom.teacher' => function ($query) {
                    $query->select('user_code', 'full_name');
                },
                'classroom.schedules.session' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
            ])
            ->get(['id', 'student_code', 'class_code', 'status', 'noted', 'date']);

        // Xử lý dữ liệu export
        $result = $attendances->groupBy('class_code')->map(function ($classGroup) use ($today) {
            $firstAttendance = $classGroup->first();
            // $attendanceDate = $classGroup->date;
            // Lấy tất cả học sinh từ lớp học
            $students = $firstAttendance->classroom->users;

            // Lọc danh sách điểm danh có ngày bằng hôm nay
            $attendanceToday = $classGroup->filter(function ($attendance) use ($today) {
                return Carbon::parse($attendance->date)->toDateString() === $today;
            });
            // Lấy tất cả các lịch học từ lớp học
            $schedules = $firstAttendance->classroom->schedules;
            // dd($classGroup);
            // Gộp dữ liệu điểm danh và danh sách học sinh
            $attendanceData = $students->map(function ($student) use ($attendanceToday, $today) {
                $studentAttendance = $attendanceToday->firstWhere('student_code', $student->user_code);
                // dd($attendanceToday);
                if ($studentAttendance) {

                    // Trả về dữ liệu attendance hiện tại
                    return [
                        'student_code' => $student->user_code,
                        'full_name' => $student->full_name,
                        'status' => $studentAttendance->status,
                        'date' => Carbon::parse($studentAttendance->date)->toDateString(),
                        'noted' => $studentAttendance->noted,
                    ];
                } else {
                    // Trả về dữ liệu mặc định nếu không có attendance
                    return [
                        'student_code' => $student->user_code,
                        'full_name' => $student->full_name,
                        'status' => null,
                        'date' => $today,
                        'noted' => 'Chưa điểm danh',
                    ];
                }
            });

            return [
                'class_code' => $firstAttendance->class_code,
                'attendance' => $attendanceData,
            ];
        })->values()->all();

        return Excel::download(new AttendancesExport($result), 'attendance.xlsx');
    }

    public function importAttendance(Request $request)
    {
        // Kiểm tra xem file có tồn tại hay không
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv', // Đảm bảo chỉ nhận file Excel
        ]);

        try {
            // Import file và xử lý qua AttendancesImport
            $import = new AttendancesImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Attendance data imported successfully.',
                'class_code' => $import->getClassCode(), // Trả về class_code đã trích xuất
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during import: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $classCode)
    {
        // try {
        //     $attendances = Attendance::where('class_code', $classCode)->first();
        //     if (!$attendances) {

        //         return $this->handleInvalidId();
        //     } else {
        //         $attendances->delete($attendances);

        //         return response()->json([
        //             'message' => 'Xoa thanh cong'
        //         ], 200);            
        //     }
        // } catch (Throwable $th) {

        //     return $this->handleErrorNotDefine($th);
        // }
    }
}
