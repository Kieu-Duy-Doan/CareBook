<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'doctor_profile_id'  => 'required|exists:doctor_profiles,id',
            'room_id'            => 'required|exists:rooms,id',
            'appointment_date'   => 'required|date|after_or_equal:today',
            'appointment_time'   => 'required',
            'status'             => 'required|in:pending,checked_in,examining,completed,cancelled,absent,late',
            'source'             => 'required|in:web,counter,chatbot',
            'reason'             => 'required|string',
            'receptionist_note'  => 'nullable|string',
            'vital_pulse'        => 'nullable|integer|min:0',
            'vital_systolic_bp'  => 'nullable|integer|min:0',
            'vital_diastolic_bp' => 'nullable|integer|min:0',
            'vital_temperature'  => 'nullable|numeric|min:0',
            'vital_respiratory'  => 'nullable|integer|min:0',
            'vital_spo2'         => 'nullable|numeric|min:0',
            'vital_weight_kg'    => 'nullable|numeric|min:0',
            'vital_height_cm'    => 'nullable|numeric|min:0',
            'vital_bmi'          => 'nullable|numeric|min:0',
            'vital_note'         => 'nullable|string',
            'measured_by'        => 'nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường :attribute không được để trống.',
            'exists' => 'Trường :attribute được chọn không hợp lệ hoặc đã bị vô hiệu hóa.',
            'date' => 'Trường :attribute phải là định dạng ngày hợp lệ.',
            'after_or_equal' => 'Trường :attribute phải là ngày hôm nay hoặc sau đó.',
            'in' => 'Trường :attribute chọn giá trị không hợp lệ.',
            'integer' => 'Trường :attribute phải là số nguyên.',
            'numeric' => 'Trường :attribute phải là số.',
            'min' => 'Trường :attribute không được nhỏ hơn :min.',
            'string' => 'Trường :attribute phải là chuỗi ký tự.',
        ];
    }

    public function attributes()
    {
        return [
            'patient_profile_id' => 'bệnh nhân',
            'specialty_id' => 'chuyên khoa',
            'doctor_profile_id' => 'bác sĩ',
            'room_id' => 'phòng khám',
            'appointment_date' => 'ngày khám',
            'appointment_time' => 'giờ khám',
            'status' => 'trạng thái',
            'source' => 'nguồn đặt',
            'reason' => 'lý do khám',
            'receptionist_note' => 'ghi chú',
            'vital_pulse' => 'mạch',
            'vital_systolic_bp' => 'huyết áp tâm thu',
            'vital_diastolic_bp' => 'huyết áp tâm trương',
            'vital_temperature' => 'nhiệt độ',
            'vital_respiratory' => 'nhịp thở',
            'vital_spo2' => 'SpO2',
            'vital_weight_kg' => 'cân nặng',
            'vital_height_cm' => 'chiều cao',
            'vital_bmi' => 'chỉ số BMI',
            'vital_note' => 'ghi chú sinh hiệu',
            'measured_by' => 'người đo',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exists = \App\Models\Appointment::where('patient_profile_id', $this->input('patient_profile_id'))
                ->where('doctor_profile_id', $this->input('doctor_profile_id'))
                ->whereDate('appointment_date', $this->input('appointment_date'))
                ->whereTime('appointment_time', $this->input('appointment_time'))
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($exists && $this->input('status') !== 'cancelled') {
                $validator->errors()->add('appointment_time', 'Bệnh nhân này đã có lịch hẹn (chưa huỷ) với bác sĩ vào ngày và khung giờ này. Vui lòng chọn khung giờ khác.');
            }
        });
    }
}
