<?php

namespace App\Exports;

use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatientsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = PatientProfile::with(['user'])->latest('created_at');

        if ($this->request->filled('search')) {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%'.$this->request->search.'%')
                  ->orWhere('id_card', 'like', '%'.$this->request->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->request->search.'%')
                  ->orWhere('insurance_code', 'like', '%'.$this->request->search.'%')
                  ->orWhere('patient_code', 'like', '%'.$this->request->search.'%')
                  ->orWhereHas('user', fn($uq) =>
                      $uq->where('full_name', 'like', '%'.$this->request->search.'%')
                         ->orWhere('phone', 'like', '%'.$this->request->search.'%')
                  );
            });
        }

        if ($this->request->filled('status')) {
            if ($this->request->status == '1') {
                $query->whereHas('user', fn($uq) => $uq->where('is_active', true));
            } else {
                $query->whereHas('user', fn($uq) => $uq->where('is_active', false));
            }
        }

        if ($this->request->filled('has_insurance')) {
            if ($this->request->has_insurance == '1') {
                $query->whereNotNull('insurance_code');
            } else {
                $query->whereNull('insurance_code');
            }
        }

        if ($this->request->filled('is_self')) {
            $query->where('is_self', $this->request->is_self);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Mã Bệnh Nhân',
            'Họ Tên Hồ Sơ',
            'Ngày Sinh',
            'Giới Tính',
            'Số Điện Thoại',
            'CCCD/CMND',
            'Mã Thẻ BHYT',
            'Tài Khoản Liên Kết (Khách Hàng)',
            'Loại Hồ Sơ',
            'Ngày Tạo',
        ];
    }

    public function map($profile): array
    {
        $genderMap = [
            'male'   => 'Nam',
            'female' => 'Nữ',
            'other'  => 'Khác'
        ];

        return [
            $profile->patient_code,
            $profile->full_name,
            $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : '',
            $genderMap[$profile->gender] ?? $profile->gender,
            $profile->phone,
            $profile->id_card,
            $profile->insurance_code,
            $profile->user ? $profile->user->full_name . ' (' . $profile->user->phone . ')' : '',
            $profile->is_self ? 'Bản thân' : 'Người thân',
            $profile->created_at ? $profile->created_at->format('d/m/Y H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
