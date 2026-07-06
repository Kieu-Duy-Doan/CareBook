<?php

namespace App\Exports;

use App\Models\Specialty;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class SpecialtiesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Specialty::query();
        
        if ($this->request->filled('search')) {
            $query->where('name', 'like', '%'.$this->request->search.'%')
                  ->orWhere('description', 'like', '%'.$this->request->search.'%');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên chuyên khoa (*)',
            'Mô tả',
            'Thứ tự hiển thị',
            'Trạng thái'
        ];
    }

    public function map($specialty): array
    {
        return [
            $specialty->id,
            $specialty->name,
            $specialty->description,
            $specialty->display_order,
            $specialty->is_active ? 'Đang hoạt động' : 'Ngừng hoạt động',
        ];
    }
}
