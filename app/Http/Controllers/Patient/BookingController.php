<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Models\DoctorLevelFee;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService)
    {
    }

    /**
     * Bước 1: Chọn hồ sơ bệnh nhân
     */
    public function step1(Request $request): View
    {
        $user = auth()->user();
        $profiles = PatientProfile::where('owner_id', $user->id)
            ->orderByDesc('is_self')
            ->get();
            
        $draftId = $request->query('draft_id');
        $booking = $draftId ? Cache::get("booking_draft_{$draftId}", []) : [];
        $selectedProfileId = $booking['patient_profile_id'] ?? null;
            
        return view('patient.booking.steps.step1', compact('profiles', 'selectedProfileId', 'draftId'));
    }

    public function postStep1(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id,owner_id,' . auth()->id(),
        ]);
        
        $draftId = $request->input('draft_id') ?: Str::uuid()->toString();
        $booking = Cache::get("booking_draft_{$draftId}", []);
        $booking['patient_profile_id'] = $request->patient_profile_id;
        
        Cache::put("booking_draft_{$draftId}", $booking, now()->addHours(2));
        
        return redirect()->route('patient.booking.step2', ['draft_id' => $draftId]);
    }

    /**
     * Bước 2: Chọn phương thức và chuyên khoa/bác sĩ
     */
    public function step2(Request $request): View|RedirectResponse
    {
        $draftId = $request->query('draft_id');
        $booking = $draftId ? Cache::get("booking_draft_{$draftId}", []) : [];
        
        if (empty($booking['patient_profile_id'])) {
            return redirect()->route('patient.booking.step1', ['draft_id' => $draftId]);
        }

        $specialties = Specialty::where('is_active', true)->orderBy('display_order')->get();
        $doctors = DoctorProfile::with(['user:id,full_name', 'specialties:id,name'])
            ->whereHas('workSchedules', fn($q) => $q->where('is_active', true))
            ->get();
            
        $activeLevels = $doctors->pluck('level')->unique()->toArray();
        $fees = DoctorLevelFee::whereIn('level', $activeLevels)->get();
        
        $specialtyLevels = [];
        foreach ($doctors as $doctor) {
            foreach ($doctor->specialties as $specialty) {
                $specialtyLevels[$specialty->id][] = $doctor->level;
            }
        }
        foreach ($specialtyLevels as $id => $levels) {
            $specialtyLevels[$id] = array_values(array_unique($levels));
        }
        
        $validSpecialtyIds = array_keys($specialtyLevels);
        $specialties = $specialties->filter(fn($s) => in_array($s->id, $validSpecialtyIds))->values();
        
        return view('patient.booking.steps.step2', compact('specialties', 'doctors', 'fees', 'booking', 'specialtyLevels', 'draftId'));
    }

    public function postStep2(Request $request): RedirectResponse
    {
        $request->validate([
            'draft_id' => 'required|string',
            'booking_method' => 'required|in:specialty,doctor,suggested',
            'specialty_id' => 'required_if:booking_method,specialty|nullable|exists:specialties,id',
            'level' => 'required_if:booking_method,specialty|nullable|string',
            'doctor_id' => 'required_if:booking_method,doctor,suggested|nullable|exists:doctor_profiles,id',
        ]);
        
        $draftId = $request->input('draft_id');
        $booking = Cache::get("booking_draft_{$draftId}", []);
        
        $booking['booking_method'] = $request->booking_method;
        
        if ($request->booking_method === 'specialty') {
            $booking['specialty_id'] = $request->specialty_id;
            $booking['level'] = $request->level;
            unset($booking['doctor_id']);
        } else {
            $booking['doctor_id'] = $request->doctor_id;
            unset($booking['specialty_id'], $booking['level']);
            // Tự nạp chuyên khoa từ bác sĩ (nếu có 1)
            $doctor = DoctorProfile::with('specialties')->find($request->doctor_id);
            if ($doctor) {
                $specId = $doctor->primary_specialty_id ?? ($doctor->specialties->first()->id ?? null);
                if ($specId) {
                    $booking['specialty_id'] = $specId;
                }
            }
        }
        
        Cache::put("booking_draft_{$draftId}", $booking, now()->addHours(2));
        
        return redirect()->route('patient.booking.step3', ['draft_id' => $draftId]);
    }

    /**
     * Bước 3: Chọn Ngày và Giờ (Slots)
     */
    public function step3(Request $request): View|RedirectResponse
    {
        $draftId = $request->query('draft_id');
        $booking = $draftId ? Cache::get("booking_draft_{$draftId}", []) : [];
        
        if (empty($booking['patient_profile_id']) || empty($booking['booking_method'])) {
            return redirect()->route('patient.booking.step1', ['draft_id' => $draftId]);
        }

        $doctorId = $booking['doctor_id'] ?? null;
        $specialtyId = $booking['specialty_id'] ?? null;
        $level = $booking['level'] ?? null;
        
        $availableDates = $this->bookingService->getAvailableDates($doctorId, $specialtyId, $level);
        
        $selectedDate = $request->query('date');
        $slots = [];
        
        if ($selectedDate) {
            $slots = $this->bookingService->getSlots($doctorId, $specialtyId, $selectedDate, $level, $draftId);
        }

        return view('patient.booking.steps.step3', compact('availableDates', 'selectedDate', 'slots', 'booking', 'draftId'));
    }

    public function postStep3(Request $request): RedirectResponse
    {
        $request->validate([
            'draft_id' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'doctor_id' => 'nullable|exists:doctor_profiles,id',
        ]);
        
        $draftId = $request->input('draft_id');
        $booking = Cache::get("booking_draft_{$draftId}", []);
        
        $booking['date'] = $request->date;
        $booking['time'] = $request->time;
        
        if ($request->doctor_id) {
            $booking['doctor_id'] = $request->doctor_id;
        }
        
        Cache::put("booking_draft_{$draftId}", $booking, now()->addHours(2));
        
        return redirect()->route('patient.booking.step4', ['draft_id' => $draftId]);
    }

    /**
     * Bước 4: Xác nhận
     */
    public function step4(Request $request): View|RedirectResponse
    {
        $draftId = $request->query('draft_id');
        $booking = $draftId ? Cache::get("booking_draft_{$draftId}", []) : [];
        
        if (empty($booking['date']) || empty($booking['time'])) {
            return redirect()->route('patient.booking.step3', ['draft_id' => $draftId]);
        }
        
        $profile = PatientProfile::find($booking['patient_profile_id']);
        $summary = $this->bookingService->calculateBookingSummary($booking);
        
        // Khóa slot tạm thời (Soft-lock)
        $doctorId = $booking['doctor_id'];
        $date = $booking['date'];
        $time = $booking['time'];
        $lockKey = "slot_lock_{$doctorId}_{$date}_{$time}";
        
        $currentLock = Cache::get($lockKey);
        if ($currentLock && $currentLock !== $draftId) {
            return redirect()->route('patient.booking.step3', ['draft_id' => $draftId])
                ->with('error', 'Slot này đang được người khác giữ chỗ. Vui lòng chọn giờ khác.');
        }
        
        // Giữ chỗ 5 phút
        Cache::put($lockKey, $draftId, now()->addMinutes(5));

        return view('patient.booking.steps.step4', [
            'booking' => $booking,
            'profile' => $profile,
            'doctor' => $summary['doctor'],
            'specialty' => $summary['specialty'],
            'totalFee' => $summary['totalFee'],
            'roomName' => $summary['roomName'],
            'draftId' => $draftId
        ]);
    }

    /**
     * Store (Submit final)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'draft_id' => 'required|string',
            'reason' => 'nullable|string|max:1000',
        ]);
        
        $draftId = $request->input('draft_id');
        $booking = Cache::get("booking_draft_{$draftId}", []);
        
        if (empty($booking['patient_profile_id']) || empty($booking['date']) || empty($booking['time'])) {
            return redirect()->route('patient.booking.step1')->with('error', 'Phiên đặt lịch đã hết hạn, vui lòng thử lại.');
        }
        
        // Kiểm tra Soft-lock lần cuối
        $doctorId = $booking['doctor_id'];
        $date = $booking['date'];
        $time = $booking['time'];
        $lockKey = "slot_lock_{$doctorId}_{$date}_{$time}";
        
        $currentLock = Cache::get($lockKey);
        if ($currentLock && $currentLock !== $draftId) {
            return redirect()->route('patient.booking.step3', ['draft_id' => $draftId])
                ->with('error', 'Slot này đã bị người khác đặt mất. Vui lòng chọn giờ khác.');
        }

        // Tạo mảng data cho StoreBookingRequest
        $data = [
            'patient_profile_id' => $booking['patient_profile_id'],
            'booking_method' => $booking['booking_method'],
            'appointment_date' => $booking['date'],
            'appointment_time' => $booking['time'],
            'reason' => $request->reason,
        ];
        
        if (!empty($booking['doctor_id'])) $data['doctor_profile_id'] = $booking['doctor_id'];
        if (!empty($booking['specialty_id'])) $data['specialty_id'] = $booking['specialty_id'];
        if (!empty($booking['level'])) $data['level'] = $booking['level'];
        
        try {
            $appointment = $this->bookingService->createAppointment($data, auth()->user());
            
            \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'patient_confirmation');
            
            Cache::forget("booking_draft_{$draftId}");
            Cache::forget($lockKey);
            
            return redirect()
                ->route('patient.booking.success', $appointment->id)
                ->with('success', 'Đặt lịch thành công! Mã lịch hẹn: ' . $appointment->appointment_code);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Fast Track cho Đặt lịch thay thế
     */
    public function fastTrack(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'doctor_id' => 'required|exists:doctor_profiles,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'reason' => 'nullable|string',
            'date' => 'nullable|date',
            'time' => 'nullable|string'
        ]);
        
        $draftId = Str::uuid()->toString();
        $booking = [];
        
        $booking['patient_profile_id'] = $request->patient_profile_id;
        $booking['booking_method'] = 'suggested';
        $booking['doctor_id'] = $request->doctor_id;
        
        if ($request->specialty_id) {
            $booking['specialty_id'] = $request->specialty_id;
        }
        
        // Nếu truyền sẵn date và time (thay thế vào cùng slot)
        if ($request->date && $request->time) {
            $booking['date'] = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
            $booking['time'] = substr($request->time, 0, 5);
            Cache::put("booking_draft_{$draftId}", $booking, now()->addHours(2));
            return redirect()->route('patient.booking.step4', ['draft_id' => $draftId]);
        }

        Cache::put("booking_draft_{$draftId}", $booking, now()->addHours(2));
        return redirect()->route('patient.booking.step3', ['draft_id' => $draftId])
            ->with('success', 'Đã nạp thông tin đặt lịch thay thế, vui lòng chọn ngày giờ mới.');
    }
    
    public function success(int $id): View
    {
        $appointment = \App\Models\Appointment::with([
            'patientProfile',
            'doctorProfile.user',
            'specialty',
            'room',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->findOrFail($id);

        return view('patient.booking.success', compact('appointment'));
    }
}
