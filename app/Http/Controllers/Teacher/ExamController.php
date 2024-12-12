<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreStudentToExamDayRequest;
use App\Models\Classroom;
use App\Models\ClassroomUser;
use App\Models\Schedule;
use App\Models\ScheduleStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function handleInvalidId()
    // {
    //    return response()->json([
    //        'message' => 'Không có lịch thi nào!',
    //    ], 404);
    // }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
       return response()->json([
           'message' => "Đã xảy ra lỗi không xác định",
           'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
       ], 500);
    }
    public function listExamDays(string $class_code){
        try {

            $teacher_code = request()->user()->user_code;

            $classroom = Classroom::
            where([
                'class_code' =>  $class_code,
                'user_code' => $teacher_code
            ])->first();

            if(!$classroom){
                return response()->json([
                    'status' => false,
                    'message' => 'Lớp học này không tồn tại!'
                ],404);
            }

            $exam_days = Schedule::select('id','class_code', 'room_code', 'session_code', 'date')->withCount('students')
            ->with([
                'classroom' => function($query){
                    $query->select('class_code', 'subject_code');
                },
                'classroom.subject' => function($query){
                    $query->select('subject_code', 'subject_name');
                },
                'room' => function($query){
                    $query->select('cate_name', 'cate_code');
                },
                'session' => function($query){
                    $query->select('cate_name', 'cate_code', 'value');
                }
            ])
            ->where([
                'class_code' => $classroom->class_code,
                'type' => 'exam'
            ])->get()->map(function($exam_day){
                $subject_info = optional($exam_day->classroom->subject);
                $room_info = optional($exam_day->room);
                $session_info = optional($exam_day->session);
                return [
                    'id' => $exam_day->id,
                    'subject_code' => $subject_info->subject_code,
                    'subject_name' => $subject_info->subject_name,
                    'date' => $exam_day->date,
                    'room_code' => $room_info->cate_code,
                    'room_name' => $room_info->cate_name,
                    'session_name' => $session_info->cate_name,
                    'session_value' => $session_info->value,
                    'students_count' => $exam_day->students_count
                ];
            });
            // return response()->json($exam_days);
            $students_has_been_arrange = ScheduleStudent::whereIn('schedule_id', $exam_days->pluck('id'))
            ->select('schedule_id', 'student_code')->get()->keyBy('student_code');
            
            $students = ClassroomUser::with([
                'user' => function($query){
                    $query->select('user_code', 'full_name');
                }
            ])->where([
                'class_code' => $class_code, 
                'is_qualified' => true
            ])
            ->select('class_code', 'user_code', 'is_qualified')
            ->get()->map(function($student) use ($students_has_been_arrange){

                $user_info = optional($student->user);
                $exam_day = null;
                if(isset($students_has_been_arrange[$student->user->user_code])){
                    $exam_day = $students_has_been_arrange[$student->user->user_code]->schedule_id;
                }
                return [
                    'user_code' => $user_info->user_code,
                    'user_name' => $user_info->full_name,
                    'exam_day' => $exam_day
                ];
            });
        
            return response()->json([
                'status' => true,
                'class_code'=> $classroom->class_code,
                'exam_days' => $exam_days,
                'students' => $students
            ]);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
     }
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentToExamDayRequest $request)
    {
        try {
            DB::beginTransaction();

            $data_from_request = $request->validated();
            // Lấy ra danh sách sinh viên và ngày thi tương ứng với từng sinh viên
            $students = $data_from_request['students'];
            $class_code = $data_from_request['class_code'];
            $classroom = Classroom::select('class_code', 'class_name')->with(['schedules' => function($query){
                $query->where('type', 'exam')->select('id', 'class_code')->lockForUpdate();
            }])->where('class_code', $class_code)
            ->first();

            if(!$classroom){
                return response()->json([
                    'status' => false,
                    'message' => 'Lớp học không tồn tại!'
                ],404);
            }

            // Xoá các bản ghi cũ
            ScheduleStudent::whereIn('schedule_id', $classroom->schedules->pluck('id'))->delete();

            $students_should_add_examday = collect($students)->filter(function($student){
                return !is_null($student['exam_day']);
            })->map(function($student){
                return [
                    'student_code' => $student['user_code'],
                    'schedule_id' => $student['exam_day']
                ];
            });


            ScheduleStudent::insert($students_should_add_examday->toArray());

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Lưu sinh viên vào ca thi thành công!'
            ],201);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $exam_day)
    {
       
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
