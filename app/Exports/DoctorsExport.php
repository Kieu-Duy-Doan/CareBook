<?php

namespace App\Exports;

use App\Models\DoctorProfile;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class DoctorsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = DoctorProfile::with(['user', 'specialties'])->whereHas('user');

        if ($this->request->filled('search')) {
            $query->where(function($q) {
                $q->where('doctor_code', 'like', '%'.$this->request->search.'%')
                  ->orWhereHas('user', fn($uq) =>
                      $uq->where('full_name', 'like', '%'.$this->request->search.'%')
                         ->orWhere('phone', 'like', '%'.$this->request->search.'%')
                  );
            });
        }

        if ($this->request->filled('specialty_id')) {
            $query->whereHas('specialties', fn($q) =>
                $q->where('specialties.id', $this->request->specialty_id)
            );
        }

        if ($this->request->filled('level')) {
            $query->where('level', $this->request->level);
        }

        if ($this->request->filled('status')) {
            $query->whereHas('user', fn($q) =>
                $q->where('is_active', $this->request->status)
            );
        }

        return $query->latest('created_at');
    }

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

    public function map($doctor): array
    {
        $primarySpecialty = $doctor->primary_specialty ? $doctor->primary_specialty->name : '';
        $otherSpecialties = $doctor->specialties->where('id', '!=', $doctor->primary_specialty?->id)->pluck('name')->implode(', ');

        return [
            $doctor->doctor_code,
            $doctor->user->full_name ?? '',
            $doctor->user->phone ?? '',
            $doctor->user->email ?? '',
            $doctor->user->username ?? '',
            '', // Mật khẩu rỗng
            $doctor->level,
            $doctor->academic_title,
            $doctor->experience_years,
            $doctor->license_number,
            $primarySpecialty,
            $otherSpecialties,
            $doctor->user->is_active ? 'Đang hoạt động' : 'Đã khoá',
        ];
    }
}
