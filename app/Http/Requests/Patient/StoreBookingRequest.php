<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_profile_id' => ['required', 'integer', 'exists:patient_profiles,id'],
            'specialty_id'       => ['required', 'integer', 'exists:specialties,id'],
            'doctor_profile_id'  => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'appointment_date'   => ['required', 'date', 'after_or_equal:today'],
            'appointment_time'   => ['required', 'date_format:H:i'],
            'reason'             => ['nullable', 'string', 'max:500'],
            'booking_method'     => ['required', 'in:specialty,doctor'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_profile_id.required' => 'Vui lòng chọn hồ sơ bệnh nhân.',
            'patient_profile_id.exists'   => 'Hồ sơ bệnh nhân không hợp lệ.',
            'specialty_id.required'       => 'Vui lòng chọn chuyên khoa.',
            'appointment_date.required'   => 'Vui lòng chọn ngày khám.',
            'appointment_date.after_or_equal' => 'Ngày khám không thể là ngày trong quá khứ.',
            'appointment_time.required'   => 'Vui lòng chọn giờ khám.',
            'appointment_time.date_format'=> 'Giờ khám không hợp lệ.',
        ];
    }
}