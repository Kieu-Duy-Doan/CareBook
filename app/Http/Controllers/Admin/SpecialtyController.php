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
            'image' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
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

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('specialties', 'public');
            $data['image_url'] = $path;
        };

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
            'image' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
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

         if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($specialty->image_url) {
                Storage::disk('public')->delete($specialty->image_url);
            }
            
            $path = $request->file('image')->store('specialties', 'public');
            $data['image_url'] = $path;
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
}