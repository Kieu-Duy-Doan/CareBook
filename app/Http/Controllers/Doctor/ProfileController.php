<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\DoctorProfile;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile ?? new DoctorProfile();
        return view('doctor.profile.index', compact('user', 'doctorProfile'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => 'nullable|image|max:2048',
            'password' => 'nullable|min:8|confirmed',
            'expertise' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'bio' => 'nullable|string',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = \Illuminate\Support\Facades\Storage::url($path);
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        if ($user->doctorProfile) {
            $user->doctorProfile->update([
                'expertise' => $request->expertise,
                'experience_years' => $request->experience_years,
                'bio' => $request->bio,
            ]);
        } else {
            DoctorProfile::create([
                'user_id' => $user->id,
                'expertise' => $request->expertise,
                'experience_years' => $request->experience_years,
                'bio' => $request->bio,
                'doctor_code' => 'DOC' . time(),
                'level' => 'BS',
            ]);
        }

        return back()->with('success', 'Hồ sơ cá nhân đã được cập nhật.');
    }
}
