<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingStep3Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'draft_id' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'doctor_id' => 'nullable|exists:doctor_profiles,id',
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Vui lòng chọn ngày khám.',
            'date.date' => 'Ngày khám không đúng định dạng.',
            'date.after_or_equal' => 'Ngày khám phải từ hôm nay trở đi.',
            'time.required' => 'Vui lòng chọn giờ khám.',
            'time.date_format' => 'Giờ khám không hợp lệ.',
            'doctor_id.exists' => 'Bác sĩ không hợp lệ.',
            'draft_id.required' => 'Dữ liệu đặt lịch không hợp lệ, vui lòng thử lại.',
        ];
    }
}
