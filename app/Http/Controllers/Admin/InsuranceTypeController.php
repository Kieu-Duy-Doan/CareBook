<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsuranceType;
use Illuminate\Http\Request;

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

        InsuranceType::create($validated);

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

        return redirect()->route('admin.insurance-types.index')
            ->with('success', 'Đã cập nhật loại BHYT "' . $validated['prefix'] . '" thành công.');
    }

    public function toggleActive($id)
    {
        $insuranceType = InsuranceType::findOrFail($id);
        $insuranceType->update(['is_active' => !$insuranceType->is_active]);

        $status = $insuranceType->is_active ? 'kích hoạt' : 'vô hiệu hóa';

        return redirect()->route('admin.insurance-types.index')
            ->with('success', "Đã {$status} loại BHYT \"{$insuranceType->prefix}\".");
    }

    public function destroy($id)
    {
        $insuranceType = InsuranceType::findOrFail($id);
        $prefix = $insuranceType->prefix;
        $insuranceType->delete();

        return redirect()->route('admin.insurance-types.index')
            ->with('success', "Đã xóa loại BHYT \"{$prefix}\".");
    }
}
