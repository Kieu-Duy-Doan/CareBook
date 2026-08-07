<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorAppointmentStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:pending,checked_in,examining,completed,cancelled,absent,late',
            'reason' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'reason.max' => 'Lý do không được vượt quá 500 ký tự.'
        ];
    }
}
