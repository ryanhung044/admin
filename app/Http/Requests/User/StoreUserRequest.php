<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'user_code' => 'required|unique:users,user_code',
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone_number' => 'required|regex:/^(0[0-9]{9})$/',
            'address' => 'required',
            'sex' => 'required',
            'birthday' => 'required|date_format:Y-m-d',
            'citizen_card_number' => 'required|unique:users,citizen_card_number',
            'issue_date' => 'required|date_format:Y-m-d',
            'place_of_grant' => 'required',
            'nation' => 'required',
        ];
    }

    public function messages(){
        return [
            // 'user_code.required' => 'Bạn chưa nhập mã user',
            // 'user_code.unique' => 'Mã user đã được sử dụng',
            'full_name.required' => 'Bạn chưa nhập họ và tên',
            'email.required' => 'Bạn chưa nhập email',
            'email.email' => 'Định dạng Email không hợp lệ',
            'email.unique' => 'Email này đã được sử dụng',
            'password.required' => 'Bạn chưa nhập password',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự',
            'phone_number.required' => 'Bạn chưa nhập số đt',
            'phone_number.regex' => 'Số điện thoại không hợp lệ',
            'address.required' => 'Bạn chưa nhập địa chỉ',
            'sex.required' => 'Bạn chưa nhập giới tính',
            'birthday.required' => 'Bạn chưa nhập ngày tháng năm sinh',
            'birthday.date_format' => 'Ngày tháng năm sinh không hợp lệ',
            'citizen_card_number.required' => 'Bạn chưa nhập số CCCD',
            'citizen_card_number.unique' => 'Mã CCCD này đã được sử dụng',
            'issue_date.required' => 'Bạn chưa nhập ngày cấp CCCD',
            'issue_date.date_format' => 'Ngày cấp CCCD không hợp lệ',
            'place_of_grant.required' => 'Bạn chưa nhập nơi cấp CCCD',
            'nation.required' => 'Bạn chưa nhập Dân tộc của bạn'
        ];
    }
    protected $stopOnFirstFailure = true;

}
