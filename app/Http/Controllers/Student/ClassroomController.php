<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use App\Models\ClassroomUser;
use DateTime;

class ClassroomController extends Controller
{


    public function handleErrorNotDefine($th)
    {
        return response()->json([
            'status' => false,
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ], 500);
    }

    public function handleInvalidId()
    {
        return response()->json([
            'status' => false,
            'message' => 'Không tìm thấy lớp học này!',
        ], 404);
    }


    public function index()
    {
        try {
            $student_code = request()->user()->user_code;
            $classrooms = Classroom::whereHas('users', function ($query) use ($student_code) {
                                $query->where('classroom_user.user_code', $student_code)
                                    ->select('users.user_code', 'users.full_name');
                            })
                            ->with(['subject' => function($query){
                                $query->select('subject_code', 'subject_name');

                            },
                            'teacher' => function($query) {
                                $query->select('user_code', 'full_name');
                                
                            },
                            'schedules.room' => function($query) {
                                $query->select('cate_code', 'cate_name', 'value');
                                
                            },
                            'schedules.session' => function($query) {
                                $query->select('cate_code', 'cate_name', 'value');
                                
                            }])->get(['class_code', 'class_name', 'user_code', 'is_active', 'subject_code']);
            $result = $classrooms->map(function($classroom) {
                $schedules_first = optional($classroom->schedules->first());
                $student = optional($classroom->users);
                $totalStudent = $student->count();
                $studyTime = json_decode($schedules_first->session['value'], true);
                return [
                    'class_code' => $classroom->class_code ?? null,
                    'class_name' => $classroom->class_name ?? null,
                    'subject_name' => $classroom->subject->subject_name ?? null,
                    'teacher_name' => $classroom->teacher->full_name ?? null,
                    'type_day' => (new DateTime($schedules_first->date))->format('d') % 2 != 0 ? 'Thứ 2,4,6' : 'Thứ 3,5,7',
                    'total_student' => $totalStudent ?? null,
                    'room_name' => $schedules_first->room->cate_name ?? null,
                    'session_name' => $schedules_first->session->cate_name ?? null,
                    'value' => $studyTime ?? null,                    
                ];
            });
            if($classrooms->isEmpty()){
                return response()->json(
                    ['message' => "Không có lớp học nào!"], 204
                );
            }
            return response()->json($result,200);
            // $student = User::with([
            //     'classrooms' => function ($query) {
            //         $query->select('id');
            //     }
            // ])->where([
            //     'is_active' => true,
            //     'user_code' => $student_code
            // ])->first();

            // if (!$student) {
            //     return response()->json("Bạn không có quyền truy cập!");
            // }
            // $classroom_codes = $student->classrooms->pluck('pivot.class_code');
            // $classrooms = Classroom::with(['subject' => function ($query) {
            //     $query->select('subject_code', 'subject_name');
            // }])
            //     ->whereIn('class_code', $classroom_codes)->select('class_code', 'class_name', 'subject_code')->get();
            // return response()->json($classrooms);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    public function show(string $class_code)
    {
        try {
            $student_code = request()->user()->user_code;

            $classroom_user = ClassroomUser::with([
                'classroom' => function ($query) {
                    $query->select('class_code', 'class_name', 'is_active', 'subject_code', 'user_code');
                },
                'classroom.subject' => function ($query) {
                    $query->select('subject_code', 'subject_name');
                },
                'classroom.teacher' => function ($query) {
                    $query->select('user_code', 'full_name', 'email', 'major_code');
                },
                'classroom.teacher.major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                }
            ])->where([
                'user_code' => $student_code,
                'class_code' => $class_code
            ])->first();

            if (!$classroom_user) {
                return $this->handleInvalidId();
            }

            $classroom = $classroom_user->classroom->first();

            if (!$classroom) {
                return $this->handleInvalidId();
            }


            return response()->json($classroom, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

}
