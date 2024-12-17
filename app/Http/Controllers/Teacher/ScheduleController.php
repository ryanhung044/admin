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
            // $perPage = $request->input('per_page', 10);
            // $search = $request->input('search');
            // $orderBy = $request->input('orderBy', 'created_at'); 
            // $orderDirection = $request->input('orderDirection', 'asc'); 

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
                // ->orderBy($orderBy, $orderDirection)
                // ->paginate($perPage);

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
        try {
            // Lấy các tham số từ request
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $orderBy = $request->input('orderBy', 'date'); 
            $orderDirection = $request->input('orderDirection', 'asc'); 
            $teacher_code = request()->user()->user_code;
    
            // Lấy thời gian hiện tại và thời gian 7 ngày sau
            $now = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
            $sevenDaysLater = $now->clone()->addDays(7)->endOfDay();
    
            // Lấy danh sách lịch học cho giảng viên
            $list_schedules = Schedule::with(['classroom.subject', 'session', 'classroom'])
                // ->withCount('classroom.users as users_count')
                ->where('teacher_code', $teacher_code)
                ->whereBetween('date', [$now, $sevenDaysLater]);
    
            // Nếu có tham số tìm kiếm (search), thêm vào điều kiện tìm kiếm
            if ($search) {
                $list_schedules->where(function ($query) use ($search) {
                    $query->where('schedules.class_code', 'LIKE', "%$search%") // Chỉ định rõ bảng schedules
                          ->orWhere('schedules.room_code', 'LIKE', "%$search%") // Chỉ định rõ bảng schedules
                          ->orWhere('schedules.date', 'LIKE', "%$search%") // Chỉ định rõ bảng schedules
                          ->orWhereHas('classroom.subject', function ($q) use ($search) {
                              $q->where('subjects.subject_name', 'LIKE', "%$search%") // Chỉ định rõ bảng subjects
                                ->orWhere('subjects.subject_code', 'LIKE', "%$search%"); // Chỉ định rõ bảng subjects
                          })
                          ->orWhereHas('session', function ($q) use ($search) {
                              $q->where('categories.cate_name', 'LIKE', "%$search%"); // Chỉ định rõ bảng categories
                          })
                        //   ->orWhereHas('classroom.users', function ($q) use ($search) {
                        //     $q->whereRaw("COUNT(users.id) LIKE ?", ["%$search%"]); // Sửa câu lệnh COUNT thành phù hợp với logic
                        // });
                          ;
                });
                // $list_schedules->orHaving('users_count', 'LIKE', "%$search%");
            }
            // Sắp xếp theo các trường khác nhau
            if ($orderBy == 'subject_code') {
                $list_schedules->join('classrooms', 'schedules.class_code', '=', 'classrooms.class_code')
                           ->join('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code')
                           ->orderBy('subjects.subject_code', $orderDirection);
            } elseif ($orderBy == 'session_name' || $orderBy == 'session') {
                $list_schedules->join('categories', 'schedules.session_code', '=', 'categories.cate_code')
                               ->orderBy('categories.cate_name', $orderDirection);
            } elseif ($orderBy == 'count_users') {
                $list_schedules->withCount('users as users_count') // Tính số lượng users
                               ->orderBy('users_count', $orderDirection);
            } else if ($orderBy == 'subject_name') {
                $list_schedules->join('classrooms', 'schedules.class_code', '=', 'classrooms.class_code')
                            ->join('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code')
                            ->orderBy('subjects.subject_name', $orderDirection);
            } else {
                // Các trường hợp sắp xếp khác
                $list_schedules->orderBy($orderBy, $orderDirection);
            }
    
            // Áp dụng phân trang sau khi đã sắp xếp
            $list_schedules = $list_schedules->paginate($perPage);
    
            // Chuyển đổi dữ liệu theo yêu cầu
            $schedules = $list_schedules->getCollection()->map(function ($schedule) {
                return [
                    'class_code'    => $schedule->classroom->class_code,
                    'date'          => $schedule->date,
                    'subject_name'  => $schedule->classroom->subject->subject_name,
                    'subject_code'  => $schedule->classroom->subject_code,
                    'room_code'     => $schedule->room_code,
                    'session'       => $schedule->session->value,
                    'session_code'  => $schedule->session->cate_code,
                    'session_name'  => $schedule->session->cate_name,
                    'count_users'   => $schedule->classroom->users->count() ?? 0,
                    'session_start' => $schedule->session->start,
                ];
            });
    
            // Đặt lại collection đã chuyển đổi vào paginate
            $list_schedules->setCollection($schedules);
    
            // Trả về kết quả
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
