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

    public function show($id)
    {
        $schedule = WorkSchedule::with(['doctor.user', 'room'])->findOrFail($id);

        // dd($schedule->toArray());

        $today = Carbon::today();
        // $today = Carbon::parse('2026-06-28');

        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // dd($weekStart->toDateString(), $weekEnd->toDateString());

        $overrides = ScheduleOverride::with('room')->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereBetween('override_date', [$weekStart, $weekEnd])
            ->get();


        // dd($overrides->toArray());

        // Lấy danh sách lịch hẹn sắp tới thuộc ca trực này
        $upcomingAppointments = \App\Models\Appointment::with(['patientProfile.user'])
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereRaw('DAYOFWEEK(appointment_date) = ?', [$schedule->day_of_week])
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereTime('appointment_time', '>=', $schedule->start_time)
            ->whereTime('appointment_time', '<', $schedule->end_time)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15);

        // Lấy lịch làm việc cả tuần của bác sĩ này
        $weeklySchedules = WorkSchedule::with('room')
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        // dd($weeklySchedules->toArray());

        if (!empty($overrides)) {
            foreach ($overrides as $override) {
                $dayOfWeek = Carbon::parse($override['override_date'])->dayOfWeekIso + 1;

                if ($dayOfWeek == 8) {
                    $dayOfWeek = 1;
                }
                // dd($override->toArray(), $dayOfWeek);

                if (!isset($weeklySchedules[$dayOfWeek])) {
                    $weeklySchedules[$dayOfWeek] = [
                        [
                            'id' => $override->id,
                            "doctor_profile_id" => $override['doctor_profile_id'],
                            "room_id" => $override['room_id'],
                            "day_of_week" => $dayOfWeek,
                            "start_time" => $override['start_time'],
                            "end_time" => $override['end_time'],
                            "slot_duration_minutes" => 15,
                            "max_slots" => 2,
                            "is_active" => true,
                            'is_override' => true,
                            'room' => $override['room']
                        ]
                    ];

                    continue;
                }

                foreach ($weeklySchedules[$dayOfWeek] as $key => $schedule) {
                    // // dd($override->toArray());
                    // dd([
                    //     'override' => $override->start_time,
                    //     'schedule' => $schedule->start_time,
                    //     'equal' => $override->start_time == $schedule->start_time,
                    // ]);

                    if (
                        $override->type == 'close' &&
                        $override->start_time == $schedule->start_time &&
                        $override->end_time == $schedule->end_time
                    ) {
                        unset($weeklySchedules[$dayOfWeek][$key]);
                    } elseif (
                        $override->type === 'extra'
                    ) {
                        $weeklySchedules[$dayOfWeek][] = [
                            'id' => $override->id,
                            "doctor_profile_id" => $override['doctor_profile_id'],
                            "room_id" => $override['room_id'],
                            "day_of_week" => $dayOfWeek,
                            "start_time" => $override['start_time'],
                            "end_time" => $override['end_time'],
                            "slot_duration_minutes" => 15,
                            "max_slots" => 2,
                            "is_active" => true,
                            "is_override" => true,
                            'room' => $override['room']
                        ];
                    }
                }
            }
        }

        // dd($weeklySchedules->toArray());

        // dd($schedule->toArray());

        // dd((int)date('H', strtotime($schedule->start_time)));

        // dd(get_debug_type($minute));
        // Tạo mảng slot giờ khám để hiển thị (tùy chọn)
        $startMin = (int)date('H', strtotime($schedule->start_time)) * 60 + (int)date('i', strtotime($schedule->start_time));
        $endMin = (int)date('H', strtotime($schedule->end_time)) * 60 + (int)date('i', strtotime($schedule->end_time));
        $duration = $schedule->slot_duration_minutes;
        $slotsCount = $duration > 0 ? floor(($endMin - $startMin) / $duration) : 0;

        return view('admin.work-schedules.show', compact('schedule', 'upcomingAppointments', 'slotsCount', 'weeklySchedules'));
    }


    public function showOverride($id)
    {
        // dd('show override', $id);
        $overrideSchedule = ScheduleOverride::with(['doctor.user', 'room'])->findOrFail($id);

        $schedule = WorkSchedule::with(['doctor.user', 'room'])->findOrFail($overrideSchedule->doctor_profile_id);

        dd($overrideSchedule->toArray());

        $today = Carbon::today();
        // $today = Carbon::parse('2026-06-28');

        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        // dd($weekStart->toDateString(), $weekEnd->toDateString());

        $overrides = ScheduleOverride::with('room')->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereBetween('override_date', [$weekStart, $weekEnd])
            ->get();


        // dd($overrides->toArray());

        // Lấy danh sách lịch hẹn sắp tới thuộc ca trực này
        $upcomingAppointments = \App\Models\Appointment::with(['patientProfile.user'])
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereRaw('DAYOFWEEK(appointment_date) = ?', [$overrideSchedule->override_date->dayOfWeekIso + 1])
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereTime('appointment_time', '>=', $overrideSchedule->start_time)
            ->whereTime('appointment_time', '<', $overrideSchedule->end_time)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15);

        // dd($upcomingAppointments->toArray());

        // Lấy lịch làm việc cả tuần của bác sĩ này
        $weeklySchedules = WorkSchedule::with('room')
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        // dd($weeklySchedules->toArray());

        if (!empty($overrides)) {
            foreach ($overrides as $override) {
                $dayOfWeek = Carbon::parse($override['override_date'])->dayOfWeekIso + 1;

                if ($dayOfWeek == 8) {
                    $dayOfWeek = 1;
                }
                // dd($override->toArray(), $dayOfWeek);

                if (!isset($weeklySchedules[$dayOfWeek])) {
                    $weeklySchedules[$dayOfWeek] = [
                        [
                            'id' => $override->id,
                            "doctor_profile_id" => $override['doctor_profile_id'],
                            "room_id" => $override['room_id'],
                            "day_of_week" => $dayOfWeek,
                            "start_time" => $override['start_time'],
                            "end_time" => $override['end_time'],
                            "slot_duration_minutes" => 15,
                            "max_slots" => 2,
                            "is_active" => true,
                            'is_override' => true,
                            'room' => $override['room']
                        ]
                    ];

                    continue;
                }

                foreach ($weeklySchedules[$dayOfWeek] as $key => $schedule) {
                    // // dd($override->toArray());
                    // dd([
                    //     'override' => $override->start_time,
                    //     'schedule' => $schedule->start_time,
                    //     'equal' => $override->start_time == $schedule->start_time,
                    // ]);

                    if (
                        $override->type == 'close' &&
                        $override->start_time == $schedule->start_time &&
                        $override->end_time == $schedule->end_time
                    ) {
                        unset($weeklySchedules[$dayOfWeek][$key]);
                    } elseif (
                        $override->type === 'extra'
                    ) {
                        $weeklySchedules[$dayOfWeek][] = [
                            'id' => $override->id,
                            "doctor_profile_id" => $override['doctor_profile_id'],
                            "room_id" => $override['room_id'],
                            "day_of_week" => $dayOfWeek,
                            "start_time" => $override['start_time'],
                            "end_time" => $override['end_time'],
                            "slot_duration_minutes" => 15,
                            "max_slots" => 2,
                            "is_active" => true,
                            "is_override" => true,
                            'room' => $override['room']
                        ];
                    }
                }
            }
        }

        // dd($weeklySchedules->toArray());

        // dd($schedule->toArray());

        // dd((int)date('H', strtotime($schedule->start_time)));

        // dd(get_debug_type($minute));
        // Tạo mảng slot giờ khám để hiển thị (tùy chọn)
        $startMin = (int)date('H', strtotime($schedule->start_time)) * 60 + (int)date('i', strtotime($schedule->start_time));
        $endMin = (int)date('H', strtotime($schedule->end_time)) * 60 + (int)date('i', strtotime($schedule->end_time));
        $duration = $schedule->slot_duration_minutes;
        $slotsCount = $duration > 0 ? floor(($endMin - $startMin) / $duration) : 0;

        return view('admin.work-schedules.show', compact('schedule', 'upcomingAppointments', 'slotsCount', 'weeklySchedules'));
    }
}
