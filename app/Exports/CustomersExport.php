<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = User::where('role', 'patient')->withCount('patientProfiles')->latest('created_at');

        if ($this->request->filled('search')) {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%'.$this->request->search.'%')
                  ->orWhere('id_card', 'like', '%'.$this->request->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->request->search.'%')
                  ->orWhere('email', 'like', '%'.$this->request->search.'%')
                  ->orWhere('username', 'like', '%'.$this->request->search.'%');
            });
        }

        if ($this->request->filled('status')) {
            if ($this->request->status == '1') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên Đăng Nhập',
            'Họ Và Tên',
            'Số Điện Thoại',
            'CCCD / CMND',
            'Email',
            'Số lượng HSBN',
            'Trạng Thái',
            'Ngày Tạo',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->username,
            $customer->full_name,
            $customer->phone,
            $customer->id_card,
            $customer->email,
            $customer->patient_profiles_count,
            $customer->is_active ? 'Hoạt động' : 'Khoá',
            $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
