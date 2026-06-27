<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SpecialtiesTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Tên chuyên khoa (*)',
            'Mô tả',
            'Thứ tự hiển thị',
            'Trạng thái'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Khám bệnh đa khoa', 
                'Chuyên khám và điều trị các bệnh lý chung...', 
                '1', 
                'Đang hoạt động'
            ]
        ];
    }
}
