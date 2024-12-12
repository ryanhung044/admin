<?php

namespace App\Http\Requests\Major;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMajorRequest extends FormRequest
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
            'cate_code' => 'required|unique:categories,cate_code',
            'cate_name' => 'required|max:255|regex:/^[^<>{}]*$/',
            'value' => 'nullable|regex:/^[^<>{}]*$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'description' => 'nullable|regex:/^[^<>{}]*$/',
            'parent_code' => 'nullable|regex:/^[^<>{}]*$/',
            'is_active' => 'regex:/^[^<>{}]*$/'
        ];
    }

    public function messages()
    {
        return [
            'cate_code.required' => 'Bạn chưa nhập mã chuyên ngành',
            'cate_code.unique' => 'Mã chuyên ngành đã được sử dụng',
            'cate_name.required' => 'Bạn chưa nhập tên chuyên ngành',
            'cate_name.max' => 'Tên chuyên ngành không quá 255 kí tự',
            'cate_name.regex' => 'Tên chuyên ngành không chứa kí tự đặc biệt',
            // 'value.regex' => 'Giá trị không chứa kí tự đặc biệt',
            'image.image' => 'File phải là ảnh',
            'image.mimes' => 'File ảnh phải có định dạng jpeg, png, jpg, gif, hoặc svg.',
            'description.regex' => 'Mô tả không chứa kí tự đặc biệt',
            'parent_code.regex' => 'Không chứa kí tự đặc biệt',
            'is_active.regex' => 'Trạng thái không chứa kí tự đặc biệt'
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
