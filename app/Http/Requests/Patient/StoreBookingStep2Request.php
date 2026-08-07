<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingStep2Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'draft_id' => 'required|string',
            'booking_method' => 'required|in:specialty,doctor,suggested',
            'specialty_id' => 'required_if:booking_method,specialty|nullable|exists:specialties,id',
            'level' => 'required_if:booking_method,specialty|nullable|string',
            'doctor_id' => 'required_if:booking_method,doctor,suggested|nullable|exists:doctor_profiles,id',
        ];
    }

    public function messages()
    {
        return [
            'booking_method.required' => 'Vui lòng chọn phương thức đặt lịch.',
            'booking_method.in' => 'Phương thức đặt lịch không hợp lệ.',
            'specialty_id.required_if' => 'Vui lòng chọn chuyên khoa.',
            'specialty_id.exists' => 'Chuyên khoa không hợp lệ.',
            'level.required_if' => 'Vui lòng chọn cấp bậc bác sĩ.',
            'doctor_id.required_if' => 'Vui lòng chọn bác sĩ.',
            'doctor_id.exists' => 'Bác sĩ không hợp lệ.',
            'draft_id.required' => 'Dữ liệu đặt lịch không hợp lệ, vui lòng thử lại.',
        ];
    }
}
