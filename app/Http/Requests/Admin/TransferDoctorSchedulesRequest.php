<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TransferDoctorSchedulesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'from_doctor_id' => 'required|exists:doctor_profiles,id',
            'to_doctor_id' => 'required|exists:doctor_profiles,id|different:from_doctor_id',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường :attribute không được để trống.',
            'exists' => 'Trường :attribute không hợp lệ.',
            'different' => 'Bác sĩ nguồn và bác sĩ đích phải khác nhau.',
        ];
    }

    public function attributes()
    {
        return [
            'from_doctor_id' => 'bác sĩ nguồn',
            'to_doctor_id' => 'bác sĩ đích',
        ];
    }
}
