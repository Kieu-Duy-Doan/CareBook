<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreBookingRequest;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Specialty;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService)
    {
    }

    /**
     * GET /dat-lich
     * Render SPA booking page với dữ liệu cần thiết.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Hồ sơ bệnh nhân của user (eager load)
        $profiles = PatientProfile::where('owner_id', $user->id)
            ->orderByDesc('is_self')
            ->get(['id', 'full_name', 'date_of_birth', 'phone', 'gender', 'is_self', 'relationship']);

        // Chuyên khoa đang hoạt động
        $specialties = Specialty::where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'name', 'description', 'image_url']);

        // Bác sĩ: eager load user + specialties
        $doctors = DoctorProfile::with([
            'user:id,full_name',
            'specialties:id,name',
        ])
        ->whereHas('workSchedules', fn($q) => $q->where('is_active', true))
        ->get()
        ->map(fn($d) => [
            'id'                  => $d->id,
            'full_title'          => $d->full_title,
            'level_label'         => $d->level_label,
            'primary_specialty'   => $d->primary_specialty?->name,
            'primary_specialty_id'=> $d->primary_specialty?->id,
            'room_name'           => null, // sẽ được resolve khi chọn ngày
        ]);

        // Xử lý thông báo huỷ lịch (nếu có)
        $suggestedDoctors = [];
        if ($request->has('notification_id')) {
            $notification = \App\Models\Notification::where('id', $request->notification_id)
                ->where('user_id', $user->id)
                ->first();
                
            if ($notification && !empty($notification->data['alternatives'])) {
                $suggestedDoctors = $notification->data['alternatives'];
            }
        }

        // Gán user's patientProfiles cho blade template
        $user->setRelation('patientProfiles', $profiles);

        return view('patient.booking.index', compact('specialties', 'doctors', 'suggestedDoctors'));
    }

    /**
     * GET /dat-lich/ngay-kha-dung
     * API: Trả về 14 ngày tới có lịch khám.
     *
     * @query doctor_id    int (optional)
     * @query specialty_id int (optional)
     */
    public function availableDates(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id'    => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
        ]);

        $dates = $this->bookingService->getAvailableDates(
            $request->integer('doctor_id', null) ?: null,
            $request->integer('specialty_id', null) ?: null,
        );

        return response()->json(['dates' => $dates]);
    }

    /**
     * GET /dat-lich/slots
     * API: Trả về danh sách slot giờ khám theo ngày.
     *
     * @query doctor_id    int (optional)
     * @query specialty_id int (optional)
     * @query date         string Y-m-d (required)
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'date'         => ['required', 'date', 'after_or_equal:today'],
            'doctor_id'    => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
        ]);

        $slots = $this->bookingService->getSlots(
            $request->integer('doctor_id', null) ?: null,
            $request->integer('specialty_id', null) ?: null,
            $request->string('date'),
        );

        return response()->json(['slots' => $slots]);
    }

    /**
     * POST /dat-lich
     * Lưu lịch hẹn mới.
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        try {
            $appointment = $this->bookingService->createAppointment(
                $request->validated(),
                auth()->user()
            );

            \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'confirmation');

            return redirect()
                ->route('patient.booking.success', $appointment->id)
                ->with('success', 'Đặt lịch thành công! Mã lịch hẹn: ' . $appointment->appointment_code);

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * GET /dat-lich/thanh-cong/{id}
     * Trang xác nhận sau khi đặt lịch thành công.
     */
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
