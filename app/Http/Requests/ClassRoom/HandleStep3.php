<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class HandleStep3 extends FormRequest
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
            'room_code' => 'required|exists:categories,cate_code',
            'subject_code' => 'required|exists:subjects,subject_code',
            'course_code' => 'required|exists:categories,cate_code',
        ];
    }

    public function messages(){
        return [
            'room_code.required' => 'Bạn chưa chọn phòng học!',
            'room_code.exists' => 'Phòng học này không tồn tại!',
            'subject_code.required' => 'Bạn chưa chọn môn học!',
            'subject_code.exists' => 'Môn học này không tồn tại',
            'course_code.required' => 'Bạn chưa chọn khoá học',
        ];
    }

    protected $stopOnFirstFailure = true;
}
