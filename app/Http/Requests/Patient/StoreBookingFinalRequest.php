<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingFinalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'draft_id' => 'required|string',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'draft_id.required' => 'Dữ liệu đặt lịch không hợp lệ, vui lòng thử lại.',
            'reason.max' => 'Lý do khám không được vượt quá 1000 ký tự.',
        ];
    }
}
