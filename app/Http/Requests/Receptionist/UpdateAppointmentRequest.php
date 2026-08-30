<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Appointment;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $appointmentId = $this->route('appointment') ?? $this->route('id');
        $appointment = $appointmentId instanceof Appointment ? $appointmentId : Appointment::findOrFail($appointmentId);
        $isLocked = in_array($appointment->status, ['examining', 'completed']);

        if ($isLocked) {
            return [
                'status' => 'required|in:pending,checked_in,examining,completed,cancelled,absent,late',
                'receptionist_note' => 'nullable|string',
            ];
        }

        return [
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'doctor_profile_id'  => 'required|exists:doctor_profiles,id',
            'room_id'            => 'required|exists:rooms,id,is_active,1',
            'appointment_date'   => 'required|date|after_or_equal:today',
            'appointment_time'   => 'required',
            'status'             => 'required|in:pending,checked_in,examining,completed,cancelled,absent,late',
            'source'             => 'required|in:web,counter,chatbot',
            'reason'             => 'required|string',
            'receptionist_note'  => 'nullable|string',

            // Vitals
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $appointmentId = $this->route('appointment') ?? $this->route('id');
            $appointment = $appointmentId instanceof Appointment ? $appointmentId : Appointment::findOrFail($appointmentId);
            $oldStatus = $appointment->status;
            $newStatus = $this->status;

            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                $validator->errors()->add('status', 'Không thể thay đổi trạng thái của lịch hẹn đã hoàn thành.');
            }

            if ($oldStatus === 'examining' && $newStatus !== 'examining' && $newStatus !== 'completed') {
                $validator->errors()->add('status', 'Lịch hẹn đang khám chỉ có thể chuyển sang trạng thái Hoàn thành.');
            }

            $isLocked = in_array($oldStatus, ['examining', 'completed']);
            if ($isLocked) return; // Nếu đã khóa thì bỏ qua kiểm tra ngày giờ dưới đây

            // Xác thực chuyên khoa
            if ($this->doctor_profile_id && $this->specialty_id) {
                $doctorBelongsToSpecialty = DB::table('doctor_specialties')
                    ->where('doctor_profile_id', $this->doctor_profile_id)
                    ->where('specialty_id', $this->specialty_id)
                    ->exists();

                if (!$doctorBelongsToSpecialty) {
                    $validator->errors()->add('doctor_profile_id', 'Bác sĩ được chọn không thuộc chuyên khoa đã chỉ định.');
                }
            }

            // Kiểm tra lịch làm việc của bác sĩ
            if ($this->appointment_date && $this->appointment_time) {
                $dayOfWeek = \Carbon\Carbon::parse($this->appointment_date)->dayOfWeek + 1;
                $reqTime = $this->appointment_time;

                $override = \App\Models\ScheduleOverride::where('doctor_profile_id', $this->doctor_profile_id)
                    ->whereDate('override_date', $this->appointment_date)
                    ->first();

                if ($override) {
                    if ($override->type === 'close') {
                        $validator->errors()->add('appointment_time', 'Bác sĩ đã đăng ký nghỉ / đóng ca vào ngày này.');
                    } elseif ($override->type === 'extra') {
                        $isValidTime = $override->start_time <= $reqTime && $override->end_time >= $reqTime;
                        if (!$isValidTime) {
                            $validator->errors()->add('appointment_time', 'Khung giờ này không nằm trong ca làm việc bổ sung của bác sĩ.');
                        }
                    }
                } else {
                    $hasSchedule = \App\Models\WorkSchedule::where('doctor_profile_id', $this->doctor_profile_id)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_active', true)
                        ->where('start_time', '<=', $reqTime)
                        ->where('end_time', '>=', $reqTime)
                        ->exists();

                    if (!$hasSchedule) {
                        $validator->errors()->add('appointment_time', 'Bác sĩ không có lịch làm việc đăng ký vào ngày và khung giờ này.');
                    }
                }
            }

            // Chặn dời lịch về quá khứ trong ngày hôm nay
            if ($this->appointment_date === now()->toDateString() && $this->appointment_time) {
                $currentTime = now()->format('H:i');
                $reqTimeShort = substr($this->appointment_time, 0, 5);
                if ($reqTimeShort < $currentTime) {
                    $validator->errors()->add('appointment_time', 'Không thể đặt hoặc dời lịch khám về thời gian trong quá khứ của ngày hôm nay.');
                }
            }

            // Kiểm tra trùng lịch hẹn
            if ($this->status !== 'cancelled' && $this->appointment_date && $this->appointment_time) {
                // Bác sĩ trùng lịch
                $doctorConflict = Appointment::where('doctor_profile_id', $this->doctor_profile_id)
                    ->whereDate('appointment_date', $this->appointment_date)
                    ->whereTime('appointment_time', $this->appointment_time)
                    ->where('id', '!=', $appointment->id)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($doctorConflict) {
                    $validator->errors()->add('appointment_time', 'Bác sĩ này đã có lịch hẹn khác vào khung giờ này. Vui lòng chọn giờ khác.');
                }

                // Bệnh nhân trùng lịch
                $patientConflict = Appointment::where('patient_profile_id', $this->patient_profile_id)
                    ->whereDate('appointment_date', $this->appointment_date)
                    ->whereTime('appointment_time', $this->appointment_time)
                    ->where('id', '!=', $appointment->id)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($patientConflict) {
                    $validator->errors()->add('appointment_time', 'Bệnh nhân này đã có lịch hẹn khác vào cùng khung giờ này.');
                }
            }
        });
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
            'receptionist_note' => 'ghi chú lễ tân',
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
}
