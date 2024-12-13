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

    function forgetPasswordPost(Request $request){
        try{
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = $request->input('email');
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ]
            );
            $resetUrl = url("http://localhost:5173/reset-password/$token/$email");

            // Gửi email qua Job
            SendResetPasswordEmail::dispatch($email, $resetUrl);

            return response()->json(['message' => 'Vui lòng kiểm tra Email']);

        }catch(\Throwable $th){
            return response()->json(['message' => $th->getMessage()]);
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

        $password_reset = DB::table('password_reset_tokens')
                ->where([
                    'email' => $request->email,
                    'token' => $request->token
                 ])->first();

        if(!$password_reset){
            return response()->json(['message' => 'token không tồn tại hoặc hết hạn']);
        }

        User::where('email',$request->email)->update(['password' => bcrypt($request->password)]);
        return response()->json(['message' => 'Đổi mật khẩu thành công'], 200);
       }
       catch(\Throwable $th){
        return response()->json(['error' => $th->getMessage()]);
       }

    }
}


