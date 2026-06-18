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

    public function store(Request $request)
    {
        $request->validate([
            'doctor_profile_id' => 'required|exists:doctor_profiles,id',
            'room_id' => 'required|exists:rooms,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration_minutes' => 'required|integer|min:5|max:120',
            'max_slots' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean'
        ]);

        $existsRoom = WorkSchedule::where('doctor_profile_id', $request->doctor_profile_id)
            ->where('room_id', $request->room_id)
            ->where('day_of_week', $request->day_of_week)
            ->exists();

        if ($existsRoom) {
            return back()->with('error', 'Bác sĩ này đã có lịch tại phòng này vào thứ đã chọn.');
        }

        $existsTime = WorkSchedule::where('doctor_profile_id', $request->doctor_profile_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                    ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($existsTime) {
            return back()->with('error', 'Bác sĩ đã có lịch làm việc trùng thời gian.');
        }

        $schedule = WorkSchedule::create([
            'doctor_profile_id' => $request->doctor_profile_id,
            'room_id' => $request->room_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_duration_minutes' => $request->slot_duration_minutes,
            'max_slots' => $request->max_slots,
            'is_active' => $request->has('is_active'),
        ]);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'WORK_SCHEDULE_CREATED',
            'module' => 'work_schedule',
            'ref_type' => 'work_schedule',
            'ref_id' => $schedule->id,
            'description' => 'Thêm ca trực cho bác sĩ ID ' . $schedule->doctor_profile_id,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã thêm ca trực thành công.');
    }
}
