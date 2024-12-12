<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateTransferScheduleTimeframe extends FormRequest
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
            'start_time' => 'bail|required|date_format:Y-m-d\TH:i|after_or_equal:today',
            'end_time' => 'bail|required|date_format:Y-m-d\TH:i|after_or_equal:today',
        ];
    }

    public function messages(){
        return [
            'start_time.required' => 'Bạn chưa nhập thời gian bắt đầu!', 
            'start_time.date_format' => 'Thời gian bắt đầu có định dạng không hợp lệ!',
            'start_time.after_or_equal' => 'Thời gian bắt đầu không hợp lệ!',
            'end_time.required' => 'Bạn chưa nhập thời gian kết thúc!',
            'end_time.date_format' => 'Thời gian kết thúc có định dạng không hợp lệ!',
            'end_time.after_or_equal' => 'Thời gian kết thúc không hợp lệ!',
        ];
    }

    protected $stopOnFirstFailure = true;
}
