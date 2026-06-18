<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}