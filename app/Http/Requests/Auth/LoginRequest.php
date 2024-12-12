<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => "required|email",
            'password' => 'required|min:6'
        ];
    }

    public function messages(){
        return [
            'email.required' => 'Bạn chưa nhập email',
                    'password.required' => "Bạn chưa nhập password",
                    'email.email' => 'Email không hợp lệ',
                    'password.min' => "Mật khẩu phải tối thiểu 6 kí tự"
        ];
    }

    protected $stopOnFirstFailure = true;
}
