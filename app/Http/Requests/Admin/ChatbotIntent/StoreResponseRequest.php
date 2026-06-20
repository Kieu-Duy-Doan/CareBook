<?php

namespace App\Http\Requests\Admin\ChatbotIntent;

use Illuminate\Foundation\Http\FormRequest;

// Quản lý việc kiểm tra dữ liệu đầu vào khi Thêm mới câu trả lời (Response)
class StoreResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'priority' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ];
    }
}
