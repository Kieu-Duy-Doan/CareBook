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
use Illuminate\Support\Facades\DB;
use App\Exports\WorkSchedulesExport;
use App\Exports\WorkSchedulesTemplateExport;
use App\Imports\WorkSchedulesImport;
use App\Models\User;
use App\Services\WorkScheduleService;
use App\Http\Requests\Admin\StoreWorkScheduleRequest;
use App\Http\Requests\Admin\UpdateWorkScheduleRequest;
use App\Http\Requests\Admin\StoreScheduleOverrideRequest;
use App\Http\Requests\Admin\TransferDoctorSchedulesRequest;

class WorkScheduleController extends Controller
{
    protected WorkScheduleService $workScheduleService;

    public function __construct(WorkScheduleService $workScheduleService)
    {
        $this->workScheduleService = $workScheduleService;
    }
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

    public function store(StoreWorkScheduleRequest $request)
    {
        $validated = $request->validated();
        
        $this->workScheduleService->createSchedule($validated, Auth::id());

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
                            'override_type' => $override->type,
                            'room' => $override['room']
                        ]
                    ];

                    continue;
                }

                foreach ($weeklySchedules[$dayOfWeek] as $key => $schedule) {
                    if (
                        $override->type == 'close' &&
                        substr($override->start_time, 0, 5) == substr(data_get($schedule, 'start_time'), 0, 5) &&
                        substr($override->end_time, 0, 5) == substr(data_get($schedule, 'end_time'), 0, 5)
                    ) {
                        if (is_object($schedule)) {
                            $schedule->is_override = true;
                            $schedule->override_type = 'close';
                        } else {
                            $weeklySchedules[$dayOfWeek][$key]['is_override'] = true;
                            $weeklySchedules[$dayOfWeek][$key]['override_type'] = 'close';
                        }
                    }
                }

                if ($override->type === 'extra') {
                    // Đảm bảo không bị add nhiều lần nếu vòng lặp chạy nhiều lần
                    $alreadyAdded = false;
                    foreach ($weeklySchedules[$dayOfWeek] as $ws) {
                        if (data_get($ws, 'is_override') && data_get($ws, 'override_type') === 'extra' && data_get($ws, 'start_time') === $override->start_time && data_get($ws, 'end_time') === $override->end_time) {
                            $alreadyAdded = true;
                            break;
                        }
                    }

                    if (!$alreadyAdded) {
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
                            "override_type" => 'extra',
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
                            'override_type' => $override->type,
                            'room' => $override['room']
                        ]
                    ];
                    continue;
                }

                foreach ($weeklySchedules[$ovDayOfWeek] as $key => $ws) {
                    if (
                        $override->type == 'close' &&
                        substr($override->start_time, 0, 5) == substr(data_get($ws, 'start_time'), 0, 5) &&
                        substr($override->end_time, 0, 5) == substr(data_get($ws, 'end_time'), 0, 5)
                    ) {
                        if (is_object($ws)) {
                            $ws->is_override = true;
                            $ws->override_type = 'close';
                        } else {
                            $weeklySchedules[$ovDayOfWeek][$key]['is_override'] = true;
                            $weeklySchedules[$ovDayOfWeek][$key]['override_type'] = 'close';
                        }
                    }
                }

                if ($override->type === 'extra') {
                    $alreadyAdded = false;
                    foreach ($weeklySchedules[$ovDayOfWeek] as $ws_check) {
                        if (data_get($ws_check, 'is_override') && data_get($ws_check, 'override_type') === 'extra' && data_get($ws_check, 'start_time') === $override->start_time && data_get($ws_check, 'end_time') === $override->end_time) {
                            $alreadyAdded = true;
                            break;
                        }
                    }

                    if (!$alreadyAdded) {
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
                            "override_type" => 'extra',
                            'room' => $override['room']
                        ];
                    }
                }
            }
        }

        $isOverride = true;

        return view('admin.work-schedules.show', compact('schedule', 'upcomingAppointments', 'slotsCount', 'weeklySchedules', 'isOverride'));
    }

    public function update(UpdateWorkScheduleRequest $request, $id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        
        $validated = $request->validated();
        
        $this->workScheduleService->updateSchedule($schedule, $validated, Auth::id());

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

    public function storeOverride(StoreScheduleOverrideRequest $request)
    {
        $validated = $request->validated();
        
        $this->workScheduleService->createOverride($validated, Auth::id());

        return back()->with('success', 'Đã thêm ngoại lệ lịch thành công.');
    }

    public function destroyOverride($id)
    {
        $override = ScheduleOverride::findOrFail($id);
        $override->delete();

        return back()->with('success', 'Đã xoá ngoại lệ lịch thành công.');
    }

    public function transferDoctorSchedules(TransferDoctorSchedulesRequest $request)
    {
        $validated = $request->validated();
        
        try {
            $this->workScheduleService->transferSchedules($validated, Auth::id());
            return back()->with('success', 'Chuyển đổi bác sĩ thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
