<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicalVisitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'findings'       => 'nullable|string',
            'status'         => 'required|in:waiting,in_progress,completed,refused,redirected',
            'payment_amount' => 'nullable|numeric|min:0',
            'result_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'refusal_reason' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'payment_amount.numeric' => 'Số tiền phải là số.',
            'payment_amount.min' => 'Số tiền không được nhỏ hơn 0.',
            'result_files.*.file' => 'File tải lên không hợp lệ.',
            'result_files.*.mimes' => 'Định dạng file không được hỗ trợ (chỉ nhận pdf, jpg, jpeg, png, doc, docx).',
            'result_files.*.max' => 'Dung lượng mỗi file không vượt quá 10MB.',
            'refusal_reason.max' => 'Lý do từ chối không được vượt quá 500 ký tự.',
        ];
    }
}
