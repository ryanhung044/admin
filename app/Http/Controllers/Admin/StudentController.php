<?php

namespace App\Http\Controllers\Admin;

use App\Imports\StudentImport;
use App\Exports\StudentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\DeleteStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;


class StudentController extends Controller
{

    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {
        return response()->json([
            'status' => false,
            'message' => 'Sinh viên này không tồn tại!',
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
            'message' => 'Sinh viên này đã được cập nhật trước đó, vui lòng cập nhật lại trang!'
        ], 409);
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $list_users = User::with([
                'major' => function ($query) {
                    $query->select('cate_code', 'cate_name', 'parent_code');
                },
                'semester' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'course' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                }
            ])->where('role', '3')
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('full_name', 'LIKE', "%$search%")
                            ->orWhere('user_code', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    });
                })
                ->orderBy('id', 'desc')
                ->select('id', 'user_code', 'full_name', 'email', 'phone_number', 'address', 'sex', 'place_of_grant', 'nation', 'avatar', 'role', 'is_active', 'major_code', 'course_code', 'semester_code')
                ->paginate($perPage);

            if ($list_users->isEmpty()) {
                return response()->json(
                    ['message' => 'Không có tài khoản nào!'],
                    404
                );
            }
            return response()->json($list_users, 200);
        } catch (Throwable $th) {
            return response()->json(
                [
                    'message' => 'Đã xảy ra lỗi không xác định',
                    'error' => env('APP_DEBUG') ? $th->getMessage() : "Đã xảy ra lỗi!"
                ],
                500
            );
        }
    }


    public function store(StoreStudentRequest $request)
    {
        
        try {
            $data = $request->validated();
            $newest_student_code = User::withTrashed()
                ->where('user_code', 'LIKE', 'FE%')
                ->selectRaw("MAX(CAST(SUBSTRING(user_code, 3) AS UNSIGNED)) as max_code")
                ->value('max_code');
            $current_code = $newest_student_code ?: 0;
            $new_student_code = 'FE' . str_pad($current_code + 1, 5, '0', STR_PAD_LEFT);
            $data['user_code'] = $new_student_code;
            $data['role'] = '3';
            User::create($data);

            
            return response()->json([
                'status' => true,
                'message' => 'Thêm mới sinh viên thành công!'
            ], 201);
        } catch (\Throwable $th) {
            
            return $this->handleErrorNotDefine($th);
        }
    }


    public function show(string $student_code)
    {
        try {
            $student = User::with([
                'major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'narrow_major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'semester' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'course' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
            ])->where([
                'user_code' => $student_code,
            ])->select(
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
                'narrow_major_code',
                'semester_code',
                'course_code',
                'created_at',
                'updated_at'
            )->first();

            if (!$student) {
                return $this->handleInvalidId();
            }

            $studentArray = $student->toArray();
            $studentArray['created_at'] = $student->created_at->toDateTimeString();
            $studentArray['updated_at'] = $student->updated_at->toDateTimeString();
            return response()->json($studentArray, 200);
        } catch (Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    public function update(UpdateStudentRequest $request, string $user_code)
    {

        try {

            $student = User::where('user_code', $user_code)->first();

            if (!$student) {
                return $this->handleInvalidId();
            }

            $data = $request->validated();

            // Kiểm tra updated at trong db có khác với updated at ở phiên hiện tại hay không
            // if ($student->updated_at->toDateTimeString() !== $data['updated_at']) {
            //     return $this->handleConflict();
            // }

            if (!isset($data['narrow_major_code'])) {
                $data['narrow_major_code'] = null;
            }
            $data['role'] = '3';

            $student->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thông tin sinh viên thành công!'
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    public function destroy(string $user_code)
    {

        try {


            $student = User::where('user_code', $user_code)->first();

            if (!$student) {
                return $this->handleInvalidId();
            }

            $student->delete();


            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xoá sinh viên thành công'
                ],
                200
            );
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }


    public function importStudents(Request $request)
    {
        try {
            Excel::import(new StudentImport(), $request->file('file'));
            return response()->json([
                'status' => true,
                'message' => 'Import sinh viên thành công'
            ], 201);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function exportStudents()
    {
        return Excel::download(new StudentExport, 'students.xlsx');
    }

    public function changeMajorStudent(int $id, Request $request)
    {
        try {
            $user = User::find($id);
            $user->update([
                'semester_code' => $request->semester_code,
                'major_code'    => $request->major_code
            ]);

            return response()->json([
                'message' => 'Chuyển chuyên ngành thành công',
                'student' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
    }

    public function updateActive(string $userCode)
    {
        try {
            $listStudent = User::where('user_code', $userCode)->firstOrFail();
            // dd(!$listStudent->is_active);
            $listStudent->update([
                'is_active' => !$listStudent->is_active
            ]);
            $listStudent->save();
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
