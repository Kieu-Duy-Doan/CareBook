<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreFastTrackRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctor_profiles,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'reason' => 'nullable|string',
            'date' => 'nullable|date',
            'time' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'patient_profile_id.required' => 'Vui lòng chọn hồ sơ bệnh nhân.',
            'patient_profile_id.exists' => 'Hồ sơ bệnh nhân không hợp lệ.',
            'doctor_id.required' => 'Vui lòng chọn bác sĩ.',
            'doctor_id.exists' => 'Bác sĩ không hợp lệ.',
            'specialty_id.exists' => 'Chuyên khoa không hợp lệ.',
            'date.date' => 'Ngày khám không đúng định dạng.',
        ];
    }
}
