<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeInfoRequest extends FormRequest
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
    public function rules()
    {
        return [
            'full_name'             => 'nullable|string|max:255',
            'sex'                   => 'nullable|string|in:Nam,Nữ',
            'date_of_birth'         => 'nullable|date',
            'address'               => 'nullable|string|max:255',
            'citizen_card_number'   => 'nullable|string|max:20',
            'note'                  => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'sex.in' => 'Giới tính phải là "Nam" hoặc "Nữ".',
            'date_of_birth.date' => 'Ngày sinh phải là ngày hợp lệ.',
            'citizen_card_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự.',
        ];
    }
}
