<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class HandleTransferSchedule extends FormRequest
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
            'class_code_current' => 'required|exists:classrooms,class_code',
            'course_code' => 'required|exists:categories,cate_code',
            'class_code_target' => 'required|exists:classrooms,class_code'
        ];
    }

    public function messages(){
        return [
            'class_code_current.required' => 'Bạn cần cung cấp thông tin lớp học hiện tại!',
            'class_code_current.exists' => 'Lớp học này không tồn tại!',
            'course_code.required' => 'Bạn cần cunng cấp khoá học hiện tại của bạn!',
            'course_code.exists' => 'Khoá học này không tồn tại!',
            'class_code_target.required' => 'Bạn cần cung cấp thông tin lớp học cần đổi!',
            'class_code_target.exists' => 'Lớp học cần đổi không tồn tại!'
        ];
    }

    protected $stopOnFirstFailure = true;
}
