<?php

namespace App\Http\Controllers\Student;

use App\Models\ClassroomUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\HandleTransferSchedule;
use App\Http\Requests\Schedule\ShowListScheduleCanBeTransfer;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\TransferScheduleHistory;
use App\Models\TransferScheduleTimeframe;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{



    public function handleErrorNotDefine($th)
    {
        return response()->json([
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ], 500);
    }

    public function response422($message){
        return response()->json([
            'status' => false,
            'message' => $message
        ],422);
    }

    public function response404($message){
        return response()->json([
            'status' => false,
            'message' => $message
        ],404);
    }

    // cắt tên lớp học để lấy mã khoá học được gắn trên tên lớp
    public function sliceCourseFromClasscode($class_code)
    {
        return strstr($class_code, '.', true);
    }

    public function responseTypeDay($date)
    {
        return (int) (new Datetime($date))->format('N') % 2 != 0 ? 'even' : 'odd';
    }

    public function index(Request $request)
    {
        try {
            // $perPage = $request->input('per_page', 7);

            $student_code = request()->user()->user_code;

            $today = now();

            // $dates_want_response = [];

            // for ($i = 0; $i < 7; $i++) {
            //     $today->add(new DateInterval('P1D'));
            //     $dates_want_response[] = $today->format('Y-m-d');
            // }


            $classroom_codes = ClassroomUser::where('user_code', $student_code)->pluck('class_code');
            if (!$classroom_codes) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có lớp học nào!'
                ], 200);
            }


            $schedules = Schedule::with([
                'classroom.subject',
                'session',
                'classroom'
            ])->whereIn('class_code', $classroom_codes)
                // ->whereIn('date', $dates_want_response)
                ->where('date', '>=', $today)
                ->orderBy('date', 'asc')
                ->orderBy('session_code', 'asc') 
                ->get()->map(function ($schedule) {
                    // $session_info = optional($schedule->session);
                    return [
                        'class_code'    => $schedule->classroom->class_code,
                        'date'          => $schedule->date,
                        'subject_name'  => $schedule->classroom->subject->subject_name,
                        'room_code'     => $schedule->room_code,
                        'session'       => $schedule->session->value,
                        'session_code'  => $schedule->session->cate_code,
                        'session_name'  => $schedule->session->cate_name,
                        'subject_code'  => $schedule->classroom->subject_code,
                    ];
                });
            return response()->json($schedules, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function schedulesOfClassroom(string $class_code)
    {

        $student_code = request()->user()->user_code;

        $classroom = Classroom::whereHas('users', function ($query) use ($student_code) {
            $query->where('users.user_code', $student_code);
        })->firstWhere([
            'class_code' => $class_code
        ]);

        if (!$classroom) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền truy cập!'
            ], 403);
        }

        $schedules = Schedule::with([
            'session' => function ($query) {
                $query->select('cate_code', 'cate_name', 'value');
            }
        ])->where('class_code', $classroom->class_code)->get()->map(function ($schedule) {
            $session_info = optional($schedule->session);
            return [
                'id' => $schedule->id,
                'class_code' => $schedule->class_code,
                'room_code' => $schedule->room_code,
                'teacher_code' => $schedule->teacher_code,
                'date' => $schedule->date,
                'type' => $schedule->type,
                'session_name' => $session_info->cate_name,
                'session_value' => $session_info->value
            ];
        });
        return response()->json($schedules, 200);
    }



    public function transferSchedules()
    {
        try {
            $student_code = request()->user()->user_code;
            // Lấy khoảng thời gian có thể đổi lịch
            $timeFrame = TransferScheduleTimeframe::select('start_time', 'end_time')->first();
            if (!$timeFrame) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Không có khung giờ đổi lịch nào!'
                    ]
                );
            }
            // Lấy thời gian hiện tại
            $now = now()->toDateTimeString();
            // Kiểm tra xem thời gian hiện tại có đủ điều kiện để đổi lịch không
            if ($now <= $timeFrame->start_time || $now >= $timeFrame->end_time) {
                $start_date_time = new DateTime($timeFrame->start_time);
                $end_date_time = new DateTime($timeFrame->start_time);

                $start_date = $start_date_time->format('d/m/Y');
                $start_time = $start_date_time->format('H:i');
                $end_date = $end_date_time->format('d/m/Y');
                $end_time = $end_date_time->format('H:i');

                return response()->json('Thời gian đổi lịch từ '
                    . $start_time . ' ngày ' . $start_date . ' đến ' . $end_time . ' ngày ' . $end_date .  '!');
            }


            $class_code_transfered = TransferScheduleHistory::where('student_code', $student_code)->pluck('to_class_code');

            // Lấy thông tin các lớp học và số lượng học sinh hiện tại của lớp đó
            $classrooms = Classroom::whereNotIn('class_code', $class_code_transfered)
                ->select('class_code', 'class_name', 'subject_code')
                ->withCount('users')->with([
                    'subject:subject_code,subject_name',
                    'schedules' => function ($query) {
                        $query->select('class_code', 'room_code', 'session_code', 'date');
                    },
                    'schedules.session:cate_code,cate_name,value',
                    'schedules.room:cate_code,cate_name,value'
                ])
                ->whereHas('users' ,function($query) use ($student_code){
                    $query->where('users.user_code', $student_code);
                })
                ->whereHas('schedules', function($query){
                    // Loại bỏ các lớp có ngày bắt đầu <= hôm nay
                    $query->where('date', function($subQuery){
                        $subQuery->select(DB::raw('MIN(date)'))
                        ->from('schedules')
                        ->whereColumn('schedules.class_code', 'classrooms.class_code');
                    })->where('date', '>', now()->format('Y-m-d'));
                })
                ->get()
            ->map(function ($classroom) {
                $subject_info = optional($classroom->subject);
                $first_schedule = optional($classroom->schedules->first());
                $session_info = optional($first_schedule->session);
                $room_info = optional($first_schedule->room);
                $study_days = (int) (new Datetime($first_schedule->date))->format('N') % 2 == 0 ?
                    'Thứ 3, Thứ 5, Thứ 7' : 'Thứ 2, Thứ 4, Thứ 6';
                return [
                    'class_code' => $classroom->class_code,
                    'class_name' => $classroom->class_name,
                    'subject_code' => $subject_info->subject_code,
                    'subject_name' => $subject_info->subject_name,
                    'users_count' => $classroom->users_count,
                    'room_slot' => $room_info->value,
                    'session_name' => $session_info->cate_name,
                    'session_value' => $session_info->value,
                    'study_days' => $study_days,
                    'date_from' => $first_schedule->date
                ];
            });
            return response()->json($classrooms);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listSchedulesCanBeTransfer(ShowListScheduleCanBeTransfer $request)
    {
        try {
            // Thông tin sinh viên đang đăng nhập
            $student = request()->user();

            // Lấy khoảng thời gian có thể đổi lịch
            $timeFrame = TransferScheduleTimeframe::select('start_time', 'end_time')->first();

            // Lấy thời gian hiện tại
            $now = now()->toDateTimeString();
            // Kiểm tra xem thời gian hiện tại có đủ điều kiện để đổi lịch không
            if ($now <= $timeFrame->start_time || $now >= $timeFrame->end_time) {
                return $this->response422('Đã hết thời gian đổi lịch học!');
            }

            $data = $request->validated();

            // Kiểm tra lớp học hiện tại có tồn tại hay không!
            $classroom_current = Classroom::join('classroom_user', 'classroom_user.class_code', 'classrooms.class_code')
                ->join('subjects', 'subjects.subject_code', 'classrooms.subject_code')
                ->where('classrooms.class_code', $data['class_code'])
                ->where('classroom_user.user_code', $student->user_code)
                ->select(
                    'classrooms.id',
                    'classrooms.class_code',
                    'classrooms.class_name',
                    'classroom_user.user_code',
                    'subjects.subject_code',
                    'subjects.subject_name',
                    'subjects.semester_code',
                    'subjects.major_code'
                )
                ->first();

                // Lấy ra các lớp học khác ở hiện tại
            $classroom_others_current = Classroom::with([
                'schedules' => function ($query) {
                    $query->select('class_code', 'session_code', 'date');
                }
            ])
                ->whereHas('users', function ($query) use ($student) {
                    $query->where('classroom_user.user_code', $student->user_code);
                })
                ->whereHas('schedules', function($query){
                        $query->where('date', function($subQuery){
                            $subQuery->select(DB::raw('MIN(date)'))
                            ->from('schedules')
                            ->whereColumn('schedules.class_code', 'classrooms.class_code');
                        })->where('date', '>', now()->format('Y-m-d'));
                })
                // ->where('class_code', 'LIKE', $data['course_code'] . "." . '%')
                ->where('class_code', '!=', $classroom_current->class_code)
                ->get()->pluck('schedules')->flatten()->unique('class_code')->values();


            // Kiểm tra lớp học tồn tại và sinh viên đang đăng nhập có học trong lớp đó không
            if (!$classroom_current || $classroom_current->user_code != $student->user_code) {
                return $this->response404('Lớp học hiện tại không tồn tại!');
            }
            // Tìm các lớp học có Ca học Được yêu cầu + không phải lớp học hiện tại + các lớp học có cùng mã khoá học và mã môn học
            $subject_code = $classroom_current->subject_code;
            $classrooms_can_be_transfer = Classroom::select('class_code', 'class_name', 'subject_code')->withCount('users')
                ->with([
                    'subject' => function ($query) {
                        $query->select('subject_code', 'subject_name');
                    },
                    'schedules' => function ($query) {
                        $query->orderBy('date', 'asc');
                    },
                    'schedules.session' => function ($query) {
                        $query->select('cate_code', 'cate_name', 'value');
                    },
                    'schedules.room' => function ($query) {
                        $query->select('cate_code', 'cate_name', 'value');
                    }
                ])->whereHas('subject',  function ($query) use ($subject_code) {
                    $query->where('subject_code', $subject_code);
                })
                ->whereHas(
                    'schedules', function($query){
                         // Loại bỏ các lớp có ngày bắt đầu <= hôm nay
                    $query->where('date', function($subQuery){
                        $subQuery->select(DB::raw('MIN(date)'))
                        ->from('schedules')
                        ->whereColumn('schedules.class_code', 'classrooms.class_code');
                    })->where('date', '>', now()->format('Y-m-d'));
                    }
                )
                ->whereHas('schedules.session', function ($query) use ($data) {
                    $query->where('cate_code', $data['session_code']);
                })
                ->whereHas('schedules.room', function ($query) {
                    $query->whereRaw('CAST(value as SIGNED) > (SELECT COUNT(*) FROM classroom_user where classroom_user.class_code = schedules.class_code)');
                })
                ->where('class_code', 'LIKE', $this->sliceCourseFromClasscode($classroom_current->class_code) . "." . '%')
                ->where('class_code', '!=', $classroom_current->class_code)
                ->get()->map(function ($classroom_current) use ($classroom_others_current) {


                    $subject_info = optional($classroom_current->subject);
                    $first_schedule = optional($classroom_current->schedules->first());
                    $session_info = optional($first_schedule->session);
                    $room_info = optional($first_schedule->room);
                    $study_days = $this->responseTypeDay($first_schedule->date);


                    foreach ($classroom_others_current as $cls_others_studying) {

                        if ($session_info->cate_code == $cls_others_studying['session_code'] && $this->responseTypeDay($cls_others_studying['date']) == $study_days) {
                            return null;
                        }
                    }

                    return [
                        'class_code' => $classroom_current->class_code,
                        'class_name' => $classroom_current->class_name,
                        'users_count' => $classroom_current->users_count,
                        'subject_code' => $subject_info->subject_code,
                        'subject_name' => $subject_info->subject_name,
                        'session_name' => $session_info->cate_name,
                        'session_code' => $session_info->cate_code,
                        'session_value' => $session_info->value,
                        'room_slot' => $room_info->value,
                        'study_days' => $study_days == 'even' ? 'Thứ 2, Thứ 4, Thứ 6' : 'Thứ 3, Thứ 5, Thứ 7',
                        'date_from' => $first_schedule->date,
                    ];
                })->filter()->values();


            // Trường hợp không có lớp học nào để đổi
            if ($classrooms_can_be_transfer->count() === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có lớp học nào để đổi!'
                ]);
            }

            return response()->json($classrooms_can_be_transfer);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    // Hàm check các điều kiện khi đổi lịch của lớp học hiện tại
    public function checkClassroomCurrentForTransfer($classroom_current){
        // Kiểm tra lớp học hiện tại có tồn tại không
        if (!$classroom_current) {
            return $this->response404('Bạn không thuộc lớp học này!');
        }
        // Kiểm tra lịch học hiện tại đã bắt đầu hay chưa
        if($classroom_current->schedules->first()->date <= now()->format('Y-m-d')){
            return $this->response422('Lịch học hiện tại của bạn đã bắt đầu rồi, không thể đổi sang lịch học khác!');
        }
        // Kiểm tra giới hạn sinh viên có trong lớp hiện tại 
        if ($classroom_current->users_count <= 25) {
            return $this->response422('Lớp học đã đạt giới hạn tối thiểu, hiện tại không thể đổi lịch!');
        }
    }

    // Hàm check các điều kiện khi đổi lịch của lớp học mục tiêu

    public function checkClassroomTargetForTransfer($classroom_target){
        if (!$classroom_target) {
            return $this->response404('Lớp học bạn muốn đổi không tồn tại!');
        }

        if($classroom_target->schedules->first()->date <= now()->format('Y-m-d')){
            return $this->response422('Lịch học này đã bắt đầu rồi, hãy chuyển sang lịch học khác phù hợp!');
        }
        
        if ($classroom_target->users_count >= 40) {
            return $this->response422('Lớp học bạn muốn chuyển đến đã đạt giới hạn tối đa!');
        }

       
    }
    public function handleTransferSchedule(HandleTransferSchedule $request)
    {
        try {

            $data = $request->validated();
            $class_code_current = $data['class_code_current'];

            $timeFrame = TransferScheduleTimeframe::select('start_time', 'end_time')->first();

            // Lấy thời gian hiện tại
            $now = now()->toDateTimeString();
            // Kiểm tra xem thời gian hiện tại có đủ điều kiện để đổi lịch không
            if ($now <= $timeFrame->start_time || $now >= $timeFrame->end_time) {
                return $this->response422('Thời gian đổi lịch học đã kết thúc!');
            }

            // Lấy thông tin đăng nhập hiện tại của sinh viên
            $student = request()->user();
            $student_code = $student->user_code;

            // Trường hợp mã lớp học hiện tại = lớp học muốn chuyển đến 
            if ($data['class_code_current'] == $data['class_code_target']) {
                return $this->response422('Bạn đang học tại lớp học này rồi!');
            }

            // Kiểm tra xem học sinh này hiện tại có học trong lớp học có mã lớp được gửi lên hay không?
            $classroom_current = Classroom::withCount('users')
            ->with('schedules' ,function($query){
                $query->select('class_code', 'date')->orderBy('date', 'asc')->limit(1);
            })
                ->whereHas('users', function ($query) use ($student_code, $class_code_current) {
                    $query->where('classroom_user.user_code', $student_code)
                        ->where('classrooms.class_code', $class_code_current);

                })->lockForUpdate()->first();
            $this->checkClassroomCurrentForTransfer($classroom_current);

           
            // Kiểm tra xem sinh viên này đã đổi lịch lần nào hay chưa?
            $class_code_transfered = TransferScheduleHistory::where([
                'student_code' => $student_code,
                'to_class_code' => $classroom_current->class_code
            ])->exists();

            if ($class_code_transfered) {
                return $this->response422('Bạn không thể đổi lịch với môn học này nữa!');
            }

            // Kiểm tra xem lớp học mục tiêu có tồn tại không?
            $classroom_target = Classroom::withCount('users')->select('class_code', 'class_name', 'subject_code', 'user_code')->withCount('users')->with(
                [
                    'schedules' => function ($query) {
                        $query->select('class_code', 'room_code', 'session_code', 'date')->orderBy('date', 'asc');
                    },
                    'schedules.room' => function ($query) {
                        $query->select('cate_code', 'cate_name', 'value');
                    },
                    'schedules.session' => function ($query) {
                        $query->select('cate_code', 'cate_name', 'value');
                    }
                ]
            )->where('class_code', $data['class_code_target'])
                ->lockForUpdate()->first();
            
            $this->checkClassroomTargetForTransfer($classroom_target);
            
            // Kiểm tra 2 lớp có cùng khoá và môn không?
            if ($this->sliceCourseFromClasscode($classroom_current->class_code) !== $this->sliceCourseFromClasscode($classroom_target->class_code)) {
                return $this->response422('Khoá học giữa 2 lớp không trùng khớp!');
            }
            if ($classroom_current->subject_code !== $classroom_target->subject_code) {
                return $this->response422('Môn học giữa 2 lớp không trùng khớp!');
            }
            
            // Lấy ra thông tin lịch, ca, phòng của lớp học mục tiêu
            $schedule = $classroom_target->schedules->first();

            if (!$schedule) {
                return $this->response404('Lịch học của lớp học không xác định!');
            }

            if (!$schedule->room) {
                return $this->response404('Phòng học của lớp học không xác định!');
            }
            // Lấy sức chứa tối đa của lớp học
            $classroom_target_capacity = $schedule->room->value;
            // Lấy số lượng sinh viên hiện tại của lớp học
            $classroom_target_current_capacity = $classroom_target->users_count;

            // Trường hợp số lượng sinh viên hiện tại >= sức chứa tối đa của lớp học
            if ($classroom_target_current_capacity >= $classroom_target_capacity) {
                return $this->response422('Lớp học này đã đầy');
            }

            // Lấy ra các ngày học của lớp học mục tiêu
            $schedules_of_classroom_target = $classroom_target->schedules->pluck('date');

            // Xoá các lịch điểm danh được tạo sẵn ở lớp học cũ của sinh viên này
            Attendance::where([
                'student_code' => $student_code,
                'class_code' => $data['class_code_current']
            ])->delete();

            $now = now();
            $data_insert_to_attendance_table = [];
            foreach ($schedules_of_classroom_target as $date) {
                $data_insert_to_attendance_table[] = [
                    'student_code' => $student_code,
                    'class_code' => $classroom_target->class_code,
                    'date' => $date,
                    'status' => 'present',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Xoá liên kết giữa sinh viên và lớp học cũ
            $classroom_current->users()->detach($student_code);
            // Thêm liên kết giữa sinh viên và lớp học mới
            $classroom_target->users()->attach($student_code);
            // Thêm lịch điểm danh cho sinh viên ở lớp học mới
            Attendance::insert($data_insert_to_attendance_table);
            // Tạo lịch sử đổi lớp cho sinh viên 
            TransferScheduleHistory::create([
                'student_code' => $student_code,
                'from_class_code' => $classroom_current->class_code,
                'to_class_code' => $classroom_target->class_code,
            ]);

            
            return response()->json([
                'status' => true,
                'message' => 'Đổi lịch học thành công!'
            ], 201);
        } catch (\Throwable $th) {
            
            return $this->handleErrorNotDefine($th);
        }
    }
}
