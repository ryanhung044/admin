<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateNewsletterRequest extends FormRequest
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
            'code' => 'required|regex:/^[^<>{}]*$/|unique:newsletters,code,' . $this->route('newsletter') . ',code',
            'title' => 'required|max:255|regex:/^[^<>{}]*$/',
            // 'tags' => 'regex:/^[^<>{}]*$/',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'description' => 'regex:/^[^<>{}]*$/',
            'type' => 'required|regex:/^[^<>{}]*$/',
            'order' => 'regex:/^[^<>{}]*$/',
            'is_active' => 'regex:/^[^<>{}]*$/',
            // 'notification_object' => 'regex:/^[^<>{}]*$/',
            'user_code' => 'regex:/^[^<>{}]*$/',
            'cate_code' => 'regex:/^[^<>{}]*$/'
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Bạn chưa nhập mã bản tin',
            'code.regex' => 'Mã bản tin không chứa kí tự đặc biệt',
            'code.unique' => 'Mã bản tin đã được sử dụng',
            'title.required' => 'Bạn chưa nhập Title bản tin',
            'title.max' => 'Title bản tin không quá 255 kí tự',
            'title.regex' => 'Title bản tin không chứa kí tự đặc biệt',
            // 'tags.regex' => 'Nhãn bản tin không chứa kí tự đặc biệt',
            'image.image' => 'File phải là ảnh',
            'image.mimes' => 'File ảnh phải có định dạng jpeg, png, jpg, gif, hoặc svg.',
            'description.regex' => 'Mô tả không chứa kí tự đặc biệt',
            'type.required' => 'Bạn chưa chọn kiểu',
            'type.regex' => 'Kiểu bản tin không chứa kí tự đặc biệt',
            'order.regex' => 'Thứ tự bản tin không chứa kí tự đặc biệt',
            'is_active.regex' => 'Trạng thái bản tin không chứa kí tự đặc biệt',
            // 'notification_object.regex' => 'Đối tượng bản tin không chứa kí tự đặc biệt',
            'user_code.regex' => 'Tên tác giả bản tin không chứa kí tự đặc biệt',
            'cate_code.regex' => 'Chuyên mục bản tin không chứa kí tự đặc biệt'
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
