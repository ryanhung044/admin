<?php

namespace App\Http\Controllers\Auth;




use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Validator;

class AuthController extends Controller
{

    public function handleErrorNotDefine($th){
        return response()->json([
            'status' => false,
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ],500);
    }
    public function login(LoginRequest $request)
    {
        // try {
            $data = $request->validated();
            $user = User::firstWhere('email',$data['email']);

            if(!$user || !Hash::check($data['password'], $user['password'])){
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản hoặc mật khẩu không chính xác'
                ], 401);
            }

                // Tạo token khi tài khoản đúng
                $token = $user->createToken($user->id)->plainTextToken;

                return response()->json([
                    'status' => true,
                    'user' => $user,
                    'token' => [
                        'access_token' => $token,
                        'type_token' => 'Bearer'
                    ]
                ], 200);


        // } catch (\Throwable $th) {
        //     return $this->handleErrorNotDefine($th);
        //     }
    }

    public function logout(Request $request)
    {
        try {

            // Lấy token từ frontend gửi lên
        $token  = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền truy cập!"
            ], 401);
        }
        // Tìm bản ghi token trong db
        $accessToken = PersonalAccessToken::findToken($token);

        // Trường hợp tìm thấy bản ghi trong db có token vừa nhận được
        if ($accessToken) {
            $accessToken->delete();
            return response()->json([
                'status' => true,
                'message' => "Đăng xuất thành công!"
            ], 200);
        }

            // Trường hợp không tìm thấy token trùng khớp hoặc đã hết hạn trong db
            return response()->json([
                'status' => false,
                'message' => "Token không hợp lệ hoặc đã hết hạn!"
            ], 401);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }

    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {

            $user_code = $request->user()->user_code;

            $user = User::where('user_code', $user_code)->firstOrFail();
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Mật khẩu hiện tại không khớp',
                    'password_current' => $request->current_password,
                    'password_real' => $user->password
                ], 500);
            }

            $user->update(['password'=> $request->new_password]);

            return response()->json(['message' => 'Mật khẩu đã được thay đổi thành công.','data'=>$user]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


}
