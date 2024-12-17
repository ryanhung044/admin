<?php

namespace App\Http\Controllers;

use App\Jobs\SendResetPasswordEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function Laravel\Prompts\table;

class ForgetPasswordController extends Controller
{
    function forgetPassword(){
        return view('auth.forgot-password');
    }

    function forgetPasswordPost(Request $request)
    {
        try {
            // Validate email
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ], [
                'email.exists' => 'Email không tồn tại trong hệ thống.',
                'email.email'  => 'Email không hợp lệ'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first('email') // Lấy thông báo lỗi đầu tiên cho email
                ], 422); // Sử dụng mã lỗi 422 thay vì 404
            }

            $email = $request->input('email');

            // Generate token
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ]
            );

            // Create reset password URL
            $resetUrl = url("https://feduvn.com/reset-password/$token/$email");

            // Send email via Job
            SendResetPasswordEmail::dispatch($email, $resetUrl);

            return response()->json(['message' => 'Vui lòng kiểm tra Email.']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'], 500);
        }
    }


    function resetPassword(Request $request){
        $token = $request->query('token');
        $email = $request->query('email');

        return view('auth.reset-password', compact('token', 'email'));
    }

    function resetPasswordPost(Request $request){
       try{
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $password_reset = DB::table('password_reset_tokens')->where([
                    'email' => $request->email,
                    'token' => $request->token
                 ])->first();

        if(!$password_reset){
            return response()->json(['message' => 'token không tồn tại hoặc hết hạn']);
        }

        $userUpdate = User::where('email', $request->email)
        ->update(['password' => bcrypt($request->password)]);

         DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->delete();


        return response()->json(['message' => 'Đổi mật khẩu thành công','data'=> $userUpdate], 200);
       }
       catch(\Throwable $th){
        return response()->json(['error' => $th->getMessage()]);
       }

    }
}


