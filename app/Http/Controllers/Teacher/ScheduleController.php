<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\Count;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function handleInvalidId()
    {
        return response()->json([
            'message' => 'Lớp học không tồn tại!',
        ], 404);
    }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
        return response()->json([
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ], 500);
    }

    public function index(Request $request)
    {

        try {
            $today = Carbon::today();
            $sevenDaysLater = Carbon::today()->addDays(7);

            $userCode = $request->user()->user_code;

            // $list_classroom_codes = Classroom::where([
            //     'user_code' =>  $userCode,
            //     'is_active' => true
            // ])
            // ->pluck('class_code');

            // $list_schedules = Schedule::with(['classroom','room','session'])
            // ->whereIn('class_code', $list_classroom_codes)
            // ->select('class_code', 'room_code' , 'session_code', 'date')
            // ->get();

            $list_schedules = Schedule::whereBetween('date', [$today, $sevenDaysLater])
                ->where('type', 'study')
                ->where('teacher_code', $userCode)
                ->get();

            return response()->json($list_schedules, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }



    public function listSchedulesForClassroom(string $classcode){
        try{
            $teacher_code = request()->user()->user_code;

            $classroom = Classroom::select('class_code', 'class_name', 'user_code', 'subject_code')->with([
                'schedules' => function($query){
                    $query->select('class_code', 'room_code', 'session_code', 'teacher_code', 'date', 'type');
                }, 
                'schedules.session' => function($query){
                    $query->select('cate_name', 'cate_code', 'value');
                }, 
                'schedules.teacher' => function($query){
                    $query->select('user_code', 'full_name');
                }
            ])->where([
                'class_code' => $classcode,
                'user_code' => $teacher_code
            ])->first();

            if(!$classroom){
                return response()->json([
                    'status' => false,
                    'message' => 'Lớp học không tồn tại!'
                ],404);
            }
            
            $schedules = $classroom->schedules->map(function($schedule){
                $session_info = optional($schedule->session);
                $teacher_info = optional($schedule->teacher);
                return [
                    'date' => $schedule->date,
                    'session_name' => $session_info->cate_name,
                    'session_value' => $session_info->value,
                    'room_code' => $schedule->room_code,
                    'type' => $schedule->type,
                    'teacher_code' => $teacher_info->user_code,
                    'teacher_name' => $teacher_info->full_name
                ];
            });
            return response()->json([
                'status' => true,
                'schedules' => $schedules
            ],200);
        }
       catch(\Throwable $th){
            return $this->handleErrorNotDefine($th);
       }
    }

    public function listSchedulesForTeacher(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        try {
            $teacher_code = request()->user()->user_code;
            $now = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
            $sevenDaysLater = $now->clone()->addDays(7)->endOfDay();

            $list_schedules = Schedule::with(['classroom.subject', 'session', 'classroom'])
                ->where('teacher_code', $teacher_code)
                ->whereBetween('date', [$now, $sevenDaysLater])
                ->orderBy('date', 'asc')
                ->paginate($perPage);

            $schedules = $list_schedules->map(function ($schedule) {
                return [
                    'class_code'    => $schedule->classroom->class_code,
                    'date'          => $schedule->date,
                    'subject_name'  => $schedule->classroom->subject->subject_name,
                    'subject_code'  => $schedule->classroom->subject_code,
                    'room_code'     => $schedule->room_code,
                    'session'       => $schedule->session->value,
                    'session_code'  => $schedule->session->cate_code,
                    'session_name'  => $schedule->session->cate_name,
                    'count_users'   => $schedule->classroom?->users->count() ?? 0,
                ];
            });
            $list_schedules->setCollection($schedules);
            return response()->json($list_schedules, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listSchedulesForStudent(Request $request)
    {
        try {
            $student_code = request()->user()->user_code;
            $student = User::where('user_code', $student_code)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Lấy lịch học của sinh viên với thông tin từ 'classroom.subject' và 'session'
            // $schedules = $student->schedules()
            // ->with('classroom.subject', 'session')->get();
            $schedules = $student->schedules()
                ->with([
                    'classroom' => function ($query) {
                        // Lấy chỉ các cột cần thiết từ bảng classroom
                        $query->select('id', 'class_code', 'class_name', 'subject_code');
                        $query->with([
                            'subject' => function ($query) {
                                // Lấy chỉ các cột cần thiết từ bảng subject
                                $query->select('id', 'subject_code', 'subject_name');
                            }
                        ]);
                    },
                    'session' => function ($query) {
                        // Lấy chỉ các cột cần thiết từ bảng session
                        $query->select('id', 'cate_code', 'cate_name', 'value');
                    }
                ])
                ->select('id', 'class_code', 'room_code', 'session_code', 'teacher_code', 'date', 'type')
                ->get();
            // Tạo dữ liệu mảng để trả về
            $data = $schedules->map(function ($schedule) {
                return [
                    'date'          => $schedule->date,
                    'room_code'     => $schedule->room_code,
                    'subject_code'  => $schedule->classroom->subject_code,
                    'subject_name'  => $schedule->classroom->subject->subject_name,
                    'class_code'    => $schedule->classroom->class_code,
                    'session'       => $schedule->session->cate_name,
                    'session_time'  => $schedule->session->value,
                ];
            });

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
