<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkSchedule;
use App\Models\ScheduleOverride;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        // Try to find an override for today
        $todayOverride = ScheduleOverride::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('override_date', Carbon::today())
            ->orderBy('start_time')
            ->first();

        if ($todayOverride) {
            return redirect()->route('doctor.work-schedules.show', ['schedule' => $todayOverride->id, 'type' => 'override']);
        }

        $iso = Carbon::today()->dayOfWeekIso;
        $todayDayOfWeek = $iso == 7 ? 1 : $iso + 1;

        // Try to find a normal schedule for today
        $todaySchedule = WorkSchedule::where('doctor_profile_id', $doctorProfile->id)
            ->where('day_of_week', $todayDayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->first();

        if ($todaySchedule) {
            return redirect()->route('doctor.work-schedules.show', $todaySchedule->id);
        }

        // Try to find ANY active schedule
        $anySchedule = WorkSchedule::where('doctor_profile_id', $doctorProfile->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->first();
            
        if ($anySchedule) {
            return redirect()->route('doctor.work-schedules.show', $anySchedule->id);
        }

        $query = WorkSchedule::with(['room'])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->orderBy('start_time', 'asc');

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $schedules = $query->paginate(15)->withQueryString();

        $overrides = ScheduleOverride::with(['room'])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->whereMonth('override_date', now()->month)
            ->whereYear('override_date', now()->year)
            ->orderBy('override_date')
            ->get();

        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        return view('doctor.work-schedules.index', compact('schedules', 'overrides', 'rooms'));
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $isOverride = $request->query('type') === 'override';
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        if ($isOverride) {
            $schedule = ScheduleOverride::with(['room'])
                ->where('doctor_profile_id', $doctorProfile->id)
                ->findOrFail($id);
                
            $targetDate = Carbon::parse($schedule->override_date);
            
            // Format to match view structure
            $schedule->day_of_week = $targetDate->dayOfWeekIso + 1;
            if ($schedule->day_of_week == 8) {
                $schedule->day_of_week = 1;
            }
        } else {
            $schedule = WorkSchedule::with(['room'])
                ->where('doctor_profile_id', $doctorProfile->id)
                ->findOrFail($id);
                
            $targetIsoDay = $schedule->day_of_week - 1;
            if ($targetIsoDay == 0) {
                $targetIsoDay = 7;
            }
            $targetDate = $weekStart->copy()->addDays($targetIsoDay - 1);
        }

        $upcomingAppointments = \App\Models\Appointment::with(['patientProfile.user'])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', $targetDate)
            ->whereTime('appointment_time', '>=', $schedule->start_time)
            ->whereTime('appointment_time', '<', $schedule->end_time)
            ->orderBy('appointment_time')
            ->paginate(15);

        // Weekly schedules for side panel
        $weeklySchedules = WorkSchedule::with('room')
            ->where('doctor_profile_id', $doctorProfile->id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $overrides = ScheduleOverride::with('room')
            ->where('doctor_profile_id', $doctorProfile->id)
            ->whereBetween('override_date', [$weekStart, $weekEnd])
            ->get();

        if (!empty($overrides)) {
            foreach ($overrides as $override) {
                $dayOfWeek = Carbon::parse($override->override_date)->dayOfWeekIso + 1;
                if ($dayOfWeek == 8) {
                    $dayOfWeek = 1;
                }

                if (!isset($weeklySchedules[$dayOfWeek])) {
                    $weeklySchedules[$dayOfWeek] = collect([[
                        'id' => $override->id,
                        "doctor_profile_id" => $override->doctor_profile_id,
                        "room_id" => $override->room_id,
                        "day_of_week" => $dayOfWeek,
                        "start_time" => $override->start_time,
                        "end_time" => $override->end_time,
                        "is_active" => true,
                        'is_override' => true,
                        'room' => $override->room
                    ]]);
                    continue;
                }

                // Append to existing
                $weeklySchedules[$dayOfWeek]->push(
                    (object)[
                        'id' => $override->id,
                        "doctor_profile_id" => $override->doctor_profile_id,
                        "room_id" => $override->room_id,
                        "day_of_week" => $dayOfWeek,
                        "start_time" => $override->start_time,
                        "end_time" => $override->end_time,
                        "is_active" => true,
                        'is_override' => true,
                        'room' => $override->room
                    ]
                );
            }
        }

        return view('doctor.work-schedules.show', compact('schedule', 'upcomingAppointments', 'weeklySchedules', 'targetDate', 'isOverride'));
    }
}
