<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_profile_id' => [
                'required', 
                'integer', 
                \Illuminate\Validation\Rule::exists('patient_profiles', 'id')->where(function ($query) {
                    $query->where('owner_id', auth()->id());
                })
            ],
            'specialty_id'       => ['required', 'integer', 'exists:specialties,id'],
            'doctor_profile_id'  => ['required', 'integer', 'exists:doctor_profiles,id'],
            'appointment_date'   => ['required', 'date', 'after_or_equal:today'],
            'appointment_time'   => ['required', 'date_format:H:i'],
            'reason'             => ['nullable', 'string', 'max:500'],
            'booking_method'     => ['required', 'in:specialty,doctor'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_profile_id.required' => 'Vui lòng chọn hồ sơ bệnh nhân.',
            'patient_profile_id.exists'   => 'Hồ sơ bệnh nhân không hợp lệ.',
            'specialty_id.required'       => 'Vui lòng chọn chuyên khoa.',
            'appointment_date.required'   => 'Vui lòng chọn ngày khám.',
            'appointment_date.after_or_equal' => 'Ngày khám không thể là ngày trong quá khứ.',
            'appointment_time.required'   => 'Vui lòng chọn giờ khám.',
            'appointment_time.date_format'=> 'Giờ khám không hợp lệ.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $appointmentDate = $this->input('appointment_date');
            $appointmentTime = $this->input('appointment_time');
            $patientProfileId = $this->input('patient_profile_id');
            
            if ($appointmentDate && $appointmentTime && $patientProfileId) {
                // Lấy tất cả các lịch khám của hồ sơ này trong ngày (bao gồm cả trạng thái cancelled)
                $profileAppointments = \App\Models\Appointment::where('patient_profile_id', $patientProfileId)
                    ->whereDate('appointment_date', $appointmentDate)
                    ->get();

                // Yêu cầu 1: Mỗi hồ sơ được đặt tối đa 2 lần/ngày (tính cả lịch đã huỷ)
                if ($profileAppointments->count() >= 2) {
                    $validator->errors()->add('patient_profile_id', 'Hồ sơ bệnh nhân này đã đạt giới hạn đặt 2 lịch trong ngày ' . \Carbon\Carbon::parse($appointmentDate)->format('d/m/Y') . ' (bao gồm cả lịch đã huỷ). Vui lòng chọn hồ sơ khác.');
                    return;
                }

                // Yêu cầu 2: Thời gian đặt lịch phải cách nhau ít nhất 2 tiếng (120 phút)
                $newTime = \Carbon\Carbon::parse($appointmentTime);
                foreach ($profileAppointments as $appointment) {
                    $existingTime = \Carbon\Carbon::parse($appointment->appointment_time);
                    
                    // Tính khoảng cách tuyệt đối bằng phút giữa 2 khung giờ
                    $diffInMinutes = abs($newTime->diffInMinutes($existingTime, false));
                    
                    if ($diffInMinutes < 120) {
                        $validator->errors()->add('appointment_time', 'Giờ khám phải cách các lịch khám đã đặt trong ngày ít nhất 2 tiếng để tránh spam.');
                        break;
                    }
                }
            }
        });
    }
}