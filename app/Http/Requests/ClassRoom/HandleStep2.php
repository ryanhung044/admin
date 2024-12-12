<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class HandleStep2 extends FormRequest
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
            'subject_code' => 'required|exists:subjects,subject_code',
            'major_code' => 'required|exists:categories,cate_code',
            'session_code' => 'required|exists:categories,cate_code',
            // 'date_from' => 'required|date|after_or_equal:tomorrow',
            // 'study_days' => 'required|array',
            // 'study_days.*' => 'in:1,2,3,4,5,6,7',
            'list_study_dates' => 'required|array',
            'list_study_dates.*' => 'date|after_or_equal:tomorrow',
            'room_code' => 'required|exists:categories,cate_code',
            'teacher_code' => 'nullable|exists:users,user_code',
        ];
    }

    public function messages(){
        return [
            // 'list_study_dates.required' => 'Danh sách lịch học không được để trống!',
            // 'list_study_dates.array' => 'Danh sách lịch học phải là 1 mảng!',
            // 'list_study_dates.*.date' => 'Lịch học không đúng định dạng (Y-m-d)!',
            // 'list_study_dates.*.after_or_equal' => 'Các ngày học phải là tương lai!',
            // 'session_code.required' => 'Bạn chưa chọn ca học!',
            // 'session_code.exists' => "Ca học này không tồn tại!",
            // 'subject_code.required' => "Bạn chưa chọn môn học!",
            // 'subject_code.exists' => "Môn học này không tồn tại!"
        ];
    }

    protected $stopOnFirstFailure = true;
}
