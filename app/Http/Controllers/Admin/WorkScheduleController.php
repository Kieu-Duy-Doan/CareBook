<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkSchedule;
use App\Models\ScheduleOverride;
use App\Models\DoctorProfile;
use App\Models\Room;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class WorkScheduleController extends Controller
{
    public function index(Request $request)
    {
        $doctors = DoctorProfile::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        $query = WorkSchedule::with(['doctor.user', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $schedules = $query->paginate(15)->withQueryString();

        $overrides = ScheduleOverride::with(['doctor.user', 'room', 'createdBy'])
            ->whereMonth('override_date', now()->month)
            ->whereYear('override_date', now()->year)
            ->orderBy('override_date')
            ->get();

        return view('admin.work-schedules.index', compact('schedules', 'doctors', 'rooms', 'overrides'));
    }
}
