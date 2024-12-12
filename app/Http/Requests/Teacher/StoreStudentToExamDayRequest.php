<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentToExamDayRequest extends FormRequest
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
            'class_code' => 'required|exists:classrooms,class_code',
            'students' => 'required|array',
            'students.*.user_code' => 'required|exists:users,user_code',
            'students.*.exam_day' => 'nullable|exists:schedules,id'
        ];
    }

    public function messages(){
        return [
            'class_code.required' => 'Bạn chưa cung cấp tên lớp!',
            'class_code.exists' => 'Lớp học này không tồn tại!',
            'students.required' => 'Danh sách sinh viên không được để trống!',
            'students.array' => 'Danh sách sinh viên phải là 1 mảng!',
            'students.*.user_code.required' => 'Mã sinh viên là bắt buộc',
            'students.*.user_code.exists' => 'Có sinh viên không tồn tại!',
            'students.*.exam_day.exists' => 'Ca thi không tồn tại!',
        ];
    }
}
