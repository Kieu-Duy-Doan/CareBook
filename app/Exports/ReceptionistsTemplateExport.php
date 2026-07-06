<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReceptionistsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Mã NV',
            'Họ tên',
            'Số điện thoại',
            'Tên đăng nhập',
            'Mật khẩu',
            'CMND/CCCD',
            'Email',
            'Trạng thái',
            'Vị trí',
            'Phòng ban',
            'SĐT nội bộ',
            'Ngày vào làm',
        ];
    }

    public function array(): array
    {
        return [
            [
                '', 
                'Trần Thị B', 
                '0912345678', 
                'lt.tranthib', 
                'Password@123', 
                '012345678910', 
                'tranthib@gmail.com', 
                'Đang hoạt động',
                'Lễ tân',
                'Tiếp nhận bệnh nhân', 
                '101', 
                '2023-01-01', 
            ]
        ];
    }
}
