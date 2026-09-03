<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $doctorId = $this->route('id');
        $doctor = \App\Models\DoctorProfile::findOrFail($doctorId);
        $userId = $doctor->user_id;

        return [
            'full_name'       => 'required|string|max:100',
            'phone'           => "required|string|max:15|unique:users,phone,{$userId}",
            'username'        => "required|string|max:50|unique:users,username,{$userId}",
            'email'           => "nullable|email|unique:users,email,{$userId}",
            'id_card'         => ['nullable', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', "unique:users,id_card,{$userId}"],
            'doctor_code'     => "required|string|max:20|unique:doctor_profiles,doctor_code,{$doctorId}",
            'academic_rank'   => 'required|in:none,PGS,GS',
            'degree'          => 'required|in:BS,ThS,TS,BSCK1,BSCK2,BSNT',
            'current_position'=> 'required|in:INTERN,ATTENDING,CONSULTANT,DEPARTMENT_HEAD,EXPERT',
            'doctor_type'     => 'required|in:clinical,paraclinical',
            'expertise'       => 'nullable|string',
            'experience_years'=> 'nullable|integer|min:0|max:60',
            'license_number'  => "nullable|string|max:50|unique:doctor_profiles,license_number,{$doctorId}",
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
            'id_card.regex'           => 'Số CCCD/CMND không đúng định dạng (9 hoặc 12 số).',
            'id_card.unique'          => 'Số CCCD/CMND đã được sử dụng.',
            'doctor_code.required'    => 'Vui lòng nhập mã bác sĩ.',
            'doctor_code.unique'      => 'Mã bác sĩ đã tồn tại.',
            'level.required'          => 'Vui lòng chọn cấp độ chuyên môn.',
            'specialty_ids.required'  => 'Vui lòng chọn ít nhất một chuyên khoa.',
            'primary_specialty_id.required' => 'Vui lòng chọn chuyên khoa chính.',
        ];
    }
}
