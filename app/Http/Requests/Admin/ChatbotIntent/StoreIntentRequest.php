<?php

namespace App\Http\Requests\Admin\ChatbotIntent;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ChatbotActionEnum;
use Illuminate\Validation\Rule;

// Quản lý việc kiểm tra dữ liệu đầu vào khi Thêm mới một Kịch bản (Intent)
class StoreIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'intent_name' => 'required|string|max:100|unique:chatbot_intents,intent_name|regex:/^[a-z0-9_]+$/',
            'description' => 'required|string|max:255',
            'example_phrases' => 'nullable|string',
            'action' => ['required', Rule::enum(ChatbotActionEnum::class)],
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('intent_name')) {
            $this->merge([
                'intent_name' => strtolower($this->intent_name),
            ]);
        }
    }
}
