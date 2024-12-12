<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest  extends FormRequest
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
           

            'course_code' => 'required|exists:categories,cate_code',
                'subject_code' => 'required|exists:subjects,subject_code',
                'session_code' => 'required|exists:categories,cate_code',
                'room_code' => 'required|exists:categories,cate_code',
                'teacher_code' => 'nullable|exists:users,user_code',
                'list_study_dates' => 'required|array',
                'list_study_dates.*' => 'date|after_or_equal:tomorrow|date_format:Y-m-d',
                'student_codes' => 'required|array'
            
        ];
    }

    public function messages(){
        return [
            'course_code.required' => 'Bạn chưa chọn khoá học!',
            'course_code.exists' => 'Khoá học không tồn tại trong hệ thống!',
            'class_code.required' => 'Bạn chưa nhập mã lớp học!',
            'class_code.unique' => 'Mã lớp học này đã tồn tại!',
            'class_name.required' => 'Bạn chưa nhập tên lớp học!',
            'class_name.unique' => 'Tên lớp học này đã tồn tại!',
            'subject_code.required' => 'Bạn chưa chọn môn học!',
            'subject_code.exists' => 'Môn học không tồn tại trong hệ thống!',
            'session_code.required' => 'Bạn chưa chọn ca học!',
            'session_code.exists' => 'Ca học không tồn tại!',
            'room_code.required' => 'Bạn chưa chọn phòng học!',
            'room_code.exists' => 'Phòng học không tồn tại trong hệ thống!',
            'teacher_code.exists' => 'Giảng viên này không tồn tại trong hệ thống!',
            'list_study_dates.required' => 'Bạn chưa chọn các ngày học!',
            'list_study_dates.array' => 'Danh sách các ngày học phải là 1 mảng!',
            'list_study_dates.*.date' => 'Danh sách các ngày học không hợp lệ!',
            'list_study_dates.*.after_or_equal' => 'Tất cả các ngày học phải ở tương lai!',
            'list_study_dates.*.date_format' => 'Các ngày học có định dạng không hợp lệ!',
            'student_codes.required' => 'Không thể tạo lớp nếu không có sinh viên!',
            'student_codes.array' => 'Danh sách sinh viên phải  là 1 mảng!'
        ];
    }

    protected $stopOnFirstFailure = true;
}
