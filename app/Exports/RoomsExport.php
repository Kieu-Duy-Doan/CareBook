<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class RoomsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Room::query();
        
        if ($this->request->filled('building')) {
            $query->where('building', $this->request->building);
        }

        if ($this->request->filled('room_type')) {
            $query->where('room_type', $this->request->room_type);
        }

        if ($this->request->filled('status')) {
            $query->where('is_active', $this->request->status);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên phòng (*)',
            'Số phòng',
            'Tòa nhà',
            'Tầng',
            'Loại phòng (*)',
            'Sức chứa',
            'Trạng thái'
        ];
    }

    public function map($room): array
    {
        return [
            $room->id,
            $room->name,
            $room->room_number,
            $room->building,
            $room->floor,
            $room->room_type,
            $room->capacity,
            $room->is_active ? 'Đang hoạt động' : 'Ngừng hoạt động',
        ];
    }
}
