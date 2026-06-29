<?php

namespace App\Http\Requests\Admin\Validate;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\DoctorProfile;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $doctorId = $this->route('doctor');
        $doctor = $doctorId instanceof DoctorProfile ? $doctorId : DoctorProfile::findOrFail($doctorId);
        $userId = $doctor->user_id;

        return [
            'full_name'       => 'required|string|max:100',
            'phone'           => "required|string|max:15|unique:users,phone,{$userId}",
            'username'        => "required|string|max:50|unique:users,username,{$userId}",
            'email'           => "nullable|email|unique:users,email,{$userId}",
            'academic_title'  => 'nullable|in:BS.,ThS.,TS.,PGS.TS.,GS.TS.,BSCK1.,BSCK2.',
            'level'           => 'required|string|max:100',
            'expertise'       => 'nullable|string',
            'experience_years'=> 'nullable|integer|min:0|max:60',
            'license_number'  => "nullable|string|max:50|unique:doctor_profiles,license_number,{$doctor->id}",
            'bio'             => 'nullable|string',
            'specialty_ids'        => 'required|array|min:1',
            'specialty_ids.*'      => 'exists:specialties,id',
            'primary_specialty_id' => [
                'required',
                'exists:specialties,id',
                function ($attribute, $value, $fail) {
                    $specialtyIds = $this->input('specialty_ids', []);
                    if (! in_array($value, $specialtyIds)) {
                        $fail('Chuyên khoa chính phải nằm trong danh sách chuyên khoa đã chọn.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'      => 'Vui lòng nhập họ tên.',
            'phone.required'          => 'Vui lòng nhập số điện thoại.',
            'phone.unique'            => 'Số điện thoại đã được sử dụng.',
            'username.required'       => 'Vui lòng nhập tên đăng nhập.',
            'username.unique'         => 'Tên đăng nhập đã tồn tại.',
            'level.required'          => 'Vui lòng nhập cấp độ chuyên môn.',
            'specialty_ids.required'  => 'Vui lòng chọn ít nhất một chuyên khoa.',
            'primary_specialty_id.required' => 'Vui lòng chọn chuyên khoa chính.',
        ];
    }
}
