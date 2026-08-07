<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use App\Http\Requests\Patient\StoreProfileRequest;
use App\Http\Requests\Patient\UpdateProfileRequest;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $profile = PatientProfile::where('owner_id', $user->id)
            ->where('is_self', true)
            ->first();

        return view('patient.dashboard.profile', compact('user', 'profile'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $isSelf = $request->query('is_self') === '1';
        return view('patient.profiles.create', compact('isSelf'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfileRequest $request)
    {
        $isSelfRequested = $request->input('is_self') == '1';
        $hasSelf = PatientProfile::where('owner_id', auth()->id())->where('is_self', true)->exists();
        $isSelf = $isSelfRequested && !$hasSelf;

        if (!$isSelf) {
            $familyCount = PatientProfile::where('owner_id', auth()->id())->where('is_self', false)->count();
            if ($familyCount >= 5) {
                return back()->with('error', 'Bạn chỉ được phép quản lý tối đa 5 hồ sơ người thân.')->withInput();
            }
        }

        $validated = $request->validated();

        if ($isSelf && auth()->user()->id_card) {
            $validated['id_card'] = auth()->user()->id_card;
        }

        $genderMap = ['M' => 'male', 'F' => 'female', 'O' => 'other'];
        $validated['gender'] = $genderMap[$validated['gender']] ?? $validated['gender'];

        $validated['owner_id'] = auth()->id();
        $validated['is_self'] = $isSelf;

        if ($isSelf) {
            $user = auth()->user();
            $userData = [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'id_card' => $validated['id_card'],
            ];
            if (array_key_exists('email', $validated)) {
                $userData['email'] = $validated['email'];
                unset($validated['email']);
            }
            $user->update($userData);
        }

        PatientProfile::create($validated);

        if ($request->query('redirect') === 'booking') {
            return redirect()->route('patient.booking.step1')->with('success', 'Thêm hồ sơ thành công.');
        }

        if ($isSelf) {
            return redirect()->route('patient.profiles.index')->with('success', 'Cập nhật thông tin cá nhân thành công.');
        }

        return redirect()->route('patient.family.index')->with('success', 'Thêm hồ sơ người thân thành công.');
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
    public function update(UpdateProfileRequest $request, PatientProfile $profile)
    {
        if ($profile->owner_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        if ($profile->id_card) {
            $validated['id_card'] = $profile->id_card;
        }

        if ($profile->is_self) {
            $user = auth()->user();
            $userData = [
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'id_card' => $validated['id_card'],
            ];
            if (array_key_exists('email', $validated)) {
                $userData['email'] = $validated['email'];
                unset($validated['email']);
            }
            $user->update($userData);
        }

        $genderMap = ['M' => 'male', 'F' => 'female', 'O' => 'other'];
        $validated['gender'] = $genderMap[$validated['gender']] ?? $validated['gender'];

        $profile->update($validated);

        if ($profile->is_self) {
            return redirect()->route('patient.profiles.index')->with('success', 'Cập nhật thông tin thành công.');
        }

        return redirect()->route('patient.family.index')->with('success', 'Cập nhật hồ sơ người thân thành công.');
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

        return redirect()->route('patient.family.index')->with('success', 'Xóa hồ sơ thành công.');
    }
}
