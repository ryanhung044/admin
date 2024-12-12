<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class ShowListScheduleCanBeTransfer extends FormRequest
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
            'course_code' => 'required|exists:categories,cate_code',
            'class_code' => 'required|exists:classrooms,class_code',
            'session_code' => 'required|exists:categories,cate_code'
        ];
    }

    public function messages(){
        return [
            'class_code.required' => 'Bạn chưa truyền lên thông tin lớp học hiện tại',
            'class_code.exists' => 'Lớp học không tồn tại!',
            'course_code.required' => 'Bạn chưa truyền lên thông tin khoá học',
            'course_code.exists' => 'Khoá học không tồn tại!',
            'session_code.required' => 'Bạn chưa truyền lên thông tin ca học!',
            'session_code.exists' => 'Ca học không tồn tại!'
        ];
    }

    protected $stopOnFirstFailure = true;
}
