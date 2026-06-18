<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Appointment;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'    => User::where('role', 'patient')->count(),
            'active'   => User::where('role', 'patient')->where('is_active', true)->count(),
            'locked'   => User::where('role', 'patient')->where('is_active', false)->count(),
            'profiles' => PatientProfile::count(),
        ];

        $query = User::with(['patientProfiles'])
            ->where('role', 'patient')
            ->latest('created_at');

        // Search theo tên, SĐT, email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('id_card', 'like', '%'.$request->search.'%')
                  ->orWhereHas('patientProfiles', fn($pq) =>
                      $pq->where('insurance_code', 'like', '%'.$request->search.'%')
                  );
            });
        }

        // Filter trạng thái
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Filter có BHYT hay không
        if ($request->filled('has_insurance')) {
            if ($request->has_insurance == '1') {
                $query->whereHas('patientProfiles', fn($pq) =>
                    $pq->whereNotNull('insurance_code')
                );
            } else {
                $query->whereDoesntHave('patientProfiles', fn($pq) =>
                    $pq->whereNotNull('insurance_code')
                );
            }
        }

        $patients = $query->paginate(15)->withQueryString();

        return view('admin.patients.index', compact('patients', 'stats'));
    }
    