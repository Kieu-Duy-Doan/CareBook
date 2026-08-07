<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\WorkSchedule;

class UpdateWorkScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_profile_id' => 'required|exists:doctor_profiles,id',
            'room_id' => 'required|exists:rooms,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration_minutes' => 'required|integer|min:5|max:120',
            'max_slots' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường :attribute không được để trống.',
            'exists' => 'Trường :attribute không hợp lệ.',
            'integer' => 'Trường :attribute phải là số nguyên.',
            'between' => 'Trường :attribute phải từ :min đến :max.',
            'date_format' => 'Trường :attribute không đúng định dạng thời gian.',
            'after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'min' => 'Trường :attribute tối thiểu là :min.',
            'max' => 'Trường :attribute tối đa là :max.',
            'boolean' => 'Trường :attribute phải là đúng hoặc sai.',
        ];
    }

    public function attributes()
    {
        return [
            'doctor_profile_id' => 'bác sĩ',
            'room_id' => 'phòng khám',
            'day_of_week' => 'thứ trong tuần',
            'start_time' => 'thời gian bắt đầu',
            'end_time' => 'thời gian kết thúc',
            'slot_duration_minutes' => 'thời lượng một lượt khám',
            'max_slots' => 'số lượt khám tối đa',
            'is_active' => 'trạng thái hoạt động',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isValidTime = ($this->input('start_time') === '07:00' && $this->input('end_time') === '11:00') ||
                ($this->input('start_time') === '13:00' && $this->input('end_time') === '17:00');

            if (!$isValidTime) {
                $validator->errors()->add('start_time', 'Thời gian ca trực chỉ được phép là Sáng (07:00 - 11:00) hoặc Chiều (13:00 - 17:00).');
            } else {
                $id = $this->route('id');

                // Kiểm tra bác sĩ được chọn đã có ca làm việc trùng thời gian vào thứ đã chọn chưa
                $existsTime = WorkSchedule::where('doctor_profile_id', $this->input('doctor_profile_id'))
                    ->where('day_of_week', $this->input('day_of_week'))
                    ->where('is_active', true)
                    ->where('id', '!=', $id)
                    ->where(function ($query) {
                        $query->where('start_time', '<', $this->input('end_time'))
                            ->where('end_time', '>', $this->input('start_time'));
                    })
                    ->exists();

                if ($existsTime) {
                    $validator->errors()->add('doctor_profile_id', 'Bác sĩ đã có lịch làm việc trùng thời gian.');
                }

                // Kiểm tra lịch đăng ký có trùng với lịch hay phòng bác sĩ khác không
                $existsTimeAndRoomWithOthers = WorkSchedule::where('day_of_week', $this->input('day_of_week'))
                    ->where('is_active', true)
                    ->where('room_id', $this->input('room_id'))
                    ->where('id', '!=', $id)
                    ->where(function ($query) {
                        $query->where('start_time', '<', $this->input('end_time'))
                            ->where('end_time', '>', $this->input('start_time'));
                    })
                    ->exists();

                if ($existsTimeAndRoomWithOthers) {
                    $validator->errors()->add('room_id', 'Lịch này đã có bác sĩ khác làm việc.');
                }
            }
        });
    }
}
