<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class HandleStep1 extends FormRequest
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
         $existsCategory = 'required|exists:categories,cate_code';

        return [
            'course_code' => $existsCategory,
            'semester_code' => $existsCategory,
            'major_code' => $existsCategory,
            'subject_code' => 'required|exists:subjects,subject_code',
            // 'study_days' => 'required|array',
            // 'study_days.*' => 'in:1,2,3,4,5,6,7',
            // 'date_from' => 'required|date|after_or_equal:tomorrow',
        ];
    }

    public function messages(){
        return [
            'course_code.required' => 'Bạn chưa chọn khoá học!',
            'course_code.exists' => 'Khoá học này không tồn tại!',
            'semester_code.required' => 'Bạn chưa chọn kỳ học!',
            'semester_code.exists' => 'Kỳ học này không tồn tại!',
            'major_code.required' =>  'Bạn chưa chọn chuyên ngành!',
            'major_code.exists' => 'Chuyên ngành này không tồn tại!',
            'subject_code.required' => 'Bạn chưa chọn môn học!',
            'subject_code.exists' => 'Môn học này không tồn tại!',
            // 'study_days.required' => 'Bạn chưa chọn ngày học trong tuần!',
            // 'study_days.array' => 'Các ngày học không hợp lệ!',
            // 'study_days.*.in' => 'Các ngày học không hợp lệ!',
            // 'date_from.required' => 'Bạn chưa chọn ngày bắt đầu của lớp học!',
            // 'date_from.date' => 'Định dạng ngày bắt đầu không hợp lệ!',
            // 'date_from.after_or_equal' => 'Ngày bắt đầu phải là ngày mai trở đi!'
        ];
    }

    protected $stopOnFirstFailure = true;
}
