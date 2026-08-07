<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use App\Models\WorkSchedule;
use App\Models\ScheduleOverride;

class StoreScheduleOverrideRequest extends FormRequest
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
            'override_date' => 'required|date|after_or_equal:today',
            'type' => 'required|in:close,extra',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:255'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường :attribute không được để trống.',
            'exists' => 'Trường :attribute không hợp lệ.',
            'date' => 'Trường :attribute không đúng định dạng ngày.',
            'after_or_equal' => 'Trường :attribute phải từ ngày hôm nay trở đi.',
            'in' => 'Trường :attribute không hợp lệ.',
            'date_format' => 'Trường :attribute không đúng định dạng thời gian.',
            'after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'max' => 'Trường :attribute không được dài quá :max ký tự.',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
        ];
    }

    public function attributes()
    {
        return [
            'doctor_profile_id' => 'bác sĩ',
            'room_id' => 'phòng khám',
            'override_date' => 'ngày ngoại lệ',
            'type' => 'loại ngoại lệ',
            'start_time' => 'thời gian bắt đầu',
            'end_time' => 'thời gian kết thúc',
            'reason' => 'lý do',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isValidTime = ($this->input('start_time') === '07:00' && $this->input('end_time') === '11:00') ||
                ($this->input('start_time') === '13:00' && $this->input('end_time') === '17:00');

            if (!$isValidTime) {
                $validator->errors()->add('start_time', 'Thời gian ca trực chỉ được phép là Sáng (07:00 - 11:00) hoặc Chiều (13:00 - 17:00).');
                return;
            }

            $dayOfWeek = Carbon::parse($this->input('override_date'))->dayOfWeekIso + 1;
            if ($dayOfWeek == 8) {
                $dayOfWeek = 1;
            }

            $overlapQuery = WorkSchedule::where('doctor_profile_id', $this->input('doctor_profile_id'))
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('start_time', '<', $this->input('end_time'))
                        ->where('end_time', '>', $this->input('start_time'));
                });

            if ($this->filled('room_id')) {
                $overlapQuery->where('room_id', $this->input('room_id'));
            }

            $existsTime = $overlapQuery->exists();

            if ($this->input('type') === 'extra') {
                if ($existsTime) {
                    $validator->errors()->add('type', 'Không thể thêm ca. Bác sĩ đã có lịch làm việc trùng thời gian này.');
                }

                if ($this->filled('room_id')) {
                    $existsWithOtherDoctors = WorkSchedule::where('day_of_week', $dayOfWeek)
                        ->where('is_active', true)
                        ->where('room_id', $this->input('room_id'))
                        ->where('doctor_profile_id', '!=', $this->input('doctor_profile_id'))
                        ->where(function ($query) {
                            $query->where('start_time', '<', $this->input('end_time'))
                                ->where('end_time', '>', $this->input('start_time'));
                        })
                        ->exists();

                    if ($existsWithOtherDoctors) {
                        $validator->errors()->add('room_id', 'Không thể thêm ca. Phòng này đã có bác sĩ khác làm việc theo lịch định kỳ.');
                    }

                    $existsOverrideWithOtherDoctors = ScheduleOverride::where('override_date', $this->input('override_date'))
                        ->where('type', 'extra')
                        ->where('room_id', $this->input('room_id'))
                        ->where('doctor_profile_id', '!=', $this->input('doctor_profile_id'))
                        ->where(function ($query) {
                            $query->where('start_time', '<', $this->input('end_time'))
                                ->where('end_time', '>', $this->input('start_time'));
                        })
                        ->exists();

                    if ($existsOverrideWithOtherDoctors) {
                        $validator->errors()->add('room_id', 'Không thể thêm ca. Phòng này đã có bác sĩ khác đăng ký ca ngoại lệ.');
                    }
                }
            }

            if ($this->input('type') === 'close' && !$existsTime) {
                $validator->errors()->add('type', 'Không thể đóng ca. Không có lịch làm việc nào trùng thời gian này để đóng.');
            }
        });
    }
}
