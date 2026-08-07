<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Tài khoản
            'full_name'    => 'required|string|max:100',
            'phone'        => ['required', 'string', 'max:15', 'regex:/^(0[35789])[0-9]{8}$/', 'unique:users,phone'],
            'password'     => 'required|string|min:8|confirmed',
            'username'     => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.]*$/', 'unique:users,username'],
            'id_card'      => ['required', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', 'unique:users,id_card'],
            'email'        => 'nullable|email|max:150|unique:users,email',
            // Hồ sơ
            'profile_full_name'      => 'nullable|string|max:100',
            'date_of_birth'          => 'required|date|before:today',
            'gender'                 => 'required|in:male,female,other',
            'profile_phone'          => 'nullable|string|max:15',
            'address'                => 'nullable|string',
            'occupation'             => 'nullable|string|max:100',
            'ethnicity'              => 'nullable|string|max:50',
            'insurance_code'         => 'nullable|string|max:20',
            'insurance_place'        => 'nullable|string|max:255',
            'insurance_expiry'       => 'nullable|date',
            'symptom_notes'          => 'nullable|string',
            'medical_history.*'      => 'nullable|file|mimes:pdf|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'  => 'Vui lòng nhập họ tên.',
            'phone.required'      => 'Vui lòng nhập số điện thoại.',
            'phone.unique'        => 'Số điện thoại đã được sử dụng.',
            'phone.regex'         => 'Số điện thoại không đúng định dạng Việt Nam.',
            'password.required'   => 'Vui lòng nhập mật khẩu.',
            'password.min'        => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed'  => 'Xác nhận mật khẩu không khớp.',
            'username.unique'     => 'Tên đăng nhập đã tồn tại.',
            'username.regex'      => 'Tên đăng nhập không được chứa ký tự đặc biệt.',
            'id_card.required'    => 'Vui lòng nhập số CCCD/CMND.',
            'id_card.regex'       => 'Số CCCD/CMND không đúng định dạng.',
            'id_card.unique'      => 'Số CCCD/CMND đã được sử dụng.',
            'email.unique'        => 'Email đã được sử dụng.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.before'=> 'Ngày sinh không hợp lệ.',
            'gender.required'     => 'Vui lòng chọn giới tính.',
            'medical_history.*.mimes'=> 'File tiền sử bệnh lý phải là định dạng PDF.',
            'medical_history.*.max'  => 'Kích thước file không được vượt quá 10MB.',
        ];
    }
}
