<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function edit($id)
    {
        $room = Room::with('specialties')->findOrFail($id);
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.rooms.edit', compact('room', 'specialties'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

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
        ]);

        DB::transaction(function () use ($request, $room) {
            $room->update([
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
                foreach ($request->specialty_ids as $spId) {
                    $syncData[$spId] = ['is_primary' => false];
                }
                $room->specialties()->sync($syncData);
            } else {
                $room->specialties()->detach();
            }

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'ROOM_UPDATED',
                'module' => 'room_management',
                'ref_type' => 'room',
                'ref_id' => $room->id,
                'description' => 'Cập nhật phòng khám: ' . $room->name,
                'ip_address' => request()->ip()
            ]);
        });

        return redirect()->route('admin.rooms.index')->with('success', 'Đã cập nhật phòng thành công.');
    }
    
    public function toggleActive($id)
    {
        $room = Room::findOrFail($id);
        $room->is_active = !$room->is_active;
        $room->save();

        return back()->with('success', 'Đã cập nhật trạng thái phòng.');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        $hasActiveAppointments = \App\Models\Appointment::where('room_id', $room->id)
            ->whereIn('status', ['pending', 'checked_in', 'examining'])
            ->exists();

        if ($hasActiveAppointments) {
            return back()->with('error', 'Không thể xoá phòng đang có lịch hẹn chờ khám hoặc đang khám.');
        }

        $room->specialties()->detach();
        $room->delete();

        return back()->with('success', 'Đã xoá phòng thành công.');
    }

    public function show($id)
    {
        $room = Room::with([
            'specialties',
            'workSchedules.doctor.user'
        ])->findOrFail($id);

        $todayAppointments = $room->appointments()
            ->with(['patientProfile', 'doctorProfile.user'])
            ->whereDate('appointment_date', \Carbon\Carbon::today())
            ->orderBy('appointment_time')
            ->get();

        return view('admin.rooms.show', compact('room', 'todayAppointments'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ], [
            'file.required' => 'Vui lòng chọn file để import.',
            'file.file'     => 'File không hợp lệ.',
            'file.max'      => 'Kích thước file quá lớn (tối đa 10MB).',
        ]);

        $extension = $request->file('file')->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'File import phải có định dạng: xlsx, xls, hoặc csv.');
        }

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\RoomsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Import danh sách phòng khám thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi import: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RoomsExport($request), 'rooms_list.xlsx');
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RoomsTemplateExport, 'rooms_import_template.xlsx');
    }
}