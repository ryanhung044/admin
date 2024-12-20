<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\DeleteClassroomRequest;
use App\Http\Requests\Classroom\HandleStep1;
use App\Http\Requests\Classroom\HandleStep2;
use App\Http\Requests\Classroom\HandleStep3;
use App\Http\Requests\Classroom\RenderClassroomRequest;
use App\Http\Requests\Classroom\RenderRoomsAndTeachersForStoreClassroom;
use App\Http\Requests\Classroom\RenderSchedulesForStoreClassroom;
use App\Models\Classroom;
use App\Http\Requests\Classroom\StoreClassroomRequest;
use App\Http\Requests\Classroom\UpdateClassroomRequest;
use App\Models\Attendance;
use App\Models\Category;
use App\Models\ClassroomUser;
use App\Models\Fee;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{


    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {
        return response()->json([
            'status' => false,
            'message' => 'Lớp học không tồn tại!',
        ], 404);
    }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
        return response()->json([
            'status' => false,
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ], 500);
    }

    public function handleConflict()
    {
        return response()->json([
            'status' => false,
            'message' => 'Bản ghi này đã có cập nhật trước đó, hãy cập nhật lại trang!'
        ], 409);
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->search;
            $orderBy = $request->input('orderBy', 'created_at');
            $orderDirection = $request->input('orderDirection', 'asc');
    
            $classrooms = Classroom::orderBy('classrooms.is_active', "desc")->select(['classrooms.class_code', 'classrooms.class_name', 'classrooms.user_code', 'classrooms.subject_code', 'classrooms.is_active'])
            // ->where('classrooms.is_active',true)

                ->when($search, function($query) use ($search) {
                    $query->where('classrooms.class_code', "LIKE", "%$search%")
                        ->orWhere('classrooms.class_name', "LIKE", "%$search%")
                        ->orWhereHas('teacher', function($subQuery) use ($search) {
                            $subQuery->where('full_name', "LIKE", "%$search%");
                        })
                        ->orWhereHas('subject', function($subQuery) use ($search) {
                            $subQuery->where('subject_name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('schedules.session', function($subQuery) use ($search) {
                            $subQuery->where('cate_name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('schedules.room', function($subQuery) use ($search) {
                            $subQuery->where('cate_name', 'LIKE', "%$search%");
                        });
                })
                ->withCount('users')
                ->with([
                    'teacher' => function ($query) {
                        $query->select('user_code', 'full_name')->withTrashed();
                    },
                    'subject' => function ($query) {
                        $query->select('subject_code', 'subject_name');
                    },
                    'schedules' => function ($query) {
                        $query->select('class_code', 'date', 'session_code', 'room_code');
                    },
                    'schedules.session' => function ($query) {
                        $query->select('cate_code', 'cate_name', 'value');
                    },
                    'schedules.room' => function ($query) {
                        $query->select('cate_code', 'cate_name');
                    }
                ]);
               
            // // Sắp xếp theo các trường liên quan đến quan hệ
            // switch ($orderBy) {
            //     case 'subject_name':
            //         $classrooms->join('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code');
            //         $classrooms->orderBy('subjects.subject_name', $orderDirection);
            //         break;
            //     case 'teacher_name':
            //         $classrooms->join('users as teacher', 'classrooms.user_code', '=', 'teacher.user_code') // Nối với bảng 'users' cho giáo viên
            //                     ->orderBy('teacher.full_name', $orderDirection);
            //         break;
            //     case 'students_count':
            //         $classrooms->withCount('users') // Đếm số lượng sinh viên
            //                     ->orderBy('users_count', $orderDirection);
            //         break;
            //     case 'session_name':
            //         // Lấy bản ghi đầu tiên từ bảng schedules và join với bảng sessions
            //         $classrooms->join('schedules', 'classrooms.class_code', '=', 'schedules.class_code')
            //         ->join('categories', 'schedules.session_code', '=', 'categories.cate_code')
            //         ->select('classrooms.class_code', 'classrooms.class_name', 'classrooms.user_code', 'classrooms.subject_code', 'categories.cate_name')
            //         ->groupBy('classrooms.class_code', 'classrooms.class_name', 'classrooms.user_code', 'classrooms.subject_code', 'categories.cate_name')
            //         ->orderBy('categories.cate_name', $orderDirection);
                
            //         break;
            
            //     default:
            //         $classrooms->orderBy($orderBy, $orderDirection); // Nếu không phải trường hợp cụ thể nào, sắp xếp theo trường được chỉ định
            //         break;
            // }
    
            // Lấy dữ liệu phân trang
            $classrooms = $classrooms->paginate($perPage);
    
            // Biến đổi dữ liệu sau khi lấy từ DB
            $classrooms->getCollection()->transform(function ($classroom) use ($search) {
                $subject_info = $classroom->subject ?? null;
                $teacher_info = $classroom->teacher ?? null;
                $schedule_first = $classroom->schedules->first(); // Lấy lịch học đầu tiên
                $session_info = $schedule_first ? $schedule_first->session : null;
                $room_info = $schedule_first ? $schedule_first->room : null;
    
                $students_count = $classroom->users_count;
    
                return [
                    'class_code' => $classroom->class_code,
                    'class_name' => $classroom->class_name,
                    'students_count' => $students_count,
                    'subject_code' => $subject_info->subject_code ?? null,
                    'subject_name' => $subject_info->subject_name ?? null,
                    'teacher_code' => $teacher_info->user_code ?? null,
                    'teacher_name' => $teacher_info->full_name ?? null,
                    'date_start' => $schedule_first->date ?? null,
                    'type_day' => $schedule_first && (new \DateTime($schedule_first->date))->format('d') % 2 != 0 ? "Ngày 2,4,6" : 'Ngày 3,5,7',
                    'room_name' => $room_info->cate_name ?? null,
                    'session_name' => $session_info->cate_name ?? null,
                    'session_value' => $session_info->value ?? null,
                    'is_active' => $classroom->is_active
                ];
            });
    
    
            return response()->json([
                'status' => true,
                'classrooms' => $classrooms
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
    



    public function handleStep1(HandleStep1 $request)
    {
        try {
            $data = $request->validated();
            $subject = Subject::with('semester')->where('subject_code', $data['subject_code'])->first();

            if (!$subject) {
                return response()->json([
                    'status' => false,
                    'message' => 'Môn học không tồn tại!'
                ], 404);
            }
            // Lấy ra danh sách các lớp học đã được tạo bởi môn học này + khoá học này
            //  $classroom_codes = Classroom::where('class_code', 'LIKE', $data['course_code'] . '.' . $data['subject_code'] . '%')
            //     ->select('class_code')->pluck('class_code');

                
            // Lấy ra danh sách các học sinh đã được xếp lớp cho môn học này + khoá học này 
            // $student_codes_has_been_arrange = ClassroomUser::whereIn('class_code', $classroom_codes)->pluck('user_code');
            // Lấy ra tổng số học sinh có thể được tạo lớp mới với môn học này + khoá học này
            // $count_students_can_be_arrange = User::whereNotIn('user_code', $student_codes_has_been_arrange)
            // ->where('major_code', $data['major_code'])
            // ->orWhere('narrow_major_code', $data['major_code'])    
            // ->where([
            //         'course_code' => $data['course_code'],
            //         'semester_code' => $data['semester_code'],
            //         'is_active' => true,
            //         'role' => '3'
            //     ])->pluck('user_code');

            // $student_codes_can_be_arrange  = [];
// // Lấy các sinh viên chưa từng học môn này và đã đóng tiền cho kỳ có môn học này
//              $student_codes_paid = Fee::where([
//                 'status' => 'paid',
//                 'semester_code' => $data['semester_code']
//             ])->pluck('user_code')->toArray();

//             $students_paid = User::whereIn('user_code', $student_codes_paid)->pluck('user_code')->toArray();
// // Lấy ra các sinh viên đã đóng tiền học lại
//             $student_codes_relearn = Score::where([
//                 'subject_code' => $subject->subject_code,
//                 'is_pass' => false,
//                 'status' => true
//             ])->pluck('student_code')->toArray();
            
//             $student_codes_can_be_arrange = array_unique(array_merge($student_codes_paid, $student_codes_relearn));
//             $students_can_be_arrange = User::whereNotIn('user_code', $student_codes_has_been_studied)->whereIn('user_code', $student_codes_can_be_arrange)
//                 ->where([
//                     'role' => '3',
//                     'is_active' => true,
//                     'course_code' => $data['course_code'],
//                     'semester_code' => $semester_code,
//                 ])->where(function ($query) use ($data) {
//                     $query->where('major_code', $data['major_code'])
//                         ->orWhere('narrow_major_code', $data['major_code']);
//                 })->select('user_code', 'full_name', 'email', 'sex')->limit($room_slot)->get();

//             return response()->json($count_students_can_be_arrange);


$classrooms_has_been_studied = Classroom::with('users')->where("class_code", "LIKE", $data['course_code'] . '.' . $data['subject_code'] . "%")->get();

$student_codes_has_been_studied  = $classrooms_has_been_studied->flatMap(function ($classroom) {
    return $classroom->users->pluck('user_code');
});


$student_codes_can_be_arrange  = [];
// Lấy các sinh viên chưa từng học môn này và đã đóng tiền cho kỳ có môn học này
 $student_codes_paid = Fee::where([
    'status' => 'paid',
    'semester_code' => $data['semester_code']
])->pluck('user_code')->toArray();
// Lấy ra các sinh viên đã đóng tiền học lại
$student_codes_relearn = Score::where([
    'subject_code' => $subject->subject_code,
    'is_pass' => false,
    'status' => true
])->pluck('student_code')->toArray();

$student_codes_can_be_arrange = array_unique(array_merge($student_codes_paid, $student_codes_relearn));
$students_can_be_arrange = User::whereNotIn('user_code', $student_codes_has_been_studied)->whereIn('user_code', $student_codes_can_be_arrange)
    ->where([
        'role' => '3',
        'is_active' => true,
        'course_code' => $data['course_code'],
        'semester_code' => $data['semester_code'],
    ])->where(function ($query) use ($data) {
        $query->where('major_code', $data['major_code'])
            ->orWhere('narrow_major_code', $data['major_code']);
    })->select('user_code', 'full_name', 'email', 'sex')->count();

// if ($students_can_be_arrange->isEmpty()) {
//     return response()->json([
//         'status' => false,
//         'message' => 'Không có sinh viên nào để tạo lớp học này!'
//     ], 404);
// }

return response()->json($students_can_be_arrange);

        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function renderSchedules(RenderSchedulesForStoreClassroom $request)
    {
        try {
            $data = $request->validated();

            $subject = Subject::firstWhere('subject_code', $data['subject_code']);

            $dateFrom = $data['date_from'];
            $day_type = $data['day_type'];

            // Học ngày thứ 2,4,6
            if ($day_type == 1) {
                $studyDays = [1, 3, 5];
            }
            // Học ngày thứ 3,5,7
            if ($day_type == 2) {
                $studyDays = [2, 4, 6];
            }

            // Danh sách các ngày ngày học sẽ được thêm vào
            $study_dates = [];
            $total_exam_sessions = 3;

            $curentDate = new DateTime($dateFrom);
            do {
                if (in_array($curentDate->format('N'), $studyDays)) {
                    $study_dates[] = $curentDate->format('Y-m-d');
                }
                // Tăng ngày hiện tại lên 1 ngày

                if (count($study_dates) == $subject['total_sessions'] - 3) {
                    $exam_days = 0;
                    $curentDate->add(new DateInterval('P7D'));
                    while ($exam_days < $total_exam_sessions) {
                        if (in_array($curentDate->format('N'), $studyDays)) {
                            $study_dates[] = $curentDate->format('Y-m-d');
                            $exam_days++;
                        }
                        $curentDate->add(new DateInterval('P1D'));
                    }
                    break;
                }

                $curentDate->add(new DateInterval('P1D'));
            } while (count($study_dates) < $subject['total_sessions']);

            return response()->json($study_dates, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function renderRoomsAndTeachers(RenderRoomsAndTeachersForStoreClassroom $request)
    {
        try {
            $data = $request->validated();

            // Lấy ra các lịch học với ngày học và ca học muốn tìm
            $schedules = Schedule::with([
                'room' => function ($query) {
                    $query->select('cate_code', 'cate_name', 'value');
                },
                'teacher' => function ($query) {
                    $query->select('user_code');
                }
            ])->whereIn('date', $data['list_study_dates'])
                ->where('session_code', $data['session_code'])->get();

            // Lấy ra các lớp học của lịch học vừa tìm thấy
            // $classroom_codes_studied = $schedules->pluck('class_code')->unique();
            // Lấy ra các phòng học của lịch học vừa tìm thấy
            $room_codes_studied = $schedules->pluck('room.cate_name')->unique();

            // Loại bỏ các phòng vừa tìm thấy để lấy các phòng chưa trống
            $rooms_can_be_study = Category::whereNotIn('cate_code', $room_codes_studied)->where([
                'is_active' => true,
                'type' => 'school_room'
            ])->select('cate_code', 'cate_name', 'value')->limit(10)->get();

            // Lấy ra các giảng viên đang dạy tại các lịch vừa tìm thấy
            $teacher_codes_cannot_be_teach = $schedules->pluck('teacher.user_code')->unique();

            // Loại bỏ các giảng viên vừa tìm được để tìm ra các giảng viên có thể dạy 
            $teachers_can_be_teach = User::whereNotIn('user_code', $teacher_codes_cannot_be_teach)    
            ->where([
                    'is_active' => true,
                    'role' => '2'
                ])
                ->where('major_code', $data['major_code'])
                ->orWhere('narrow_major_code', $data['major_code'])
                ->select('user_code', 'full_name')->limit(10)->get();

            return response()->json([
                'status' => true,
                'rooms' => $rooms_can_be_study,
                'teachers' => $teachers_can_be_teach
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function handleStep2(HandleStep2 $request)
    {
        try {
            $data = $request->validated();

            $subject = Subject::with('semester')->where('subject_code', $data['subject_code'])->first();

            if (!$subject) {
                return response()->json([
                    'status' => false,
                    'message' => 'Môn học không tồn tại!'
                ], 404);
            }

            $semester_code = $subject->semester->cate_code;

            $classrooms_has_been_studied = Classroom::with('users')->where("class_code", "LIKE", $data['course_code'] . '.' . $data['subject_code'] . "%")->get();

            $student_codes_has_been_studied  = $classrooms_has_been_studied->flatMap(function ($classroom) {
                return $classroom->users->pluck('user_code');
            });

            $room_slot = Category::where(
                [
                    'cate_code' => $data['room_code'],
                    'is_active' => true,
                    'type' =>   'school_room'
                ]
            )->pluck('value')->first();
            if (!$room_slot) {
                return response()->json([
                    'status' => false,
                    'message' => 'Phòng học này không tồn tại hoặc không thể học!'
                ], 404);
            }

            $student_codes_can_be_arrange  = [];
// Lấy các sinh viên chưa từng học môn này và đã đóng tiền cho kỳ có môn học này
             $student_codes_paid = Fee::where([
                'status' => 'paid',
                'semester_code' => $semester_code
            ])->pluck('user_code')->toArray();
// Lấy ra các sinh viên đã đóng tiền học lại
            $student_codes_relearn = Score::where([
                'subject_code' => $subject->subject_code,
                'is_pass' => false,
                'status' => true
            ])->pluck('student_code')->toArray();
            
            $student_codes_can_be_arrange = array_unique(array_merge($student_codes_paid, $student_codes_relearn));
            $students_can_be_arrange = User::whereNotIn('user_code', $student_codes_has_been_studied)->whereIn('user_code', $student_codes_can_be_arrange)
                ->where([
                    'role' => '3',
                    'is_active' => true,
                    'course_code' => $data['course_code'],
                    'semester_code' => $semester_code,
                ])->where(function ($query) use ($data) {
                    $query->where('major_code', $data['major_code'])
                        ->orWhere('narrow_major_code', $data['major_code']);
                })->select('user_code', 'full_name', 'email', 'sex')->limit($room_slot)->get();


            if ($students_can_be_arrange->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có sinh viên nào để tạo lớp học này!'
                ], 404);
            }

            return response()->json($students_can_be_arrange);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    public function store(StoreClassroomRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = $request->validated();
            $current_classcode = Classroom::where('class_code', 'LIKE', $data['course_code'] . '.' . $data['subject_code'] . "%")
                ->orderBy('class_code', 'desc')->pluck('class_code')->first();
            $number = 1;

            if ($current_classcode) {
                $parts = explode($data['course_code'] . '.' . $data['subject_code'], $current_classcode);
                $number = (int) $parts[1] + 1;
                // $dot_position = strrpos($current_classcode, '.');
                // $behind_dot_position = $dot_position + 1;
                // $number = (int) substr($current_classcode, $behind_dot_position) + 1;
            } 


            if (!empty($data['teacher_code'])) {
                $teacher = User::where('user_code', $data['teacher_code'])->first();
                if (!$teacher) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Giảng viên này không tồn tại!'
                    ], 404);
                }

                if($teacher->is_active == false){
                    return response()->json([
                        'status' => false,
                        'message' => "Giảng viên này hiện tại không thể tạo lớp!"
                    ]);
                }

                $schedules = Schedule::with([
                    'room' => function ($query) {
                        $query->select('cate_code', 'cate_name', 'value');
                    },
                    'teacher' => function ($query) {
                        $query->select('user_code');
                    }
                ])->whereHas('classroom', function($query)use ($data){
                    $query->where('user_code', $data['teacher_code']);
                })
                ->whereIn('date', $data['list_study_dates'])
                    ->where('session_code', $data['session_code'])->get();
                if(!$schedules->isEmpty()){
                        return response()->json([
                            'status' => false,
                            'message' => 'Giảng viên này không thể dạy lớp học này!'
                        ],409);
                    }
            }


            $now = now();

            $data_for_classrooms_table =  [
                "class_code"  => $data['course_code'] . "." . $data['subject_code'] . $number,
                "class_name"  => $data['course_code'] . "." . $data['subject_code'] . $number,
                "subject_code"  => $data['subject_code'],
                "user_code" => $data['teacher_code'],
                "created_at" => $now,
                "updated_at" => $now
            ];
            $student_codes_valid = User::whereIn('user_code', $data['student_codes'])->pluck('user_code');

            if ($student_codes_valid->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có sinh viên hợp lệ để có thể tạo lớp!'
                ], 404);
            }

            $classroom = Classroom::create($data_for_classrooms_table);
            $classroom->users()->attach($student_codes_valid, ['class_code' => $classroom->class_code]);

            $data_to_insert_schedules_table = [];
            $data_to_insert_attendance_table = [];
            $count_date = count($data['list_study_dates']);
            $exam_date_first = $count_date - 3;
            foreach ($data['list_study_dates'] as $index => $date) {
                $data_to_insert_schedules_table[] = [
                    'class_code' => $classroom->class_code,
                    'session_code' => $data['session_code'],
                    'room_code' => $data['room_code'],
                    'teacher_code' => $data['teacher_code'] ?? null,
                    'date' => $date,
                    'type' => 'study',
                    "created_at" => $now,
                    "updated_at" => $now
                ];
                if($index < $exam_date_first){
                    foreach($student_codes_valid as $std_code){
                        $data_to_insert_attendance_table[] = [
                            'student_code' => $std_code,
                            'class_code' => $classroom->class_code,
                            'date' => $date,
                            'status' => 'present',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            $total_dates = count($data_to_insert_schedules_table);
            // Lấy 3 ngày cuối là các ngày thi
            for ($i = $total_dates - 3; $i < $total_dates; $i++) {
                if ($i > 0) {
                    $data_to_insert_schedules_table[$i]['type'] = 'exam';
                }
            }


            Attendance::insert($data_to_insert_attendance_table);


            Schedule::insert($data_to_insert_schedules_table);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Tạo lớp thành công!'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->handleErrorNotDefine($th);
        }
    }




    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Classroom  $Classroom
     * @return \Illuminate\Http\Response
     */
    public function show(string $classCode)
    {
        try {
            $classroom = Classroom::with([
                // Thông tin Môn học
                'subject' => function ($query) {
                    $query->select('subject_code', 'subject_name', 'major_code');
                },
                'subject.major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                // Thông tin Giảng viên
                'teacher' => function ($query) {
                    $query->select('user_code', 'full_name', 'email', 'phone_number');
                },
                'schedules' => function ($query) {
                    $query->select('class_code', 'room_code', 'session_code', 'date')
                        ->limit(1);
                },
                'schedules.session' => function ($query) {
                    $query->select('cate_name', 'cate_code', 'value');
                },
                'schedules.room' => function ($query) {
                    $query->select('cate_code', 'cate_name', 'value');
                },
                // Các sinh viên học lớp này    
                'users' => function ($query) {
                    $query->select('users.id', 'users.user_code', 'users.full_name', 'users.email', 'users.phone_number', 'is_active');
                }
            ])->select('class_code', 'class_name', 'subject_code', 'user_code', 'created_at', 'updated_at')
                ->where([
                    'class_code' =>  $classCode,
                    'is_active' => true
                ])->first();


            if (!$classroom) {
                return $this->handleInvalidId();
            }

            $subject_info = optional($classroom->subject);
            $major_info = optional($subject_info->major);
            $schedule_info = optional($classroom->schedules->first());
            $session_info = optional($schedule_info->session);
            $room_info = optional($schedule_info->room);
            $teacher_info = optional($classroom->teacher);


            $list_students = $classroom->users->map(function ($student) {
                return [
                    'id' => $student->id,
                    'user_code' => $student->user_code,
                    'full_name' => $student->full_name,
                    'email' => $student->email,
                    'phone_number' => $student->phone_number,
                    'is_active' => $student->is_active,
                ];
            });

            $classroom = [
                'class_code' => $classroom->class_code,
                'class_name' =>  $classroom->class_name,
                'subject_code' =>  $subject_info->subject_code,
                'subject_name' =>  $subject_info->subject_name,
                'major_code' =>  $major_info->cate_code,
                'major_name' =>  $major_info->cate_name,
                'date_start' => $schedule_info->date,
                'session_name' =>  $session_info->cate_name,
                'session_value' =>  $session_info->value,
                'room_name' =>  $room_info->cate_name,
                'room_slot' =>  $room_info->value,
                'teacher_code' =>  $teacher_info->user_code,
                'teacher_name' =>  $teacher_info->full_name,
                'teacher_email' =>  $teacher_info->email,
                'teacher_phone_number' =>  $teacher_info->phone_number,
                'students' => $list_students,
            ];

            return response()->json([
                'status' => true,
                'classroom' => $classroom
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    // public function update(UpdateClassroomRequest $request, string $classCode)
    // {

    //     return response()->json($request->all());
    //     // try {
    //     //     $data = $request->all();

    //     //     return response()->json($data);

    //     //     if ($request->has('is_active')) {
    //     //         $data['is_active'] = true;
    //     //     } else {
    //     //         $data['is_active'] = false;
    //     //     }

    //     //     $classroom = Classroom::where('class_code', $classCode)->first();

    //     //     if (!$classroom) {
    //     //         return $this->handleInvalidId();
    //     //     }

    //     //     $classroom->update($data);
    //     //     return response()->json([
    //     //         'message' => 'Cập nhật thông tin lớp học thành công!',
    //     //         'classroom' => $classroom
    //     //     ], 200);
    //     // } catch (\Throwable $th) {
    //     //     return $this->handleErrorNotDefine($th);
    //     // }
    // }

    public function destroy(string $classCode)
    {
        DB::beginTransaction();
        try {

            $classroom = Classroom::with([
                'schedules' => function($query) {
                    $query->selectRaw('class_code, MAX(date) as max_date, MIN(date) as min_date')
                          ->groupBy('class_code');
                }
            ])->where('class_code', $classCode)->lockForUpdate()->first();

            if (!$classroom) {
                return $this->handleInvalidId();
            }


            $now = now()->format('Y-m-d');
            $schedule = optional($classroom->schedules->first());

                if($schedule && $now <= $schedule->max_date && $now >= $schedule->min_date ){
                    return response()->json([
                        'status' => false,
                        'message' => 'Lớp học này đang trong thời gian học. Bạn không thể xoá!'
                    ],409);
                }


            // if ($data['updated_at'] !== $classroom->updated_at->toDateTimeString()) {
            //     return $this->handleConflict();
            // }

            // Xoá lớp học
            // return response()->json($latest_date);
            
            $classroom->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Xoá lớp học ' . $classroom->class_name . ' thành công!'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->handleErrorNotDefine($th);
        }
    }

    public function updateActive(string $classCode)
    {
        try {
            $listClassroom = Classroom::where('class_code', $classCode)->firstOrFail();
            // dd(!$listClassroom->is_active);
            $listClassroom->update([
                'is_active' => !$listClassroom->is_active
            ]);
            $listClassroom->save();
            return response()->json([
                'message' => 'Cập nhật thành công',
                'error' => false
            ], 200);
        } catch (\Throwable $th) {
            // Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định',
                'error' => true
            ], 500);
        }
    }
}
