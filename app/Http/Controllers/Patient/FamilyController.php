<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index()
    {
        $profiles = PatientProfile::where('owner_id', auth()->id())
            ->where('is_self', false) // Only family members
            ->orderBy('full_name')
            ->get();

        return view('patient.profiles.index', [
            'profiles' => $profiles,
            'title' => 'Quản lý gia đình',
            'activeMenu' => 'family',
            'emptyMessageTitle' => 'Chưa có thành viên gia đình',
            'emptyMessageDesc' => 'Thêm hồ sơ người thân để đặt lịch khám cho họ.'
        ]);
    }
}