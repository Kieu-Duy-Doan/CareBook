<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('specialties')->latest();

        if ($request->filled('building')) {
            $query->where('building', $request->building);
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $rooms = $query->paginate(20)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.rooms.index', compact('rooms', 'specialties'));
    }

    public function create()
    {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.rooms.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'room_number' => 'nullable|string|max:20',
            'building' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:10',
            'room_type' => 'required|in:examination,diagnostic,surgery,other',
            'capacity' => 'nullable|integer|min:1|max:200',
            'is_active' => 'boolean',
            'specialty_ids' => 'nullable|array',
            'specialty_ids.*' => 'exists:specialties,id',
        ], [
            'required' => 'Vui lòng nhập/chọn trường này.',
            'max' => 'Vượt quá số ký tự cho phép.',
            'min' => 'Giá trị quá nhỏ.',
            'in' => 'Giá trị không hợp lệ.',
            'exists' => 'Dữ liệu không tồn tại.',
        ]);

        DB::transaction(function () use ($request) {
            $room = Room::create([
                'name' => $request->name,
                'room_number' => $request->room_number,
                'building' => $request->building,
                'floor' => $request->floor,
                'room_type' => $request->room_type,
                'capacity' => $request->capacity,
                'is_active' => $request->has('is_active'),
            ]);

            if ($request->has('specialty_ids')) {
                $syncData = [];
                foreach ($request->specialty_ids as $id) {
                    $syncData[$id] = ['is_primary' => false];
                }
                $room->specialties()->sync($syncData);
            }

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'ROOM_CREATED',
                'module' => 'room_management',
                'ref_type' => 'room',
                'ref_id' => $room->id,
                'description' => 'Thêm mới phòng khám: ' . $room->name,
                'ip_address' => request()->ip()
            ]);
        });
        return redirect()->route('admin.rooms.index')->with('success', 'Đã thêm phòng thành công.');
    }

    
}