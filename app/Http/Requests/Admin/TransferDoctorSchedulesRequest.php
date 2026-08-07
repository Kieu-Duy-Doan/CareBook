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
            'transfer_type' => 'required|in:all,date_range',
            'start_date' => 'required_if:transfer_type,date_range|date|nullable',
            'end_date' => 'required_if:transfer_type,date_range|date|after_or_equal:start_date|nullable',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường :attribute không được để trống.',
            'exists' => 'Trường :attribute không hợp lệ.',
            'different' => 'Bác sĩ nguồn và bác sĩ đích phải khác nhau.',
            'in' => 'Trường :attribute không hợp lệ.',
            'required_if' => 'Vui lòng chọn ngày bắt đầu/kết thúc khi chuyển theo khoảng thời gian.',
            'date' => 'Trường :attribute không đúng định dạng ngày.',
            'after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ];
    }

    public function attributes()
    {
        return [
            'from_doctor_id' => 'bác sĩ nguồn',
            'to_doctor_id' => 'bác sĩ đích',
            'transfer_type' => 'loại chuyển đổi',
            'start_date' => 'ngày bắt đầu',
            'end_date' => 'ngày kết thúc',
        ];
    }
}
