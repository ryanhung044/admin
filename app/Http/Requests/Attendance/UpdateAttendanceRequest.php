<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAttendanceRequest extends FormRequest
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
            '*.student_code' => 'required',
            '*.class_code' => 'nullable',
            '*.status' => 'required',
            '*.noted' => 'nullable|regex:/^[^<>{}]*$/',
            '*.date' => 'nullable',
        ];
    }

    public function messages(){
        return [
            'student_code.required' => 'Mã học sinh không được bỏ trống',
            // 'class_code.required' => 'Mã lớp không được bỏ trống',
            'note.regex' => 'Ghi chú không chứa ký tự <> {}'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'errors' => $errors->messages()
        ], 400);

        throw new HttpResponseException($response);
    }
}
