<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'diagnosis_note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_name' => 'required|string',
            'items.*.quantity' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.instructions' => 'nullable|string',
            'general_note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Vui lòng kê ít nhất một loại thuốc.',
            'items.array' => 'Danh sách thuốc không hợp lệ.',
            'items.min' => 'Vui lòng kê ít nhất một loại thuốc.',
            'items.*.medicine_name.required' => 'Tên thuốc không được để trống.',
            'items.*.quantity.required' => 'Số lượng thuốc không được để trống.',
            'items.*.dosage.required' => 'Liều dùng không được để trống.',
        ];
    }
}
