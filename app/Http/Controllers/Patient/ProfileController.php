<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profiles = PatientProfile::where('owner_id', auth()->id())
            ->orderByDesc('is_self')
            ->orderBy('full_name')
            ->get();

        return view('patient.profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('patient.profiles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:M,F,O',
            'id_card' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'ethnicity' => 'nullable|string|max:50',
            'insurance_code' => 'nullable|string|max:50',
        ]);

        $validated['owner_id'] = auth()->id();
        $validated['is_self'] = false; // Add new profiles are not self by default

        PatientProfile::create($validated);

        if ($request->query('redirect') === 'booking') {
            return redirect()->route('patient.booking.index')->with('success', 'Thêm hồ sơ thành công.');
        }

        return redirect()->route('patient.profiles.index')->with('success', 'Thêm hồ sơ thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        return view('patient.profiles.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:M,F,O',
            'id_card' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'ethnicity' => 'nullable|string|max:50',
            'insurance_code' => 'nullable|string|max:50',
        ]);

        $profile->update($validated);

        return redirect()->route('patient.profiles.index')->with('success', 'Cập nhật hồ sơ thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        if ($profile->is_self) {
            return back()->with('error', 'Không thể xóa hồ sơ chính chủ.');
        }

        if ($profile->appointments()->exists()) {
            return back()->with('error', 'Không thể xóa hồ sơ đã có lịch sử đặt khám.');
        }

        $profile->delete();

        return redirect()->route('patient.profiles.index')->with('success', 'Xóa hồ sơ thành công.');
    }
}
