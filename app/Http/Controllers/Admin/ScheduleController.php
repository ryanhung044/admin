<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\CreateTransferScheduleTimeframe;
use App\Models\Classroom;
use App\Models\ClassroomUser;
use App\Models\Schedule;
use App\Models\TransferScheduleTimeframe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{

    public function handleInvalidId()
    {
        return response()->json([
            'status' => false,
            'message' => 'Lớp học này không tồn tại!',
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


    public function schedulesOfClassroom(string $class_code)
    {
        try {

            $classroom = Classroom::with([
                'subject' => function($query){
                    $query->select('subject_code', 'subject_name');
                },
                'schedules' => function($query){
                    $query->select('class_code', 'room_code', 'session_code', 'teacher_code', 'date', 'type');
                },
                'schedules.session' => function($query){
                    $query->select('cate_code', 'cate_name', 'value');
                } ,
                'schedules.room' => function($query){
                    $query->select('cate_code', 'cate_name');
                },
                'schedules.teacher' => function($query){
                    $query->select('user_code', 'full_name');
                }
            ])->where('class_code', $class_code)->select('class_code', 'class_name', 'subject_code')->first();
            
            if (!$classroom) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Lớp học không tồn tại!'
                    ],
                    404
                );
            }

            $schedules = $classroom->schedules->map(function($schedule){
                $session_info = optional($schedule->session);
                $room_info = optional($schedule->room);
                $teacher_info = optional($schedule->teacher);
                return [
                    'class_code' => $schedule->class_code,
                    'date' => $schedule->date,
                    'type' => $schedule->type,
                    'session_name' => $session_info->cate_name,
                    'session_value' => $session_info->value,
                    'room_name' => $room_info->cate_name,
                    'teacher_code' => $teacher_info->user_code,
                    'teacher_name' => $teacher_info->full_name
                ];
            });

            if($schedules->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'Không có lịch học nào!',
                ]);
            }

            return response()->json($schedules, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function schedulesOfTeacher(string $teacher_code)
    {
        try {
            $schedules = Schedule::select('class_code', 'room_code', "session_code", "teacher_code","date", 'type')
            ->with([
                'room' => function($query){
                    $query->select('cate_code', 'cate_name');
                }, 
                'session' => function($query){
                    $query->select('cate_code', 'cate_name', 'value');
                }
            ])
            ->where('teacher_code', $teacher_code)->get()
            ->map(function($schedule){
                $session_info = optional($schedule->session);
                $room_info = optional($schedule->room);
                return [
                    'class_code' => $schedule->class_code,
                    'date' => $schedule->date,
                    'type' => $schedule->type,
                    'session_name' => $session_info->cate_name,
                    'session_value' => $session_info->value,
                    'room_name' => $room_info->cate_name,
                ];
            });
            if($schedules->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'Không có lịch học nào!'
                ]);
            }
            return response()->json($schedules);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function schedulesOfStudent(string $student_code)
    {
        try {
            $classroom_codes = ClassroomUser::where('user_code', $student_code)->pluck('class_code');
            // trường hợp không tìm thấy lớp học nào tương ứng trong bảng trung gian classroom_user
            if(empty($classroom_codes)){
                return response()->json([
                    'status' => false,
                    'message' => 'Không có lớp học nào!'
                ]);
            }

            // Tìm các lịch học tương ứng với các lớp học vừa tìm được 
            $schedules = Schedule::with([
                'session' => function($query){
                    $query->select('cate_code', 'cate_name', 'value');
                }, 
                'room' => function($query){
                    $query->select('cate_code', 'cate_name');
                }, 
                'teacher' => function($query){
                    $query->select('user_code', 'full_name');
                }])->whereIn('class_code', $classroom_codes)->get()
                ->map(function($schedule){
                    $session_info = optional($schedule->session);
                    $room_info = optional($schedule->room);
                    $teacher_info = optional($schedule->teacher);
                    return [
                        'class_code'=> $schedule->class_code,
                        'date' => $schedule->date,
                        'type' => $schedule->type,
                        'session_name'=> $session_info->cate_name,
                        'session_value' => $session_info->value,
                        'room_name' => $room_info->cate_name,
                        'teacher_code' => $teacher_info->user_code,
                        'teacher_name' => $teacher_info->full_name
                    ];
                });

            if($schedules->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'Không có lịch học nào!'
                ]);
            }
            
            return response()->json($schedules);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    public function transfer_schedule_timeframe()
    {
        try {
            $timeframe = TransferScheduleTimeframe::select('start_time', 'end_time')->first();
            
            return response()->json($timeframe);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
    public function create_transfer_schedule_timeframe(CreateTransferScheduleTimeframe $request)
    {

        try {
            $data = $request->validated();

            if ($data['start_time'] >= $data['end_time']) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu!'
                    ],
                    403
                );
            }

            TransferScheduleTimeframe::updateOrCreate(
                [
                    'id' => 1
                ],
                [
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time']
                ]
            );
            return response()->json([
                'status' => true,
                'message' => 'Đặt thời gian đổi lịch cho sinh viên thành công!'
            ]);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
}
