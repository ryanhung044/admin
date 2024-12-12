<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ScheduleStudent;
use Illuminate\Http\Request;

class ExamDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */

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
            $now = now()->format('Y-m-d');
            
            $exam_days = ScheduleStudent::select('schedule_id', 'student_code')->with([
                'schedule' => function($query){
                    $query->select('id','class_code', 'room_code', 'session_code','date', 'teacher_code');
                },
                'schedule.session' => function($query){
                    $query->select('cate_code', 'cate_name', 'value');

                },
                'schedule.classroom' => function($query){
                    $query->select('class_code', 'subject_code');
                },
                'schedule.classroom.subject' => function($query){
                    $query->select('subject_name', 'subject_code');
                }
            ])->whereHas('schedule', function($query) use ($now){
                return $query->where('date', '>=', $now)->where('type', 'exam');
            })->where('student_code', $student_code)->get()
            ->map(function($exam_day){
                $schedule_info = optional($exam_day->schedule);
                $session_info = optional($schedule_info->session);
                $classroom_info = optional($schedule_info->classroom);
                $subject_info = $classroom_info->subject;
                
                return [
                    'subject_code' => $subject_info->subject_code,
                    'subject_name' => $subject_info->subject_name,
                    'date' => $schedule_info->date,
                    'room_code' => $schedule_info->room_code,
                    'session_name' => $session_info->cate_name,
                    'session_value' => $session_info->value,
                    'teacher_code' => $schedule_info->teacher_code
                ];
            });
            return response()->json([
                'status' => true,
                'examDays' => $exam_days
            ]);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
