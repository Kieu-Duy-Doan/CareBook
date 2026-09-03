<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorLevelFee;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorLevelFeeController extends Controller
{
    public function index()
    {
        $fees = DoctorLevelFee::orderByRaw("FIELD(level, 'BS','BSCK1','BSCK2','ThS','TS','PGS','GS')")->get();
        return view('admin.doctor-level-fees.index', compact('fees'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'fees' => 'required|array',
            'fees.*.base_price' => 'required|numeric|min:0',
            'fees.*.specific_price' => 'required|numeric|min:0',
        ]);

        foreach ($request->fees as $id => $data) {
            DoctorLevelFee::where('id', $id)->update([
                'base_price' => $data['base_price'],
                'specific_price' => $data['specific_price'],
            ]);
        }

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'DOCTOR_LEVEL_FEES_UPDATED',
            'module' => 'service_management',
            'ref_type' => 'doctor_level_fees',
            'ref_id' => null,
            'description' => 'Cập nhật biểu phí khám theo cấp bậc bác sĩ',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.doctor-level-fees.index')
            ->with('success', 'Đã cập nhật biểu phí khám thành công!');
    }
}
