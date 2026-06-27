<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WorkSchedulesTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'BS01',
                'Phòng Khám 01',
                '2',
                'Sáng',
                '15',
                '16',
                'Đang hoạt động'
            ],
            [
                'BS02',
                'Phòng Khám 02',
                '3',
                'Chiều',
                '20',
                '12',
                'Đang hoạt động'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Mã Bác sĩ (*)',
            'Tên phòng (*)',
            'Thứ (1-7) (*)',
            'Ca làm việc (Sáng/Chiều) (*)',
            'Thời gian khám mỗi bệnh nhân (phút)',
            'Số bệnh nhân tối đa',
            'Trạng thái'
        ];
    }
}
