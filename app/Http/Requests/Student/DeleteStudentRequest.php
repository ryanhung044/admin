<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class DeleteStudentRequest extends FormRequest
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
            'updated_at' => 'required|date_format:Y-m-d H:i:s'
        ];
    }

    public function messages(){
        return [
            'updated_at.required' => 'Thiếu thời gian cập nhật gần đây của bản ghi này!',
            'updated_at.date_format' => 'Thời gian cập nhật gần đây có định dạng không hợp lệ!'
        ];
    }
}
