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
use App\Exports\WorkSchedulesExport;
use App\Exports\WorkSchedulesTemplateExport;
use App\Imports\WorkSchedulesImport;
use Maatwebsite\Excel\Facades\Excel;

class WorkScheduleController extends Controller
{
    public function export(Request $request)
    {
        return Excel::download(new WorkSchedulesExport($request), 'danh-sach-lich-lam-viec.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new WorkSchedulesTemplateExport(), 'mau-import-lich-lam-viec.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'Vui lòng chọn file.',
            'file.mimes' => 'File phải có định dạng xlsx, xls hoặc csv.',
            'file.max' => 'File không được vượt quá 2MB.',
        ]);

        try {
            Excel::import(new WorkSchedulesImport, $request->file('file'));

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'WORK_SCHEDULE_IMPORTED',
                'module' => 'work_schedule',
                'description' => 'Import danh sách lịch làm việc từ file',
                'ip_address' => request()->ip()
            ]);

            return back()->with('success', 'Import danh sách lịch làm việc thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi Import: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $doctors = DoctorProfile::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        $query = WorkSchedule::with(['doctor.user', 'room'])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

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

        $isValidTime = ($request->start_time === '07:00' && $request->end_time === '11:00') ||
            ($request->start_time === '13:00' && $request->end_time === '17:00');

        if (!$isValidTime) {
            return back()->with('error', 'Thời gian ca trực chỉ được phép là Sáng (07:00 - 11:00) hoặc Chiều (13:00 - 17:00).')->withInput();
        }

        // Kiểm tra bác sĩ được chọn đã có ca làm việc trùng thời gian vào thứ đã chọn chưa

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

        // Kiểm tra lịch đăng ký có trùng với lịch hay phòng bác sĩ khác không
        $existsTimeAndRoomWithOthers = WorkSchedule::where('day_of_week', $request->day_of_week)
            ->where('is_active', true)
            ->where('room_id', $request->room_id)
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                    ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($existsTimeAndRoomWithOthers) {
            return back()->with('error', 'Lịch này đã có bác sĩ khác làm việc.');
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

        $today = Carbon::today();

        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $overrides = ScheduleOverride::with('room')->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereBetween('override_date', [$weekStart, $weekEnd])
            ->get();

        $targetIsoDay = $schedule->day_of_week - 1;
        if ($targetIsoDay == 0) {
            $targetIsoDay = 7;
        }
        $targetDate = $weekStart->copy()->addDays($targetIsoDay - 1);

        // Lấy danh sách lịch hẹn trong tuần này thuộc ca trực này
        $upcomingAppointments = \App\Models\Appointment::with(['patientProfile.user'])
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereDate('appointment_date', $targetDate)
            ->whereTime('appointment_time', '>=', $schedule->start_time)
            ->whereTime('appointment_time', '<', $schedule->end_time)
            ->orderBy('appointment_time')
            ->paginate(15);

        // Lấy lịch làm việc cả tuần của bác sĩ này
        $weeklySchedules = WorkSchedule::with('room')
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        if (!empty($overrides)) {
            foreach ($overrides as $override) {
                $dayOfWeek = Carbon::parse($override['override_date'])->dayOfWeekIso + 1;

                if ($dayOfWeek == 8) {
                    $dayOfWeek = 1;
                }

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

        // Tạo mảng slot giờ khám để hiển thị (tùy chọn)
        $startMin = (int)date('H', strtotime($schedule->start_time)) * 60 + (int)date('i', strtotime($schedule->start_time));
        $endMin = (int)date('H', strtotime($schedule->end_time)) * 60 + (int)date('i', strtotime($schedule->end_time));
        $duration = $schedule->slot_duration_minutes;
        $slotsCount = $duration > 0 ? floor(($endMin - $startMin) / $duration) : 0;

        return view('admin.work-schedules.show', compact('schedule', 'upcomingAppointments', 'slotsCount', 'weeklySchedules'));
    }


    public function showOverride($id)
    {
        $overrideSchedule = ScheduleOverride::with(['doctor.user', 'room'])->findOrFail($id);

        $dayOfWeek = Carbon::parse($overrideSchedule->override_date)->dayOfWeekIso + 1;
        if ($dayOfWeek == 8) {
            $dayOfWeek = 1;
        }

        // Tạo mock schedule để hiển thị trên view
        $schedule = new WorkSchedule([
            'id' => $overrideSchedule->id,
            'doctor_profile_id' => $overrideSchedule->doctor_profile_id,
            'room_id' => $overrideSchedule->room_id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $overrideSchedule->start_time,
            'end_time' => $overrideSchedule->end_time,
            'slot_duration_minutes' => 15,
            'is_active' => true,
        ]);

        $schedule->setRelation('doctor', $overrideSchedule->doctor);
        $schedule->setRelation('room', $overrideSchedule->room);

        $startMin = (int)date('H', strtotime($schedule->start_time)) * 60 + (int)date('i', strtotime($schedule->start_time));
        $endMin = (int)date('H', strtotime($schedule->end_time)) * 60 + (int)date('i', strtotime($schedule->end_time));
        $duration = $schedule->slot_duration_minutes;
        $slotsCount = $duration > 0 ? floor(($endMin - $startMin) / $duration) : 0;
        $schedule->max_slots = $slotsCount;

        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $overrides = ScheduleOverride::with('room')->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereBetween('override_date', [$weekStart, $weekEnd])
            ->get();

        // Lấy danh sách lịch hẹn sắp tới thuộc ca ngoại lệ này
        $upcomingAppointments = \App\Models\Appointment::with(['patientProfile.user'])
            ->where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereDate('appointment_date', $overrideSchedule->override_date)
            ->whereTime('appointment_time', '>=', $overrideSchedule->start_time)
            ->whereTime('appointment_time', '<', $overrideSchedule->end_time)
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

        if (!empty($overrides)) {
            foreach ($overrides as $override) {
                $ovDayOfWeek = Carbon::parse($override['override_date'])->dayOfWeekIso + 1;
                if ($ovDayOfWeek == 8) {
                    $ovDayOfWeek = 1;
                }

                if (!isset($weeklySchedules[$ovDayOfWeek])) {
                    $weeklySchedules[$ovDayOfWeek] = [
                        [
                            'id' => $override->id,
                            "doctor_profile_id" => $override['doctor_profile_id'],
                            "room_id" => $override['room_id'],
                            "day_of_week" => $ovDayOfWeek,
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

                foreach ($weeklySchedules[$ovDayOfWeek] as $key => $ws) {
                    if (
                        $override->type == 'close' &&
                        $override->start_time == $ws->start_time &&
                        $override->end_time == $ws->end_time
                    ) {
                        unset($weeklySchedules[$ovDayOfWeek][$key]);
                    } elseif ($override->type === 'extra') {
                        $weeklySchedules[$ovDayOfWeek][] = [
                            'id' => $override->id,
                            "doctor_profile_id" => $override['doctor_profile_id'],
                            "room_id" => $override['room_id'],
                            "day_of_week" => $ovDayOfWeek,
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

        $isOverride = true;

        return view('admin.work-schedules.show', compact('schedule', 'upcomingAppointments', 'slotsCount', 'weeklySchedules', 'isOverride'));
    }

    public function update(Request $request, $id)
    {
        $schedule = WorkSchedule::findOrFail($id);

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

        $isValidTime = ($request->start_time === '07:00' && $request->end_time === '11:00') ||
            ($request->start_time === '13:00' && $request->end_time === '17:00');

        if (!$isValidTime) {
            return back()->with('error', 'Thời gian ca trực chỉ được phép là Sáng (07:00 - 11:00) hoặc Chiều (13:00 - 17:00).')->withInput();
        }

        // Kiểm tra bác sĩ được chọn đã có ca làm việc trùng thời gian vào thứ đã chọn chưa
        $existsTime = WorkSchedule::where('doctor_profile_id', $request->doctor_profile_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                    ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($existsTime) {
            return back()->with('error', 'Bác sĩ đã có lịch làm việc trùng thời gian.');
        }

        // Kiểm tra lịch đăng ký có trùng với lịch hay phòng bác sĩ khác không
        $existsTimeAndRoomWithOthers = WorkSchedule::where('day_of_week', $request->day_of_week)
            ->where('is_active', true)
            ->where('room_id', $request->room_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                    ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($existsTimeAndRoomWithOthers) {
            return back()->with('error', 'Lịch này đã có bác sĩ khác làm việc.');
        }

        $schedule->update([
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
            'action' => 'WORK_SCHEDULE_UPDATED',
            'module' => 'work_schedule',
            'ref_type' => 'work_schedule',
            'ref_id' => $schedule->id,
            'description' => 'Cập nhật ca trực cho bác sĩ ID ' . $schedule->doctor_profile_id,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã cập nhật ca trực thành công.');
    }

    public function toggleActive($id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        $schedule->is_active = !$schedule->is_active;
        $schedule->save();

        return back()->with('success', 'Đã thay đổi trạng thái ca trực.');
    }

    public function destroy($id)
    {
        $schedule = WorkSchedule::findOrFail($id);

        $hasActiveAppointments = \App\Models\Appointment::where('doctor_profile_id', $schedule->doctor_profile_id)
            ->whereIn('status', ['pending', 'checked_in'])
            ->whereRaw('DAYOFWEEK(appointment_date) = ?', [($schedule->day_of_week % 7) + 1])
            ->where('appointment_date', '>=', now()->toDateString())
            ->exists();

        if ($hasActiveAppointments) {
            session()->flash('warning', 'Ca trực đã được xoá nhưng bác sĩ này đang có lịch hẹn chờ khám vào thứ tương ứng. Hãy kiểm tra lại lịch hẹn.');
        }

        $schedule->delete();

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'WORK_SCHEDULE_DELETED',
            'module' => 'work_schedule',
            'ref_type' => 'work_schedule',
            'ref_id' => $id,
            'description' => 'Xoá ca trực',
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Đã xoá ca trực thành công.');
    }

    public function storeOverride(Request $request)
    {
        try {
            $request->validate([
                'doctor_profile_id' => 'required|exists:doctor_profiles,id',
                'room_id' => 'required|exists:rooms,id',
                'override_date' => 'required|date|after_or_equal:today',
                'type' => 'required|in:close,extra',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'reason' => 'nullable|string|max:255'
            ], [
                'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
                'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
            ]);

            $isValidTime = ($request->start_time === '07:00' && $request->end_time === '11:00') ||
                ($request->start_time === '13:00' && $request->end_time === '17:00');

            if (!$isValidTime) {
                return back()->with('error', 'Thời gian ca trực chỉ được phép là Sáng (07:00 - 11:00) hoặc Chiều (13:00 - 17:00).')->withInput();
            }

            $dayOfWeek = Carbon::parse($request->override_date)->dayOfWeekIso + 1;
            if ($dayOfWeek == 8) {
                $dayOfWeek = 1;
            }

            $overlapQuery = WorkSchedule::where('doctor_profile_id', $request->doctor_profile_id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where(function ($query) use ($request) {
                    $query->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });

            if ($request->filled('room_id')) {
                $overlapQuery->where('room_id', $request->room_id);
            }

            $existsTime = $overlapQuery->exists();

            if ($request->type === 'extra') {
                if ($existsTime) {
                    return back()->with('error', 'Không thể thêm ca. Bác sĩ đã có lịch làm việc trùng thời gian này.');
                }

                if ($request->filled('room_id')) {
                    $existsWithOtherDoctors = WorkSchedule::where('day_of_week', $dayOfWeek)
                        ->where('is_active', true)
                        ->where('room_id', $request->room_id)
                        ->where('doctor_profile_id', '!=', $request->doctor_profile_id)
                        ->where(function ($query) use ($request) {
                            $query->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>', $request->start_time);
                        })
                        ->exists();

                    if ($existsWithOtherDoctors) {
                        return back()->with('error', 'Không thể thêm ca. Phòng này đã có bác sĩ khác làm việc theo lịch định kỳ.');
                    }

                    $existsOverrideWithOtherDoctors = ScheduleOverride::where('override_date', $request->override_date)
                        ->where('type', 'extra')
                        ->where('room_id', $request->room_id)
                        ->where('doctor_profile_id', '!=', $request->doctor_profile_id)
                        ->where(function ($query) use ($request) {
                            $query->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>', $request->start_time);
                        })
                        ->exists();

                    if ($existsOverrideWithOtherDoctors) {
                        return back()->with('error', 'Không thể thêm ca. Phòng này đã có bác sĩ khác đăng ký ca ngoại lệ.');
                    }
                }
            }

            if ($request->type === 'close' && !$existsTime) {
                return back()->with('error', 'Không thể đóng ca. Không có lịch làm việc nào trùng thời gian này để đóng.');
            }

            $override = ScheduleOverride::create([
                'doctor_profile_id' => $request->doctor_profile_id,
                'room_id' => $request->room_id,
                'override_date' => $request->override_date,
                'type' => $request->type,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'reason' => $request->reason,
                'created_by' => Auth::id(),
            ]);

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'SCHEDULE_OVERRIDE_CREATED',
                'module' => 'schedule_override',
                'ref_type' => 'schedule_override',
                'ref_id' => $override->id,
                'description' => 'Thêm ngoại lệ lịch ' . $override->type,
                'ip_address' => request()->ip()
            ]);

            return back()->with('success', 'Đã thêm ngoại lệ lịch thành công.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroyOverride($id)
    {
        $override = ScheduleOverride::findOrFail($id);
        $override->delete();

        return back()->with('success', 'Đã xoá ngoại lệ lịch thành công.');
    }
}
