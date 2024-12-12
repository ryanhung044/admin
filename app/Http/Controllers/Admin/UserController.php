<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;


class UserController extends Controller
{

    public function index()
    {

        try {
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
            ])
                ->select('id', 'user_code', 'full_name', 'email', 'phone_number', 'address', 'sex', 'place_of_grant', 'nation', 'avatar', 'role', 'is_active', 'major_code', 'course_code', 'semester_code')
                ->paginate(20);
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

    public function getListSudent(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
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



    public function store(StoreUserRequest $request)
    {

        try {
            $data = $request->all();
            User::create($data);
            return response()->json([
                'message' => 'Thêm mới tài khoản thành công'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Đã xảy ra lỗi không xác định",
                'error' => env('APP_DEBUG') ? $th->getMessage() : 'Lỗi không xác định'
            ], 500);
        }
    }

    public function show(string $user_code)
    {
        try {
            $user = User::where([
                'user_code' => $user_code,
                'is_active' => true
            ])->first();

            if (!$user) {
                return response()->json([
                    'message' => "Tài khoản không tồn tại!"
                ], 404);
            }
            return response()->json($user, 200);
        } catch (Throwable $th) {
            return response()->json([
                'message' => "Đã xảy ra lỗi không xác định",
                'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
            ], 500);
        }
    }


    public function update(UpdateUserRequest $request, string $user_code)
    {
        try {
            $user = User::where('user_code', $user_code)->first();
            if (!$user) {
                return response()->json([
                    'message' => "Tài khoản không tồn tại!"
                ], 404);
            }
            $data = $request->all();
            $user->update($data);

            return response()->json([
                'message' => 'Cập nhật thông tin tài khoản thành công!'
            ], 200);
        } catch (ValidationException $e) {
            // Trả về lỗi đầu tiên từ validation
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Lỗi không xác định",
                'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
            ], 500);
        }
    }


    public function destroy(string $user_code)
    {
        try {

            $user = User::where('user_code', $user_code)->first();
            if (!$user) {
                return response()->json(
                    ['message' => 'Tài khoản không tồn tại'],
                    404
                );
            }
            $user->delete();
            return response()->json(
                ['message' => 'Xoá tài khoản thành công'],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Đã xảy ra lỗi không xác định',
                    'error' => env('APP_DEBUG') ? $th->getMessage() : 'Lỗi không xác định'
                ],
                500
            );
        }
    }


    public function import(Request $request)
    {
        if ($request->hasFile('data')) {
            $file = $request->file('data');

            try {
                // Sử dụng thư viện Excel để import dữ liệu
                Excel::import(new UsersImport, $file);  // Xử lý import với UsersImport
                return response()->json(['message' => 'Import thành công!'], 200);
            } catch (\Exception $e) {
                // Xử lý lỗi nếu có
                return response()->json(['error' => 'Có lỗi xảy ra khi import dữ liệu: ' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Không có file để import'], 400);
        }
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
