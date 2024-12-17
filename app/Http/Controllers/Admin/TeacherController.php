<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\DeleteTeacherRequest;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function handleInvalidId()
    {
        return response()->json([
            'status' => false,
            'message' => 'Giảng viên này không tồn tại!',
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

    public function handleConflict(){
        return response()->json([
            'status' => false,
            'message' => 'Giảng viên này đã được cập nhật trước đó, vui lòng cập nhật lại trang!'
        ],409);
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $orderBy = $request->input('orderBy', 'created_at'); 
            $orderBy === $orderBy; 
            $orderDirection = $request->input('orderDirection', 'asc'); 
            $teachers = User::where([
                'role' => '2'
            ])->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('user_code', 'LIKE', "%$search%")
                          ->orWhere('full_name', 'LIKE', "%$search%")
                          ->orWhere('email', 'LIKE', "%$search%");
                });
            })
            ->select(
                'user_code',
                'full_name',
                'email',
                'sex',
                'is_active',
                'deleted_at',
            )->withTrashed()
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);

            return response()->json($teachers, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        
        // try {
        //     $data = $request->validated();

        //     $newest_teacher_code = User::where('user_code', "LIKE", "TC%")
        //     ->orderBy('user_code', 'desc')->pluck('user_code')->first();

        //     $new_code = $newest_teacher_code ? (int) substr($newest_teacher_code, 2) : 0;
        //     $new_teacher_code = "TC" . str_pad($new_code + 1, 5, 0, STR_PAD_LEFT);
            
        //     $data['user_code'] = $new_teacher_code;
        //     $data['role'] = '2';
        //     User::create($data);
        //     

        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Thêm mới giảng viên thành công!'
        //     ],201);
        // } catch (\Throwable $th) {
        //     
        //     return $this->handleErrorNotDefine($th);
        // }

        try {
            $data = $request->validated();
            $newest_teacher_code = User::withTrashed()
                ->where('user_code', 'LIKE', 'TC%')
                ->selectRaw("MAX(CAST(SUBSTRING(user_code, 3) AS UNSIGNED)) as max_code")
                ->value('max_code');
            $current_code = $newest_teacher_code ?: 0;
            $new_teacher_code = 'TC' . str_pad($current_code + 1, 5, '0', STR_PAD_LEFT);
            $data['user_code'] = $new_teacher_code;
            $data['role'] = '2';
            User::create($data);

            
            return response()->json([
                'status' => true,
                'message' => 'Thêm mới giảng viên thành công!'
            ], 201);
        } catch (\Throwable $th) {
            
            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $teacher_code)
    {
        try {
            $teacher = User::with([
                'major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                }
            ])->where('user_code', $teacher_code)
                ->select(
                    'user_code',
                    'full_name',
                    'email',
                    'phone_number',
                    'address',
                    'sex',
                    'birthday',
                    'citizen_card_number',
                    'issue_date',
                    'place_of_grant',
                    'nation',
                    'role',
                    'is_active',
                    'major_code',
                    'created_at',
                    'updated_at'
                )->first();

                if(!$teacher){
                    return response()->json([
                        'status' => false,
                        'message' => 'Giảng viên này không tồn tại!'
                    ]);
                }
            return response()->json($teacher);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, string $teacher_code)
    {
        try {

            $data = $request->validated();
            // return response()->json($data);
            $teacher = User::where('user_code', $teacher_code)->first();

            if(!$teacher){
                return $this->handleInvalidId();
            }

            // if($teacher->updated_at->toDateTimeString() !== $data['updated_at']){
            //     return $this->handleConflict();
            // }

            if(!isset($data['narrow_major_code'])){
                $data['narrow_major_code'] = null;
            }

            $teacher->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Chỉnh sửa thông tin giảng viên thành công!'
            ],200);
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($teacher_code)
    {

        try {


            $teacher = User::where('user_code', $teacher_code)->first();

            if (!$teacher) {
                return $this->handleInvalidId();
            }


            // $is_exist_classroom_teaching = Classroom::where('user_code', $teacher->user_code)->where('is_active', true)->exists();
            
            // if($is_exist_classroom_teaching){
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Giảng viên này đang có lớp học đảm nhiệm, không thể xoá!'
            //     ],403);
            // }

            $teacher->delete();

            return response()->json([
                'status' => true,
                'message' => 'Xóa giảng viên thành công!'
            ], 200);
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function updateActive(string $userCode)
    {
        try {
            $listTeacher = User::where('user_code', $userCode)->firstOrFail();
            // dd(!$listTeacher->is_active);
            $listTeacher->update([
                'is_active' => !$listTeacher->is_active
            ]);
            $listTeacher->save();
            return response()->json([
                'message' => 'Cập nhật thành công',
                'error' => false
            ], 200);
        } catch (\Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định',
                'error' => true
            ], 500);
        }
    }
}
