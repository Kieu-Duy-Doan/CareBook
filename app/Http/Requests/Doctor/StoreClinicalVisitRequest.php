<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicalVisitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'room_id'        => 'required|exists:rooms,id',
            'findings'       => 'nullable|string',
            'payment_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'room_id.required' => 'Vui lòng chọn phòng khám.',
            'room_id.exists' => 'Phòng khám không tồn tại.',
            'payment_amount.numeric' => 'Số tiền phải là số.',
            'payment_amount.min' => 'Số tiền không được nhỏ hơn 0.',
        ];
    }
}
