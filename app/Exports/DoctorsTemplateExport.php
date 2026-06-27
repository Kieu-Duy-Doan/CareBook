<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DoctorsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Mã BS',
            'Họ tên',
            'Số điện thoại',
            'Email',
            'Tên đăng nhập',
            'Mật khẩu',
            'Cấp độ',
            'Chức danh',
            'Kinh nghiệm',
            'Số CCHN',
            'Chuyên khoa chính',
            'Các chuyên khoa khác',
            'Trạng thái',
        ];
    }

    public function array(): array
    {
        return [
            [
                '', 
                'Bùi Xuân Huấn', 
                '0901234567', 
                'huanbx@gmail.com', 
                'bs.huanbx', 
                'Password@123', 
                'ThS', 
                'Thạc sĩ', 
                '5', 
                '123456/BYT-CCHN', 
                'Khám bệnh đa khoa',
                'Nội tiết, Tim mạch',
                'Đang hoạt động'
            ]
        ];
    }
}
