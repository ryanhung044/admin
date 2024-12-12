<?php

namespace App\Http\Controllers\Student;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Classroom;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $userCode = $request->user()->user_code;
            // $userCode = 'student05';

            $semesterCodeUser = User::where('user_code', $userCode)
                                    ->select('semester_code')
                                    ->first();

            $semesterCode = $request->input('search') ?: $semesterCodeUser->semester_code;
        
            $listSemester = Category::where('type', 'semester')
                ->where('is_active', '1')
                ->where('cate_code', '<=', $semesterCodeUser->semester_code) 
                ->orderBy('cate_code', 'asc')
                ->select('cate_code', 'cate_name')
                ->get();
           
            $attendances = Attendance::whereHas('classroom.subject', function ($query) use ($semesterCode) {
                                $query->where('semester_code', $semesterCode);
                            })
                            ->where('student_code', $userCode)
                            ->with(['classroom' => function ($query) {
                                $query->select('class_code', 'class_name', 'subject_code', 'user_code');

                            }, 'classroom.subject' => function ($query) {
                                $query->select('subject_code', 'subject_name', 'semester_code');

                            }, 'classroom.teacher' => function ($query) {
                                $query->select('user_code', 'full_name');

                            },'classroom.schedules' => function ($query) {
                                $query->select('date', 'room_code', 'class_code', 'session_code')
                                    ->with('session', function ($query) {
                                        $query->select('cate_code', 'cate_name');
                                    });
                                
                            }
                        ])
                        ->get(['id', 'student_code', 'class_code', 'status', 'noted', 'date']);
                            
            $result = $attendances->groupBy('class_code')->map(function ($classGroup) {
                $firstAttendance = $classGroup->first();
                // Lấy tất cả các lịch học từ lớp học
                $schedules = $firstAttendance->classroom->schedules;
                // dd($schedules);
                // Gộp dữ liệu điểm danh và lịch học
                $attendanceData = $classGroup->map(function ($attendance) use ($schedules) {
                    $currentDate = Carbon::now()->toDateString();
                    $attendanceDate = Carbon::parse($attendance->date)->toDateString();
            
                    return [
                        'date' => $attendanceDate,
                        'cate_name' => $schedules->firstWhere('date', $attendanceDate)->session->cate_name ?? null,
                        'full_name' => $attendance->classroom->teacher->full_name,
                        'status' => $attendanceDate > $currentDate ? null : $attendance->status,
                        'noted' => $attendanceDate > $currentDate ? 'Chưa điểm danh' : $attendance->noted,
                    ];
                });
                // Không xóa đoạn comment này
                // $attendanceDates = $attendanceData->pluck('date')->toArray();
            
                // // Lấy các lịch học không có trong attendance
                // $scheduleData = $schedules->filter(function ($schedule) use ($attendanceDates) {
                //     return !in_array(Carbon::parse($schedule->date)->toDateString(), $attendanceDates);
                // })->map(function ($schedule) {
                //     $scheduleDate = Carbon::parse($schedule->date)->toDateString();
                //     $currentDate = Carbon::now()->toDateString();
            
                //     return [
                //         'date' => $scheduleDate,
                //         'cate_name' => optional($schedule->session)->cate_name ?? null,
                //         'full_name' => null,
                //         'status' => $currentDate > $scheduleDate ? 'absent' : null, // Trạng thái là 'absent' nếu ngày hiện tại quá ngày học
                //         'noted' => $currentDate > $scheduleDate ? 'Vắng mặt' : 'Chưa điểm danh',
                //     ];
                // });
            
                // // Kết hợp attendance và schedule, sắp xếp theo ngày
                // $finalData = $attendanceData->merge($scheduleData)->sortBy('date')->values();
                // $totalSchedule = $finalData->count();
                // Đếm số lần status là 'absent'
                $totalSchedule = $attendanceData->count();
                $absentCount = $attendanceData->where('status', 'absent')->count();
            
                return [
                    'class_code' => $firstAttendance->class_code,
                    'class_name' => $firstAttendance->classroom->class_name,
                    'subject_name' => $firstAttendance->classroom->subject->subject_name,
                    'total_absent' => $absentCount,
                    'total_schedule' => $totalSchedule,
                    'attendance' => $attendanceData,
                ];
            })->values()->all();
                            

            return response()->json([
                'semesters' => $listSemester,
                'attendances' => $result,
                'semesterCode' => $semesterCode,
            ], 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!' .$th->getMessage()
            ], 500);
        }
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
    public function show(string $semesterCode)
    {

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
