<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SpecialtyController extends Controller {
public function index() 
    {
     $specialties = Specialty::withCount(['doctors', 'rooms'])
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.specialties.index', compact('specialties'));
    }
public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:specialties,name',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập tên chuyên khoa.',
            'name.unique' => 'Tên chuyên khoa đã tồn tại.',
            'display_order.min' => 'Thứ tự không hợp lệ.',
        ]);  

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'display_order' => $request->display_order ?? 0,
            'is_active' => $request->has('is_active'),
        ];

        if ($imagePath = $this->uploadImage($request)) {
            $data['image_url'] = $imagePath;
        }

        $specialty = Specialty::create($data);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_CREATED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => 'Thêm mới chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã thêm chuyên khoa thành công.');
    }

    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('specialties')->ignore($specialty->id)],
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập tên chuyên khoa.',
            'name.unique' => 'Tên chuyên khoa đã tồn tại.',
            'display_order.min' => 'Thứ tự không hợp lệ.',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'display_order' => $request->display_order ?? 0,
            'is_active' => $request->has('is_active'),
        ];

        if ($imagePath = $this->uploadImage($request)) {
            if ($specialty->image_url) {
                Storage::disk('public')->delete($specialty->image_url);
            }
            $data['image_url'] = $imagePath;
        }

        $specialty->update($data);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_UPDATED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => 'Cập nhật chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã cập nhật chuyên khoa thành công.');
    }

    public function toggleActive($id)
    {
        $specialty = Specialty::findOrFail($id);
        $specialty->is_active = !$specialty->is_active;
        $specialty->save();

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_TOGGLED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => ($specialty->is_active ? 'Hiển thị' : 'Ẩn') . ' chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái chuyên khoa.');
    }

    protected function uploadImage(Request $request)
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        if (! $file->isValid()) {
            return null;
        }

        $path = $file->store('specialties', 'public');
        return str_replace('\\', '/', $path);
    }

    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'display_order' => 'required|integer|min:0',
        ]);

        $specialty = Specialty::findOrFail($id);
        $specialty->display_order = $request->display_order;
        $specialty->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $specialty = Specialty::withCount('doctors')->findOrFail($id);

        if ($specialty->doctors_count > 0) {
            return back()->with('error', 'Không thể xoá chuyên khoa đang có bác sĩ hoạt động.');
        }

        $hasActiveAppointments = \App\Models\Appointment::where('specialty_id', $specialty->id)
            ->whereIn('status', ['pending', 'checked_in', 'examining'])
            ->exists();

        if ($hasActiveAppointments) {
            return back()->with('error', 'Không thể xoá chuyên khoa đang có lịch hẹn chờ khám hoặc đang khám.');
        }

        $name = $specialty->name;
        $imageUrl = $specialty->image_url;

        // specialties has ManyToMany with rooms and doctor_profiles.
        $specialty->rooms()->detach();
        $specialty->doctors()->detach();

        $specialty->delete(); // Now safe to delete the main record.

        // Delete image file
        if ($imageUrl) {
            Storage::disk('public')->delete($imageUrl);
        }

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_DELETED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $id,
            'description' => 'Xoá chuyên khoa: ' . $name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã xoá chuyên khoa thành công.');
    }

    public function show($id)
    {
        $specialty = Specialty::with(['doctors.user', 'rooms'])->findOrFail($id);
        
        return view('admin.specialties.show', compact('specialty'));
    }

    public function addDoctor(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);
        $doctorId = $request->input('doctor_id');
        $isPrimary = $request->input('is_primary', 0);

        $specialty->doctors()->attach($doctorId, ['is_primary' => $isPrimary]);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_DOCTOR_ADDED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => 'Thêm bác sĩ vào chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã thêm bác sĩ vào chuyên khoa.');
    }

    public function removeDoctor(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);
        $doctorId = $request->input('doctor_id');

        $specialty->doctors()->detach($doctorId);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_DOCTOR_REMOVED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => 'Xóa bác sĩ khỏi chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã xóa bác sĩ khỏi chuyên khoa.']);
        }
        return back()->with('success', 'Đã xóa bác sĩ khỏi chuyên khoa.');
    }

    public function addRoom(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);
        $roomId = $request->input('room_id');
        $isPrimary = $request->input('is_primary', 0);

        $specialty->rooms()->attach($roomId, ['is_primary' => $isPrimary]);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_ROOM_ADDED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => 'Thêm phòng vào chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã thêm phòng vào chuyên khoa.');
    }

    public function removeRoom(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);
        $roomId = $request->input('room_id');

        $specialty->rooms()->detach($roomId);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'SPECIALTY_ROOM_REMOVED',
            'module' => 'specialty_management',
            'ref_type' => 'specialty',
            'ref_id' => $specialty->id,
            'description' => 'Xóa phòng khỏi chuyên khoa: ' . $specialty->name,
            'ip_address' => request()->ip()
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã xóa phòng khỏi chuyên khoa.']);
        }
        return back()->with('success', 'Đã xóa phòng khỏi chuyên khoa.');
    }
}