<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
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
            // 'id' => 'integer',
            'class_code' => 'required|unique:classrooms,class_code,' . $this->route('classroom') . ',class_code',
            'class_name' => 'required|unique:classrooms,class_name,' . $this->route('classroom') . ',class_code',
            'subject_code' => 'required|exists:subjects,subject_code',
            'section' => 'required|integer',
            'study_days' => 'required|array',
            'study_days.*' => 'in:Mon,Tue,Wed,Thir,Fri,Sat,Sun',
            'room_code' => 'required|exists:categories,cate_code',
            'user_code' => 'nullable|exists:users,user_code',
            'study_schedule' => 'required|array',
            // 'study_schedule.*' => 'date|after_or_equal:tomorrow'
        ];
    }

    public function messages(){
        return [
            'class_code.required' => 'Bạn chưa nhập mã lớp học',
            'class_code.unique' => 'Mã lớp học này đã tồn tại',
            'class_name.required' => 'Bạn chưa nhập tên lớp học',
            'class_name.unique' => 'Tên lớp học này đã tồn tại',
            'subject_code.required' => 'Bạn chưa chọn môn học!',
            'subject_code.exists' => 'Môn học không tồn tại trong hệ thống!',
            'section.required' => 'Bạn chưa chọn ca học!',
            'section.integer' => 'Ca học không hợp lệ',
            // 'study_days.required' => "Bạn chưa chọn các ngày học trong tuần!",
            // 'study_days.array' => "Các ngày học không hợp lệ!",
            // 'study_days.*.in' => 'Các ngày học không hợp lệ!',
            'room_code.required' => 'Bạn chưa chọn phòng học!',
            'room_code.exists' => 'Phòng học không tồn tại trong hệ thống',
            'user_code.exists' => 'Giảng viên này không tồn tại trong hệ thống',
            'study_schedule.required' => 'Bạn chưa chọn các ngày học',
            'study_schedule.array' => 'Danh sách các ngày học phải là 1 mảng',
            'study_schedule.*.date' => 'Danh sách các ngày học không hợp lệ',
            // 'study_schedule.*.after_or_equal' => 'Tất cả các ngày học phải ở tương lai'
        ];
    }

    protected $stopOnFirstFailure = true;

}
