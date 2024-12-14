<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Classroom;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use Carbon\Carbon;

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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $classCode = $request->input('search');
            $classrooms = Classroom::query()
                ->when($classCode, function ($query, $classCode) {
                    return $query
                        ->where('class_code', 'like', "%{$classCode}%");
                })
                ->paginate(10);
            if ($classrooms->isEmpty()) {

                return $this->handleInvalidId();
            }

            return response()->json($classrooms, 200);
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request)
    {
        try {
            $attendances = $request->except('_token');

            Attendance::create($attendances);

            return response()->json($attendances, 200);
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $classCode)
    {
        // try {
        $attendances = Attendance::where('class_code', $classCode)
            ->with('user', 'schedule.session')
            ->get()
            ->map(function ($attendance) {
                // if (Carbon::parse($attendance->date)->isFuture()) {
                //     $attendance->status = 'pending';
                // }

                $currentDateTime =  now()->format('Y-m-d H:i');
                $schedule = $attendance->schedule;
                $session = $schedule ? $schedule->session : null;
                $timeRange = [];
                if ($session && $session->value) {
                    $timeRange = json_decode($session->value, true); // Giải mã JSON
                }
                // return $currentDateTime;

                if ($timeRange['start'] && $timeRange['end']) {
                    $startDateTime = Carbon::parse($attendance->date . ' ' . $timeRange['start']);
                    $endDateTime = Carbon::parse($attendance->date . ' ' . $timeRange['end']);
                    $startDateTimeStr = $startDateTime->format('Y-m-d H:i');
                    $endDateTimeStr = $endDateTime->format('Y-m-d H:i');
                    // return ($currentDateTime);
                    if ($currentDateTime < $startDateTimeStr) {
                        // Nếu chưa đến thời gian bắt đầu, trạng thái là pending
                        $attendance->status = 'pending';
                    }
                }
                return [
                    'id' => $attendance->id,
                    'student_code' => $attendance->student_code,
                    'full_name' => $attendance->user ? $attendance->user->full_name : null,
                    'class_code' => $attendance->class_code,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'noted' => $attendance->noted,
                ];
            });
        // $attendances = Attendance::where('class_code', $classCode)->with('classroomUser')->get();
        if (!$attendances) {

            return $this->handleInvalidId();
        } else {

            return response()->json($attendances, 200);
        }
        // } catch (\Throwable $th) {

        //     return $this->handleErrorNotDefine($th);
        // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, string $classCode)
    {
        // return $request;
        DB::beginTransaction();
        try {
            $attendances = $request->validated();

            // Kiểm tra nếu dữ liệu hợp lệ
            if (empty($attendances) || !is_array($attendances)) {
                return response()->json(['error' => 'No attendance data provided or invalid format.'], 400);
            }

            foreach ($attendances as $atd) {
                $noted = $atd['noted'] ?? "";

                // Sử dụng updateOrInsert để tối ưu hóa
                Attendance::updateOrInsert(
                    [
                        'student_code' => $atd['student_code'],
                        'class_code' => $classCode,
                        'date' => $atd['date'],
                    ],
                    [
                        'status' => $atd['status'],
                        'noted' => $noted,
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Attendance updated successfully.',
                'data' => $attendances,
            ], 200);
        } catch (Throwable $th) {
            DB::rollBack();

            // Trả về thông báo lỗi chi tiết
            return response()->json([
                'error' => 'Failed to update attendance.',
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $attendances = Attendance::where('id', $id)->lockForUpdate()->first();
            if (!$attendances) {
                DB::rollBack();

                return $this->handleInvalidId();
            } else {
                $attendances->delete($attendances);
                DB::commit();

                return response()->json([
                    'message' => 'Xoa thanh cong'
                ], 200);
            }
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function bulkUpdateType(Request $request)
    {
        try {
            $actives = $request->input('status'); // Lấy dữ liệu từ request            
            foreach ($actives as $student_code => $active) {
                // Tìm Attendance theo ID và cập nhật trường 'status'
                $attendance = Attendance::findOrFail($student_code);
                $attendance->status = $active;
                $attendance->save();
            }

            return response()->json([
                'message' => 'Trạng thái đã được cập nhật thành công!'
            ], 200);
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }
}
