<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $specialties = Specialty::where('is_active', true)->orderBy('display_order')->get(['id', 'name']);

        $query = DoctorProfile::with(['user:id,full_name,avatar_url', 'specialties:id,name'])
            ->whereHas('workSchedules', fn($q) => $q->where('is_active', true));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('specialty_id')) {
            $query->whereHas('specialties', function ($q) use ($request) {
                $q->where('specialties.id', $request->input('specialty_id'));
            });
        }

        $doctors = $query->paginate(12)->withQueryString();

        return view('doctors.index', compact('doctors', 'specialties'));
    }
}
