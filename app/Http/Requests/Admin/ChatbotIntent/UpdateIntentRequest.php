<?php

namespace App\Http\Requests\Admin\ChatbotIntent;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ChatbotActionEnum;
use Illuminate\Validation\Rule;

// Quản lý việc kiểm tra dữ liệu đầu vào khi Cập nhật một Kịch bản có sẵn
class UpdateIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'intent_name' => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:chatbot_intents,intent_name,' . $id,
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
