<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'subject_name' => 'required|max:100|unique:subjects,subject_name,' . $this->route('subject_code') . ',subject_code',
            'tuition' => 'required|numeric',
            're_study_fee' => 'required|numeric',
            'credit_number' => 'required|numeric',
            'total_sessions' => 'required|numeric',
            'description' => 'nullable',
            'semester_code' => 'required|exists:categories,cate_code',
            'major_code' => 'required|exists:categories,cate_code',
            'is_active' => 'required|boolean'
        ];
    }

    public function messages(){
        return [
            'subject_name.required' => 'Tên môn học không được để trống!',
            'subject_name.max' => 'Tên môn học không được vượt quá :max ký tự!',
            'subject_name.unique' => 'Tên môn học đã tồn tại!',
            'tuition.required' => 'Học phí không được để trống!',
            'tuition.numeric' => 'Học phí không hợp lệ',
            're_study_fee.required' => 'Phí học lại không được để trống',
            're_study_fee.numeric' => 'Phí học lại không hợp lệ!',
            'credit_number.required' => 'Số tín chỉ không được để trống',
            'credit_number.numeric' => 'Số tín chỉ không hợp lệ!',
            'total_sessions.required' => 'Tổng số buổi học không được để trống',
            'total_sessions.numeric' => 'Tổng số buổi học không hợp lệ!',
            'semester_code.required' => 'Kỳ học không được để trống!',
            'semester_code.exists' => 'Kỳ học không tồn tại!',
            'major_code.required' => 'Chuyên ngành không được để trống!',
            'major_code.exists' => 'Chuyên ngành không tồn tại!',
            'is_active.required' => 'Is active không được để trống!',
            'is_active.boolean' => 'Is active không hợp lệ',
        ];
    }

    protected $stopOnFirstFailure = true;
}
