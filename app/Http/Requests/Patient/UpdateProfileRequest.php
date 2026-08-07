<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $profile = $this->route('profile');
        $isSelf = $profile->is_self;

        $rules = [
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,M,F,O',
            'id_card' => 'nullable|string|max:20|unique:patient_profiles,id_card,' . $profile->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'ethnicity' => 'nullable|string|max:50',
            'insurance_code' => 'nullable|string|min:10|max:15',
            'insurance_place' => 'nullable|string|max:255',
            'insurance_expiry' => 'nullable|date',
            'medical_history' => 'nullable|array',
            'medical_history.*' => 'string|max:255',
            'symptom_notes' => 'nullable|string',
        ];

        if ($isSelf) {
            $rules['email'] = 'nullable|email|max:150|unique:users,email,' . auth()->id();
        } else {
            $rules['relationship'] = 'required|in:parent,spouse,child,other';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh không hợp lệ (phải trước ngày hôm nay).',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'id_card.max' => 'CCCD không được vượt quá 20 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'occupation.max' => 'Nghề nghiệp không được vượt quá 100 ký tự.',
            'ethnicity.max' => 'Dân tộc không được vượt quá 50 ký tự.',
            'insurance_code.min' => 'Mã BHYT phải có từ 10 đến 15 ký tự.',
            'insurance_code.max' => 'Mã BHYT phải có từ 10 đến 15 ký tự.',
            'insurance_place.max' => 'Nơi KCB ban đầu không được vượt quá 255 ký tự.',
            'insurance_expiry.date' => 'Hạn thẻ BHYT không hợp lệ.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
            'relationship.required' => 'Vui lòng chọn mối quan hệ.',
            'relationship.in' => 'Mối quan hệ không hợp lệ.',
            'id_card.unique' => 'Số CMND/CCCD này đã được sử dụng trong hệ thống.',
        ];
    }
}
