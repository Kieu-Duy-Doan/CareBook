<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $profileId = $this->route('patient');
        $profile = \App\Models\PatientProfile::findOrFail($profileId);

        $rules = [
            'owner_id'               => 'required|exists:users,id',
            'is_self'                => 'required|boolean',
            'full_name'              => 'required|string|max:100',
            'date_of_birth'          => 'required|date|before:today',
            'gender'                 => 'required|in:male,female,other',
            'phone'                  => ['nullable', 'string', 'max:15'],
            'address'                => 'nullable|string',
            'occupation'             => 'nullable|string|max:100',
            'ethnicity'              => 'nullable|string|max:50',
            'insurance_code'         => 'nullable|string|max:20',
            'insurance_place'        => 'nullable|string|max:255',
            'insurance_expiry'       => 'nullable|date',
            'symptom_notes'          => 'nullable|string',
        ];

        if ($profile->card_id_change_count >= 1) {
            $rules['id_card'] = ['nullable', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/'];
        } else {
            $rules['id_card'] = ['required', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'owner_id.required'      => 'Vui lòng chọn tài khoản khách hàng quản lý hồ sơ này.',
            'owner_id.exists'        => 'Khách hàng không tồn tại.',
            'full_name.required'     => 'Vui lòng nhập họ tên hồ sơ.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.before'   => 'Ngày sinh không hợp lệ.',
            'gender.required'        => 'Vui lòng chọn giới tính.',
            'id_card.required'       => 'Vui lòng nhập số CCCD/CMND.',
            'id_card.regex'          => 'Số CCCD/CMND hồ sơ không đúng định dạng.',
        ];
    }
}
