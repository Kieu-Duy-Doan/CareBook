<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingStep1Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_profile_id' => 'required|exists:patient_profiles,id,owner_id,' . auth()->id(),
        ];
    }

    public function messages()
    {
        return [
            'patient_profile_id.required' => 'Vui lòng chọn hồ sơ bệnh nhân.',
            'patient_profile_id.exists' => 'Hồ sơ bệnh nhân không hợp lệ hoặc không thuộc quyền quản lý của bạn.',
        ];
    }
}
