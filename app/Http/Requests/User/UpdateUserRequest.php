<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
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

            'user_code' => 'required|unique:users,user_code,' . $this->route('user') . ',user_code',

            'full_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->route('user') . ',user_code',
            'phone_number' => 'required|regex:/^(0[0-9]{9})$/',
            'address' => 'required',
            'citizen_card_number' => 'required',
            'place_of_grant' => 'required',
            'nation' => 'required',
        ];
    }

    public function messages(){
        return [
            'user_code.required' => 'Bạn chưa nhập mã user',
            'full_name.required' => 'Bạn chưa nhập họ và tên',
            'email.required' => 'Bạn chưa nhập email',
            'email.unique' => 'Email đã được sử dụng',
            'email.email' => 'Định dạng Email không hợp lệ',
            'phone_number.required' => 'Bạn chưa nhập số đt',
            'phone_number.regex' => 'Số điện thoại không hợp lệ',
            'address.required' => 'Bạn chưa nhập địa chỉ',
            'citizen_card_number.required' => 'Bạn chưa nhập số CCCD',
            'place_of_grant.required' => 'Bạn chưa nhập nơi cấp CCCD',
            'nation.required' => 'Bạn chưa nhập Dân tộc của bạn'
        ];
    }
    protected $stopOnFirstFailure = true;
}
