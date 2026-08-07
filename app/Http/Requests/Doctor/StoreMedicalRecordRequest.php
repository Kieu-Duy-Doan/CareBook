<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'diagnosis' => 'required|string',
            'icd10_code' => 'nullable|string|max:20',
            'conclusion' => 'nullable|string',
            'advice' => 'nullable|string',
            'followup_date' => 'nullable|date',
            'treatment_result' => 'required|in:outpatient,admitted,monitoring',
            'result_files.*' => 'nullable|file|mimes:pdf|max:10240',
            'assistant_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'diagnosis.required' => 'Vui lòng nhập chẩn đoán.',
            'icd10_code.max' => 'Mã ICD-10 không được vượt quá 20 ký tự.',
            'followup_date.date' => 'Ngày tái khám không hợp lệ.',
            'treatment_result.required' => 'Vui lòng chọn hướng điều trị.',
            'treatment_result.in' => 'Hướng điều trị không hợp lệ.',
            'result_files.*.file' => 'File tải lên không hợp lệ.',
            'result_files.*.mimes' => 'File kết quả phải là định dạng PDF.',
            'result_files.*.max' => 'Dung lượng mỗi file PDF không được vượt quá 10MB.',
            'assistant_id.exists' => 'Người hỗ trợ không tồn tại.',
        ];
    }
}
