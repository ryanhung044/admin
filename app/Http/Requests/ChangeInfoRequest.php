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
        'full_name_old'             => 'nullable|string|max:255',
        'sex_old'                   => 'nullable|string|in:Nam,Nữ',
        'date_of_birth_old'         => 'nullable|date',
        'address_old'               => 'nullable|string|max:255',
        'citizen_card_number_old'   => 'nullable|string|max:20',

        'full_name'                 => 'nullable|string|max:255',
        'sex'                       => 'nullable|string|in:Nam,Nữ',
        'date_of_birth'             => 'nullable|date',
        'address'                   => 'nullable|string|max:255',
        'citizen_card_number'       => 'nullable|string|max:20',
        'note'                      => 'nullable|string|max:500',
    ];
}

public function messages()
{
    return [
        // Các thông báo lỗi cho các trường *_old
        'full_name_old.string' => 'Họ và tên (cũ) phải là chuỗi ký tự.',
        'full_name_old.max'    => 'Họ và tên (cũ) không được vượt quá 255 ký tự.',
        'sex_old.in'           => 'Giới tính (cũ) phải là "Nam" hoặc "Nữ".',
        'date_of_birth_old.date' => 'Ngày sinh (cũ) phải là ngày hợp lệ.',
        'address_old.string'   => 'Địa chỉ (cũ) phải là chuỗi ký tự.',
        'address_old.max'      => 'Địa chỉ (cũ) không được vượt quá 255 ký tự.',
        'citizen_card_number_old.string' => 'Số CMND/CCCD (cũ) phải là chuỗi ký tự.',
        'citizen_card_number_old.max'    => 'Số CMND/CCCD (cũ) không được vượt quá 20 ký tự.',

        // Các thông báo lỗi cho các trường không có _old
        'full_name.string' => 'Họ và tên phải là chuỗi ký tự.',
        'full_name.max'    => 'Họ và tên không được vượt quá 255 ký tự.',
        'sex.in'           => 'Giới tính phải là "Nam" hoặc "Nữ".',
        'date_of_birth.date' => 'Ngày sinh phải là ngày hợp lệ.',
        'address.string'   => 'Địa chỉ phải là chuỗi ký tự.',
        'address.max'      => 'Địa chỉ không được vượt quá 255 ký tự.',
        'citizen_card_number.string' => 'Số CMND/CCCD phải là chuỗi ký tự.',
        'citizen_card_number.max'    => 'Số CMND/CCCD không được vượt quá 20 ký tự.',
        'note.string'      => 'Ghi chú phải là chuỗi ký tự.',
        'note.max'         => 'Ghi chú không được vượt quá 500 ký tự.',
    ];
}
}
