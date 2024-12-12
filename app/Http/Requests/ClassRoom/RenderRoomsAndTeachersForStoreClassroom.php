<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class RenderRoomsAndTeachersForStoreClassroom extends FormRequest
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
            'session_code' => 'required|exists:categories,cate_code',
            'major_code' => 'required|exists:categories,cate_code',
            'list_study_dates' => 'required|array',
            'list_study_dates.*' => 'date|after_or_equal:tomorrow',
        ];
    }

    public function messages(){
        return [
            'session_code.required' => 'Bạn chưa chọn ca học!',
            'session_code.exists' => "Ca học này không tồn tại!",
            'major_code.required' =>  'Bạn chưa chọn chuyên ngành!',
            'major_code.exists' => 'Chuyên ngành này không tồn tại!',
            'list_study_dates.required' => 'Danh sách lịch học không được để trống!',
            'list_study_dates.array' => 'Danh sách lịch học phải là 1 mảng!',
            'list_study_dates.*.date' => 'Lịch học không đúng định dạng (Y-m-d)!',
        ];
    }

    
    protected $stopOnFirstFailure = true;
}
