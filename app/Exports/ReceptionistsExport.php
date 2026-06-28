<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class ReceptionistsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::with('staffProfile')
            ->where('role', 'receptionist');

        if ($this->request->filled('search')) {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%'.$this->request->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->request->search.'%')
                  ->orWhereHas('staffProfile', fn($sq) =>
                      $sq->where('employee_code', 'like', '%'.$this->request->search.'%')
                         ->orWhere('position', 'like', '%'.$this->request->search.'%')
                  );
            });
        }

        if ($this->request->filled('status')) {
            $query->where('is_active', $this->request->status);
        }

        if ($this->request->filled('department')) {
            $query->whereHas('staffProfile', fn($sq) =>
                $sq->where('department', 'like', '%'.$this->request->department.'%')
            );
        }

        return $query->latest('created_at');
    }

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

    public function map($receptionist): array
    {
        return [
            $receptionist->staffProfile->employee_code ?? '',
            $receptionist->full_name ?? '',
            $receptionist->phone ?? '',
            $receptionist->email ?? '',
            $receptionist->username ?? '',
            '', // Mật khẩu rỗng
            $receptionist->id_card ?? '',
            $receptionist->staffProfile->department ?? '',
            $receptionist->staffProfile->internal_phone ?? '',
            $receptionist->staffProfile->start_date ?? '',
            $receptionist->is_active ? 'Đang hoạt động' : 'Đã khoá',
        ];
    }
}
