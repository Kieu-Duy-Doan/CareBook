<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:150',
            'room_number' => 'nullable|string|max:20',
            'building' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:10',
            'room_type' => 'required|in:examination,diagnostic,surgery,other',
            'price' => 'required_if:room_type,diagnostic|nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:1|max:200',
            'is_active' => 'boolean',
            'specialty_ids' => 'nullable|array',
            'specialty_ids.*' => 'exists:specialties,id',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Vui lòng nhập/chọn trường này.',
            'price.required_if' => 'Vui lòng nhập giá tiền dịch vụ cho phòng cận lâm sàng.',
            'max' => 'Vượt quá số ký tự cho phép.',
            'min' => 'Giá trị quá nhỏ.',
            'in' => 'Giá trị không hợp lệ.',
            'exists' => 'Dữ liệu không tồn tại.',
        ];
    }
}
