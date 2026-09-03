<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsuranceType;
use App\Models\PatientProfile;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsuranceTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = InsuranceType::query()->orderBy('prefix');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prefix', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $insuranceTypes = $query->get();

        return view('admin.insurance-types.index', compact('insuranceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:5|unique:insurance_types,prefix|regex:/^[A-Za-z]+$/',
            'name' => 'required|string|max:100',
            'coverage_percent' => 'required|integer|min:0|max:100',
        ], [
            'prefix.required' => 'Mã đầu thẻ không được để trống.',
            'prefix.unique' => 'Mã đầu thẻ này đã tồn tại.',
            'prefix.max' => 'Mã đầu thẻ tối đa 5 ký tự.',
            'prefix.regex' => 'Mã đầu thẻ chỉ chứa chữ cái.',
            'name.required' => 'Tên loại BHYT không được để trống.',
            'coverage_percent.required' => 'Tỷ lệ chi trả không được để trống.',
            'coverage_percent.min' => 'Tỷ lệ chi trả tối thiểu là 0%.',
            'coverage_percent.max' => 'Tỷ lệ chi trả tối đa là 100%.',
        ]);

        $validated['prefix'] = strtoupper($validated['prefix']);

        $insuranceType = InsuranceType::create($validated);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'INSURANCE_TYPE_CREATED',
            'module' => 'service_management',
            'ref_type' => 'insurance_type',
            'ref_id' => $insuranceType->id,
            'description' => 'Thêm loại BHYT mới: ' . $insuranceType->prefix . ' (' . $insuranceType->name . ' - ' . $insuranceType->coverage_percent . '%)',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.insurance-types.index')
            ->with('success', 'Đã thêm loại BHYT "' . $validated['prefix'] . '" thành công.');
    }

    public function update(Request $request, $id)
    {
        $insuranceType = InsuranceType::findOrFail($id);

        $validated = $request->validate([
            'prefix' => 'required|string|max:5|regex:/^[A-Za-z]+$/|unique:insurance_types,prefix,' . $id,
            'name' => 'required|string|max:100',
            'coverage_percent' => 'required|integer|min:0|max:100',
        ], [
            'prefix.required' => 'Mã đầu thẻ không được để trống.',
            'prefix.unique' => 'Mã đầu thẻ này đã tồn tại.',
            'prefix.max' => 'Mã đầu thẻ tối đa 5 ký tự.',
            'prefix.regex' => 'Mã đầu thẻ chỉ chứa chữ cái.',
            'name.required' => 'Tên loại BHYT không được để trống.',
            'coverage_percent.required' => 'Tỷ lệ chi trả không được để trống.',
            'coverage_percent.min' => 'Tỷ lệ chi trả tối thiểu là 0%.',
            'coverage_percent.max' => 'Tỷ lệ chi trả tối đa là 100%.',
        ]);

        $validated['prefix'] = strtoupper($validated['prefix']);

        $insuranceType->update($validated);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'INSURANCE_TYPE_UPDATED',
            'module' => 'service_management',
            'ref_type' => 'insurance_type',
            'ref_id' => $insuranceType->id,
            'description' => 'Cập nhật loại BHYT: ' . $insuranceType->prefix . ' (' . $insuranceType->name . ' - ' . $insuranceType->coverage_percent . '%)',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.insurance-types.index')
            ->with('success', 'Đã cập nhật loại BHYT "' . $validated['prefix'] . '" thành công.');
    }

    public function toggleActive($id)
    {
        $insuranceType = InsuranceType::findOrFail($id);
        $insuranceType->update(['is_active' => !$insuranceType->is_active]);

        $status = $insuranceType->is_active ? 'kích hoạt' : 'vô hiệu hóa';

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'INSURANCE_TYPE_TOGGLED',
            'module' => 'service_management',
            'ref_type' => 'insurance_type',
            'ref_id' => $insuranceType->id,
            'description' => ($insuranceType->is_active ? 'Kích hoạt' : 'Vô hiệu hóa') . ' loại BHYT: ' . $insuranceType->prefix,
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('admin.insurance-types.index')
            ->with('success', "Đã {$status} loại BHYT \"{$insuranceType->prefix}\".");
    }

    public function destroy($id)
    {
        $insuranceType = InsuranceType::findOrFail($id);
        $prefix = $insuranceType->prefix;

        $isUsed = PatientProfile::where('insurance_code', 'like', $prefix . '%')->exists();
        if ($isUsed) {
            return redirect()->route('admin.insurance-types.index')
                ->with('error', "Không thể xóa loại BHYT \"{$prefix}\" vì đang có hồ sơ bệnh nhân sử dụng mã thẻ này. Hãy sử dụng tính năng Vô hiệu hóa.");
        }

        $insuranceType->delete();

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'INSURANCE_TYPE_DELETED',
            'module' => 'service_management',
            'ref_type' => 'insurance_type',
            'ref_id' => $id,
            'description' => 'Xóa loại BHYT: ' . $prefix,
            'ip_address' => request()->ip()
        ]);

        return redirect()->route('admin.insurance-types.index')
            ->with('success', "Đã xóa loại BHYT \"{$prefix}\".");
    }
}
