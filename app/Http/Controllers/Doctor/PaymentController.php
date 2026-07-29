<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\SePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected SePayService $sePayService;

    public function __construct(PaymentService $paymentService, SePayService $sePayService)
    {
        $this->paymentService = $paymentService;
        $this->sePayService = $sePayService;
    }

    /**
     * Danh sách hóa đơn cần thu & lịch sử thanh toán của bác sĩ
     */
    public function index(Request $request)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;
        $userId = Auth::id();

        // Xóa hiển thị màn hình phụ khi bác sĩ quay về danh sách
        Cache::forget('doctor_active_checkout_appointment_' . $userId);

        $tab = $request->input('tab', 'pending');
        
        $dateRange = $request->input('date_range', 'today'); // 'today', 'this_month', 'this_year', 'custom'
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $queryStart = null;
        $queryEnd = null;

        if ($dateRange === 'today') {
            $queryStart = Carbon::today();
            $queryEnd = Carbon::today()->endOfDay();
        } elseif ($dateRange === 'this_month') {
            $queryStart = Carbon::now()->startOfMonth();
            $queryEnd = Carbon::now()->endOfMonth();
        } elseif ($dateRange === 'this_year') {
            $queryStart = Carbon::now()->startOfYear();
            $queryEnd = Carbon::now()->endOfYear();
        } elseif ($dateRange === 'custom') {
            if ($fromDate) {
                $queryStart = Carbon::parse($fromDate)->startOfDay();
            }
            if ($toDate) {
                $queryEnd = Carbon::parse($toDate)->endOfDay();
            }
        }

        // --- STATS CALCULATION ---
        $paymentQuery = Payment::where('status', 'completed')
            ->where('collected_by', $userId);

        if ($queryStart) {
            $paymentQuery->where('paid_at', '>=', $queryStart);
        }
        if ($queryEnd) {
            $paymentQuery->where('paid_at', '<=', $queryEnd);
        }

        $totalCollected = (clone $paymentQuery)->sum('amount');
        $qrCollected = (clone $paymentQuery)->where('method', 'qr')->sum('amount');
        
        $clinicalVisitsQuery = \App\Models\ClinicalVisit::with(['appointment.patientProfile'])
            ->whereHas('payments', function ($q) use ($userId, $queryStart, $queryEnd) {
                $q->where('status', 'completed')
                  ->where('collected_by', $userId);
                  
                if ($queryStart) {
                    $q->where('paid_at', '>=', $queryStart);
                }
                if ($queryEnd) {
                    $q->where('paid_at', '<=', $queryEnd);
                }
            });

        $paidVisits = $clinicalVisitsQuery->get();
        $insuranceCovered = 0;
        
        $appointmentsForStats = $paidVisits->pluck('appointment')->unique('id');
        foreach ($appointmentsForStats as $apt) {
            $summary = $this->paymentService->calculateSummary($apt);
            $rate = $summary['insurance_rate'];
            
            $visitsOfApt = $paidVisits->where('appointment_id', $apt->id);
            $totalAmt = $visitsOfApt->sum('payment_amount');
            $insuranceCovered += $totalAmt * $rate;
        }

        // --- APPOINTMENTS QUERY ---
        $query = Appointment::with([
            'patientProfile',
            'clinicalVisits',
            'payments'
        ]);

        if ($tab === 'pending') {
            $query->where(function ($q) use ($doctorProfileId) {
                // Ca khám do bác sĩ này chỉ định
                $q->where('doctor_profile_id', $doctorProfileId)
                  ->whereHas('clinicalVisits', function ($q2) {
                      $q2->where('payment_status', 'pending');
                  });
            })->orWhere(function ($q) use ($doctorProfileId) {
                // Hoặc ca khám do bác sĩ này thực hiện (cận lâm sàng)
                $q->whereHas('clinicalVisits', function ($q2) use ($doctorProfileId) {
                    $q2->where('doctor_profile_id', $doctorProfileId)
                       ->where('payment_status', 'pending');
                });
            });
            
            if ($queryStart) {
                $query->where('appointment_date', '>=', $queryStart->toDateString());
            }
            if ($queryEnd) {
                $query->where('appointment_date', '<=', $queryEnd->toDateString());
            }

        } else {
            // Lịch sử (các lịch khám đã thanh toán hết)
            $query->where(function ($q) use ($doctorProfileId) {
                $q->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q2) use ($doctorProfileId) {
                      $q2->where('doctor_profile_id', $doctorProfileId);
                  });
            })->whereDoesntHave('clinicalVisits', function ($q2) {
                $q2->where('payment_status', 'pending');
            })->whereHas('payments', function ($q2) use ($userId) {
                $q2->where('collected_by', $userId);
            });
            
            if ($queryStart) {
                // Dựa trên thời gian thanh toán của các clinical visits
                $query->whereHas('clinicalVisits', function($q2) use ($queryStart, $queryEnd) {
                    if ($queryStart) $q2->where('paid_at', '>=', $queryStart);
                    if ($queryEnd) $q2->where('paid_at', '<=', $queryEnd);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', fn($q2) =>
                      $q2->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%")
                  );
            });
        }

        $appointments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('doctor.payments.index', compact(
            'appointments', 'tab', 'totalCollected', 'qrCollected', 'insuranceCovered', 
            'dateRange', 'fromDate', 'toDate'
        ));
    }

    /**
     * Chi tiết thanh toán & In ấn
     */
    public function show(string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        // Xóa hiển thị màn hình phụ
        Cache::forget('doctor_active_checkout_appointment_' . Auth::id());

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
            'payments.collectedBy',
            'medicalRecord.prescription',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        return view('doctor.payments.show', compact('appointment', 'summary'));
    }

    /**
     * Hiển thị QR Code thanh toán tại phòng bác sĩ
     */
    public function checkout(Request $request, string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        $appointment = Appointment::with([
            'patientProfile',
            'medicalRecord.prescription'
        ])
        ->where('id', $id)
        ->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })
        ->firstOrFail();

        $summary = $this->paymentService->calculateSummary($appointment);

        // Mượn logic cache intent của lễ tân nhưng dùng key riêng cho bác sĩ
        $doctorId = Auth::id();
        $timeCacheKey = 'doctor_active_checkout_time_' . $doctorId;
        $intentCacheKeySession = 'doctor_active_checkout_intent_' . $doctorId;
        $appointmentCacheKey = 'doctor_active_checkout_appointment_' . $doctorId;

        $startTime = Cache::get($timeCacheKey);

        // Nếu chuyển sang bệnh nhân khác hoặc có request renew = 1, thì reset lại timer và sinh mã mới
        $currentCachedAppointment = Cache::get($appointmentCacheKey);

        if (!$startTime || $request->has('renew') || $currentCachedAppointment != $id) {
            $startTime = time();
            Cache::put($timeCacheKey, $startTime, now()->addMinutes(60));

            // Sinh mã Intent Code mới (dùng một lần)
            $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
            Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));

            // Lưu vào global cache cho Webhook - TTL 10 phút
            Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
            Cache::put('qr_intent_' . $intentCode . '_user', Auth::id(), now()->addMinutes(10));
        } else {
            // Lấy lại mã intent đang dùng dở
            $intentCode = Cache::get($intentCacheKeySession);

            if (!$intentCode) {
                // Phòng hờ: intent session cache bị mất, sinh mã mới
                $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
                Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));
            }

            // Refresh TTL
            Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
            Cache::put('qr_intent_' . $intentCode . '_user', Auth::id(), now()->addMinutes(10));
        }

        $qrUrl = null;
        if ($summary['remaining_to_pay'] > 0) {
            $qrUrl = $this->sePayService->generateVietQrUrl($appointment, $summary['remaining_to_pay'], $intentCode);
        }

        // Kích hoạt hiển thị cho lịch hẹn này
        Cache::put($appointmentCacheKey, $id, now()->addMinutes(60));

        return view('doctor.payments.checkout', compact('appointment', 'summary', 'qrUrl', 'startTime'));
    }

    /**
     * In Phiếu Chỉ Định Cận lâm sàng (Nhiệt 80mm)
     * Bệnh nhân cầm đi khám các phòng khác
     */
    public function printReferralSlip(string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits' => fn($q) => $q->orderBy('visit_order'),
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
            'medicalRecord',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        $subVisits = $appointment->clinicalVisits->where('is_origin', false)->values();
        $originVisit = $appointment->clinicalVisits->firstWhere('is_origin', true);

        $allPaid = $subVisits->every(fn($v) => $v->payment_status === 'paid');

        return view('doctor.payments.referral-slip', compact('appointment', 'summary', 'subVisits', 'originVisit', 'allPaid'));
    }

    /**
     * In Đơn Thuốc / Kết quả (A4)
     * Bệnh nhân xuống quầy thuốc lấy thuốc
     */
    public function printPrescription(string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits.room',
            'medicalRecord.prescription',
            'payments',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);
        $prescription = $appointment->medicalRecord?->prescription;

        return view('doctor.payments.prescription-print', compact('appointment', 'summary', 'prescription'));
    }
}

