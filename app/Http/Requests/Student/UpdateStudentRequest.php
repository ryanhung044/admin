<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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


            'full_name' => 'required|max:50',
            'email' => 'required|email|max:255|unique:users,email,' . $this->route('student') . ',user_code',
            // 'password' => 'required|max:255|min:6',
            'phone_number' => 'required|max:20|regex:/^(0[3-9][0-9]{8})$/',
            'address' => 'required|max:200',
            'sex' => 'required|in:male,female',
            'birthday' => 'bail|required|date_format:Y-m-d|before_or_equal:' . today()->subYears(16)->toDateString(),
            'citizen_card_number' => 'required|max:20|unique:users,citizen_card_number,' . $this->route('student') . ',user_code',
            'issue_date' => 'bail|required|date_format:Y-m-d|before_or_equal:today',
            'place_of_grant' => 'required|max:200',
            'nation' => 'required|max:50',
            'major_code' => 'required|exists:categories,cate_code',
            'narrow_major_code' => 'nullable|exists:categories,cate_code',
            'semester_code' => 'required|exists:categories,cate_code',
            'course_code' => 'required|exists:categories,cate_code',
            // 'updated_at' =>'required|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages(){
        return [


            'full_name.required' => 'Bạn chưa nhập họ và tên!',
            'full_name.max' => 'Họ tên không được quá :max ký tự',

            'email.required' => 'Bạn chưa nhập email!',
            'email.email' => 'Định dạng Email không hợp lệ!',
            'email.max' => 'Email không được quá :max ký tự',

            'email.unique' => 'Email này đã được sử dụng!',
            // 'password.required' => 'Bạn chưa nhập password!',
            // 'password.min' => 'Mật khẩu phải có ít nhất :min ký tự!',
            // 'password.max' => 'Mật khẩu không được quá :max ký tự!',

            'phone_number.required' => 'Bạn chưa nhập số đt!',
            'phone_number.regex' => 'Số điện thoại không hợp lệ!',
            'phone_number.max' => 'Số điện thoại không được quá :max ký tự!',

            'address.required' => 'Bạn chưa nhập địa chỉ!',
            'address.max' => 'Địa chỉ không dài quá :max ký tự!',

            'sex.required' => 'Bạn chưa nhập giới tính!',
            'sex.in' => 'Giới tính không hợp lệ!',
            'birthday.required' => 'Bạn chưa nhập ngày tháng năm sinh!',
            'birthday.date_format' => 'Định dạng Ngày tháng năm sinh không hợp lệ!',
            'birthday.before_or_equal' => 'Ngày tháng năm sinh không hợp lệ!',
            'citizen_card_number.required' => 'Bạn chưa nhập số CCCD!',
            'citizen_card_number.max' => 'Số CCCD không được quá :max ký tự!',

            'citizen_card_number.unique' => 'Mã CCCD này đã được sử dụng!',
            'issue_date.required' => 'Bạn chưa nhập ngày cấp CCCD!',
            'issue_date.date_format' => 'Định dạng Ngày cấp CCCD không hợp lệ!',
            'issue_date.before_or_equal' => 'Ngày cấp CCCD không hợp lệ!',
            'place_of_grant.required' => 'Bạn chưa nhập nơi cấp CCCD!',
            'place_of_grant.max' => 'Nơi cấp CCCD không được quá :max ký tự!',

            'nation.required' => 'Bạn chưa nhập Dân tộc!',
            'nation.max' => 'Dân tộc không được dài quá :max ký tự!',

            'major_code.required' => 'Bạn chưa chọn chuyên ngành!',
            'major_code.exists' => 'Chuyên ngành này không tồn tại!',
            'narrow_major_code.exists' => 'Chuyên ngành con này không tồn tại!',
            'semester_code.required' => 'Bạn chưa chọn kỳ học!',
            'semester_code.exists' => 'Kỳ học không tồn tại!',
            'course_code.required' =>  'Bạn chưa chọn khoá học!',
            'course_code.exists' => 'Khoá học này không tồn tại!',
            // 'updated_at.required' => 'Thiếu thời gian cập nhật gần đây của bản ghi này',
            // 'updated_at.date_format' => 'Thời gian cập nhật gần đây có định dạng không hợp lệ!'
        ];
    }
    protected $stopOnFirstFailure = true;
}
