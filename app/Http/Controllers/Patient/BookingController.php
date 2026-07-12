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
            
        $selectedProfileId = session('booking.patient_profile_id');
            
        return view('patient.booking.steps.step1', compact('profiles', 'selectedProfileId'));
    }

    public function postStep1(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id,owner_id,' . auth()->id(),
        ]);
        
        session()->put('booking.patient_profile_id', $request->patient_profile_id);
        
        return redirect()->route('patient.booking.step2');
    }

    /**
     * Bước 2: Chọn phương thức và chuyên khoa/bác sĩ
     */
    public function step2(Request $request): View|RedirectResponse
    {
        if (!session()->has('booking.patient_profile_id')) {
            return redirect()->route('patient.booking.step1');
        }

        $specialties = Specialty::where('is_active', true)->orderBy('display_order')->get();
        $doctors = DoctorProfile::with(['user:id,full_name', 'specialties:id,name'])
            ->whereHas('workSchedules', fn($q) => $q->where('is_active', true))
            ->get();
            
        $fees = DoctorLevelFee::all();
        
        $booking = session('booking', []);
        
        return view('patient.booking.steps.step2', compact('specialties', 'doctors', 'fees', 'booking'));
    }

    public function postStep2(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_method' => 'required|in:specialty,doctor,suggested',
            'specialty_id' => 'required_if:booking_method,specialty|nullable|exists:specialties,id',
            'level' => 'required_if:booking_method,specialty|nullable|string',
            'doctor_id' => 'required_if:booking_method,doctor,suggested|nullable|exists:doctor_profiles,id',
        ]);
        
        session()->put('booking.booking_method', $request->booking_method);
        
        if ($request->booking_method === 'specialty') {
            session()->put('booking.specialty_id', $request->specialty_id);
            session()->put('booking.level', $request->level);
            session()->forget('booking.doctor_id');
        } else {
            session()->put('booking.doctor_id', $request->doctor_id);
            session()->forget(['booking.specialty_id', 'booking.level']);
            // Tự nạp chuyên khoa từ bác sĩ (nếu có 1)
            $doctor = DoctorProfile::find($request->doctor_id);
            if ($doctor && $doctor->primary_specialty_id) {
                session()->put('booking.specialty_id', $doctor->primary_specialty_id);
            }
        }
        
        return redirect()->route('patient.booking.step3');
    }

    /**
     * Bước 3: Chọn Ngày và Giờ (Slots)
     */
    public function step3(Request $request): View|RedirectResponse
    {
        $booking = session('booking', []);
        if (empty($booking['patient_profile_id']) || empty($booking['booking_method'])) {
            return redirect()->route('patient.booking.step1');
        }

        $doctorId = $booking['doctor_id'] ?? null;
        $specialtyId = $booking['specialty_id'] ?? null;
        $level = $booking['level'] ?? null;
        
        $availableDates = $this->bookingService->getAvailableDates($doctorId, $specialtyId, $level);
        
        $selectedDate = $request->query('date');
        $slots = [];
        
        if ($selectedDate) {
            $slots = $this->bookingService->getSlots($doctorId, $specialtyId, $selectedDate, $level);
        }

        return view('patient.booking.steps.step3', compact('availableDates', 'selectedDate', 'slots', 'booking'));
    }

    public function postStep3(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);
        
        session()->put('booking.date', $request->date);
        session()->put('booking.time', $request->time);
        
        return redirect()->route('patient.booking.step4');
    }

    /**
     * Bước 4: Xác nhận
     */
    public function step4(Request $request): View|RedirectResponse
    {
        $booking = session('booking', []);
        if (empty($booking['date']) || empty($booking['time'])) {
            return redirect()->route('patient.booking.step3');
        }
        
        $profile = PatientProfile::find($booking['patient_profile_id']);
        $doctor = isset($booking['doctor_id']) ? DoctorProfile::with('user')->find($booking['doctor_id']) : null;
        $specialty = isset($booking['specialty_id']) ? Specialty::find($booking['specialty_id']) : null;
        
        // Tính tiền
        $totalFee = 0;
        $level = null;
        if ($booking['booking_method'] === 'specialty' || $booking['booking_method'] === 'suggested') {
            $level = $booking['level'] ?? ($doctor ? $doctor->level : null);
            $fee = DoctorLevelFee::where('level', $level)->first();
            $totalFee = $fee ? $fee->base_price : 0;
        } elseif ($booking['booking_method'] === 'doctor') {
            $level = $doctor ? $doctor->level : null;
            $fee = DoctorLevelFee::where('level', $level)->first();
            $totalFee = $fee ? $fee->specific_price : 0;
        }

        return view('patient.booking.steps.step4', compact('booking', 'profile', 'doctor', 'specialty', 'totalFee'));
    }

    /**
     * Store (Submit final)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);
        
        $booking = session('booking', []);
        
        if (empty($booking['patient_profile_id']) || empty($booking['date']) || empty($booking['time'])) {
            return redirect()->route('patient.booking.step1')->with('error', 'Phiên đặt lịch đã hết hạn, vui lòng thử lại.');
        }

        // Tạo mảng data cho StoreBookingRequest
        $data = [
            'patient_profile_id' => $booking['patient_profile_id'],
            'booking_method' => $booking['booking_method'],
            'appointment_date' => $booking['date'],
            'appointment_time' => $booking['time'],
            'reason' => $request->reason,
        ];
        
        if (!empty($booking['doctor_id'])) $data['doctor_id'] = $booking['doctor_id'];
        if (!empty($booking['specialty_id'])) $data['specialty_id'] = $booking['specialty_id'];
        if (!empty($booking['level'])) $data['level'] = $booking['level'];
        
        try {
            $appointment = $this->bookingService->createAppointment($data, auth()->user());
            
            \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'patient_confirmation');
            
            session()->forget('booking');
            
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
            'reason' => 'nullable|string'
        ]);
        
        session()->put('booking.patient_profile_id', $request->patient_profile_id);
        session()->put('booking.booking_method', 'suggested');
        session()->put('booking.doctor_id', $request->doctor_id);
        
        if ($request->specialty_id) {
            session()->put('booking.specialty_id', $request->specialty_id);
        }
        
        // Nếu truyền sẵn date và time (thay thế vào cùng slot)
        if ($request->date && $request->time) {
            session()->put('booking.date', $request->date);
            session()->put('booking.time', $request->time);
            return redirect()->route('patient.booking.step4');
        }

        return redirect()->route('patient.booking.step3')->with('success', 'Đã nạp thông tin đặt lịch thay thế, vui lòng chọn ngày giờ mới.');
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
