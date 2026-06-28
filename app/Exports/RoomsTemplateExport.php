<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RoomsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Tên phòng (*)',
            'Số phòng',
            'Tòa nhà',
            'Tầng',
            'Loại phòng (*)',
            'Sức chứa',
            'Trạng thái'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Phòng khám VIP 1', 
                '101', 
                'Khu A', 
                'Tầng 1', 
                'examination', 
                '5', 
                'Đang hoạt động'
            ]
        ];
    }
}
