<?php

namespace App\Http\Requests\Admin\Faq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    // Cấp quyền truy cập cho tính năng này
    public function authorize(): bool
    {
        return true;
    }

    // Định nghĩa các quy tắc kiểm tra dữ liệu đầu vào
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'specialty_id' => ['nullable', 'exists:specialties,id'],
            'keywords' => ['nullable', 'string', 'max:500'],
        ];
    }

    // Tùy chỉnh thông báo lỗi bằng tiếng Việt
    public function messages(): array
    {
        return [
            'question.required' => 'Vui lòng nhập câu hỏi FAQ.',
            'question.max' => 'Câu hỏi không được vượt quá 255 ký tự.',
            'answer.required' => 'Vui lòng nhập nội dung câu trả lời.',
            'specialty_id.exists' => 'Chuyên khoa được chọn không tồn tại trong hệ thống.',
            'keywords.max' => 'Từ khóa không được vượt quá 500 ký tự.',
        ];
    }
}
