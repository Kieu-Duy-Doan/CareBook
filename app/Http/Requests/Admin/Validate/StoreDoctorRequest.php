<?php

namespace App\Http\Requests\Admin\Validate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreDoctorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Tài khoản
            'full_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:15', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/', 'unique:users,phone'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\.]+$/', 'unique:users,username'],
            'email' => 'nullable|email|max:150|unique:users,email',
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols(), 'confirmed'],
            // Hồ sơ chuyên môn
            'academic_title' => 'nullable|in:BS.,ThS.,TS.,PGS.TS.,GS.TS.,BSCK1.,BSCK2.',
            'level' => 'required|string|max:100',
            'expertise' => 'nullable|string|max:2000',
            'experience_years' => 'nullable|integer|min:0|max:60',
            'license_number' => 'required|string|max:50|unique:doctor_profiles,license_number',
            'bio' => 'nullable|string|max:2000',
            // Chuyên khoa
            'specialty_ids' => 'required|array|min:1',
            'specialty_ids.*' => 'exists:specialties,id,is_active,1',
            'primary_specialty_id' => [
                'required',
                'exists:specialties,id,is_active,1',
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
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (VD: 0901234567 hoặc +84901234567).',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.regex' => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu gạch dưới và dấu chấm.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự và phải bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'level.required' => 'Vui lòng nhập cấp độ chuyên môn.',
            'specialty_ids.required' => 'Vui lòng chọn ít nhất một chuyên khoa.',
            'specialty_ids.*.exists' => 'Chuyên khoa đã chọn không tồn tại hoặc đã bị vô hiệu hoá.',
            'primary_specialty_id.required' => 'Vui lòng chọn chuyên khoa chính.',
            'primary_specialty_id.exists' => 'Chuyên khoa chính không hợp lệ.',
            'expertise.max' => 'Lĩnh vực chuyên trị tối đa 2000 ký tự.',
            'bio.max' => 'Giới thiệu bản thân tối đa 2000 ký tự.',
            'license_number.required' => 'Vui lòng nhập số chứng chỉ hành nghề.',
        ];
    }
}
