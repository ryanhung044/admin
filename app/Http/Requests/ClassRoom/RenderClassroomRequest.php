<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class RenderClassroomRequest extends FormRequest
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
            'class_code' => 'required|unique:classrooms,class_code',
            'class_name' => 'required|unique:classrooms,class_name',
            'subject_code' => 'required|exists:subjects,subject_code',
            'section' => 'required|integer',
            'study_days' => 'required|array',
            'study_days.*' => 'in:Mon,Tue,Wed,Thir,Fri,Sat,Sun',
            'date_from' => 'required|date|after_or_equal:tomorrow',
            'room_code' => 'required|exists:categories,cate_code',
            'user_code' => 'nullable|exists:users,user_code',
        ];
    }

    public function messages(){
        return [
            'class_code.required' => 'Bạn chưa nhập mã lớp học!',
            'class_code.unique' => 'Mã lớp học này đã tồn tại!',
            'class_name.required' => 'Bạn chưa nhập tên lớp học!',
            'class_name.unique' => 'Tên lớp học này đã tồn tại!',
            'subject_code.required' => 'Bạn chưa chọn môn học!',
            'subject_code.exists' => 'Môn học không tồn tại trong hệ thống!',
            'section.unique' => 'Bạn chưa chọn ca học!',
            'section.integer' => 'Ca học không hợp lệ',
            'study_days.required' => "Bạn chưa chọn các ngày học trong tuần!",
            'study_days.array' => "Các ngày học không hợp lệ!",
            'study_days.*.in' => 'Các ngày học không hợp lệ!',
            'date_from.required' => 'Bạn chưa nhập ngày bắt đầu!',
            'date_from.date' => 'Ngày bắt đầu không hợp lệ!',
            'date_from.after_or_equal' => 'Ngày bắt đầu phải ở tương lai!',
            'room_code.required' => 'Bạn chưa chọn phòng học!',
            'room_code.exists' => 'Phòng học không tồn tại trong hệ thống',
            'user_code.exists' => 'Giảng viên này không tồn tại trong hệ thống'
        ];
    }

    protected $stopOnFirstFailure = true;
}
