<?php

namespace App\Exports;

use App\Models\WorkSchedule;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class WorkSchedulesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = WorkSchedule::with(['doctor.user', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time');
        
        if ($this->request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $this->request->doctor_id);
        }

        if ($this->request->filled('room_id')) {
            $query->where('room_id', $this->request->room_id);
        }

        if ($this->request->filled('day_of_week')) {
            $query->where('day_of_week', $this->request->day_of_week);
        }

        if ($this->request->filled('status')) {
            $query->where('is_active', $this->request->status);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Mã Bác sĩ (*)',
            'Tên Bác sĩ',
            'Tên phòng (*)',
            'Thứ (1-7) (*)',
            'Ca làm việc (Sáng/Chiều) (*)',
            'Giờ bắt đầu',
            'Giờ kết thúc',
            'Thời gian khám mỗi bệnh nhân (phút)',
            'Số bệnh nhân tối đa',
            'Trạng thái'
        ];
    }

    public function map($schedule): array
    {
        $shift = 'Khác';
        if (substr($schedule->start_time, 0, 5) === '07:00' && substr($schedule->end_time, 0, 5) === '11:00') {
            $shift = 'Sáng';
        } elseif (substr($schedule->start_time, 0, 5) === '13:00' && substr($schedule->end_time, 0, 5) === '17:00') {
            $shift = 'Chiều';
        }

        return [
            $schedule->id,
            $schedule->doctor->doctor_code ?? '',
            $schedule->doctor->user->full_name ?? '',
            $schedule->room->name ?? '',
            $schedule->day_of_week,
            $shift,
            substr($schedule->start_time, 0, 5),
            substr($schedule->end_time, 0, 5),
            $schedule->slot_duration_minutes,
            $schedule->max_slots,
            $schedule->is_active ? 'Đang hoạt động' : 'Ngừng hoạt động',
        ];
    }
}
