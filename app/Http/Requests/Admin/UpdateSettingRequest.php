<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    // Cho phép người dùng (đã qua lớp bảo vệ) được phép thực hiện thao tác này
    public function authorize(): bool
    {
        return true;
    }

    // Quy định điều kiện bắt buộc của dữ liệu tải lên
    public function rules(): array
    {
        return [
            // Cấu hình ảnh logo: chỉ nhận hình, các đuôi ảnh cơ bản và không quá 2MB
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'settings' => 'nullable|array',
            'settings_types' => 'nullable|array',
        ];
    }
    
    // Đổi lời báo lỗi thành tiếng Việt cho dễ hiểu
    public function messages(): array
    {
        return [
            'logo.image' => 'Tệp tải lên cho Logo phải là một hình ảnh.',
            'logo.mimes' => 'Định dạng logo không hợp lệ. Vui lòng dùng: jpeg, png, jpg, svg, webp.',
            'logo.max' => 'Dung lượng logo quá lớn, tối đa không được vượt quá 2MB.',
        ];
    }
}
