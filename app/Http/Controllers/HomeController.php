<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Models\DoctorProfile;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $specialties = Specialty::where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'name', 'description', 'image_url']);

        $doctors = DoctorProfile::with([
            'user:id,full_name,avatar_url',
            'specialties:id,name',
        ])
        ->whereHas('workSchedules', fn($q) => $q->where('is_active', true))
        ->get();

        return view('home', compact('specialties', 'doctors'));
    }
}
