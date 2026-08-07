<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Services\RoomService;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }
    public function index(Request $request)
    {
        $query = Room::with('specialties')->withCount('specialties')->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

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

    public function store(StoreRoomRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');
        $specialtyIds = $request->input('specialty_ids', []);

        $this->roomService->createRoom($validated, $specialtyIds);
        
        return redirect()->route('admin.rooms.index')->with('success', 'Đã thêm phòng thành công.');
    }

    public function edit($id)
    {
        $room = Room::with('specialties')->findOrFail($id);
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        return view('admin.rooms.edit', compact('room', 'specialties'));
    }

    public function update(UpdateRoomRequest $request, $id)
    {
        $room = Room::findOrFail($id);
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');
        
        $specialtyIds = $request->input('specialty_ids', []);
        $hasSpecialties = $request->has('specialty_ids');

        $this->roomService->updateRoom($room, $validated, $specialtyIds, $hasSpecialties);

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

        $hasWorkSchedules = $room->workSchedules()->exists();
        if ($hasWorkSchedules) {
            return back()->with('error', 'Không thể xoá phòng khám này vì đang có bác sĩ (lịch làm việc) được xếp tại đây.');
        }

        try {
            DB::transaction(function () use ($room) {
                $room->specialties()->detach();
                $room->delete();
            });
            return back()->with('success', 'Đã xoá phòng thành công.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Không thể xoá phòng khám này vì dữ liệu đang được sử dụng ở nơi khác.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xoá phòng khám.');
        }
    }

    public function show($id)
    {
        $room = Room::with([
            'specialties',
            'workSchedules.doctor.user'
        ])->withCount('specialties')->findOrFail($id);

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