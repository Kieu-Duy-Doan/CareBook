<?php

namespace App\Http\Requests\Admin\Validate;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UpdateReceptionistRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $receptionistId = $this->route('id');
        $receptionist = $receptionistId instanceof User ? $receptionistId : User::with('staffProfile')->findOrFail($receptionistId);
        $id = $receptionist->id;
        $staffProfileId = $receptionist->staffProfile?->id;

        return [
            'full_name'      => 'required|string|max:100',
            'phone'          => ["required", "string", "max:15", "regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/", "unique:users,phone,$id"],
            'username'       => ["required", "string", "max:50", "regex:/^[a-zA-Z0-9_\.]+$/", "unique:users,username,$id"],
            'id_card'        => "nullable|string|max:20|unique:users,id_card,$id",
            'email'          => "nullable|email|max:150|unique:users,email,$id",
            'department'     => ['required', 'string', 'in:Tiếp nhận bệnh nhân,Chăm sóc khách hàng'],
            'internal_phone' => 'nullable|string|max:15',
            'start_date'     => 'nullable|date|before_or_equal:today',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'     => 'Vui lòng nhập họ tên.',
            'phone.required'         => 'Vui lòng nhập số điện thoại.',
            'phone.regex'            => 'Số điện thoại không đúng định dạng (VD: 0901234567 hoặc +84901234567).',
            'phone.unique'           => 'Số điện thoại đã được sử dụng.',
            'username.required'      => 'Vui lòng nhập tên đăng nhập.',
            'username.regex'         => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu gạch dưới và dấu chấm.',
            'username.unique'        => 'Tên đăng nhập đã tồn tại.',
            'id_card.unique'         => 'Số CCCD đã được sử dụng.',
            'email.unique'           => 'Email đã được sử dụng.',
            'department.required'    => 'Vui lòng chọn phòng ban.',
            'department.in'          => 'Phòng ban không hợp lệ.',
            'start_date.before_or_equal' => 'Ngày vào làm không được là ngày trong tương lai.',
        ];
    }
}
