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
            'Tên đăng nhập',
            'Mật khẩu',
            'CMND/CCCD',
            'Email',
            'Trạng thái',
            'Chức danh',
            'Cấp độ',
            'Chuyên môn',
            'Kinh nghiệm',
            'Số CCHN',
            'Giới thiệu',
        ];
    }

    public function array(): array
    {
        return [
            [
                '',
                'Bùi Xuân Huấn', 
                '0901234567', 
                'bs.huanbx', 
                'Password@123',
                '012345678910',
                'huanbx@gmail.com', 
                'Đang hoạt động',
                'Thạc sĩ', 
                'ThS', 
                'Khám bệnh đa khoa',
                '5', 
                '123456/BYT-CCHN', 
                'Giới thiệu về bác sĩ...'
            ]
        ];
    }
}
