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
            'Email',
            'Tên đăng nhập',
            'Mật khẩu',
            'Số CCCD',
            'Phòng ban',
            'SĐT nội bộ',
            'Ngày vào làm',
            'Trạng thái'
        ];
    }

    public function array(): array
    {
        return [
            [
                '', 
                'Trần Thị B', 
                '0912345678', 
                'tranthib@gmail.com', 
                'lt.tranthib', 
                'Password@123', 
                '012345678910', 
                'Tiếp nhận bệnh nhân', 
                '101', 
                '2023-01-01', 
                'Đang hoạt động'
            ]
        ];
    }
}
