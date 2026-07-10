<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicalVisit;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicalVisitController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        // Lấy tất cả lịch hẹn có clinical_visits của bác sĩ này
        $query = Appointment::with(['patientProfile', 'clinicalVisits' => function($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id);
            }, 'payments'])
            ->whereHas('clinicalVisits', function($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id);
            })
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('appointment_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('patientProfile', function($pq) use ($request) {
                      $pq->where('full_name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('doctor.clinical-visits.index', compact('appointments'));
    }

    public function show($appointment_id)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits' => function($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id)->orderBy('visit_order');
            },
            'clinicalVisits.room',
            'payments'
        ])
        ->whereHas('clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id);
        })
        ->findOrFail($appointment_id);

        $totalAmount = $appointment->clinicalVisits->sum('payment_amount');
        $paidAmount = $appointment->payments->sum('amount');
        $unpaidAmount = max(0, $totalAmount - $paidAmount);
        
        $rooms = \App\Models\Room::where('is_active', true)->get();

        return view('doctor.clinical-visits.show', compact('appointment', 'totalAmount', 'paidAmount', 'unpaidAmount', 'rooms'));
    }

    public function storeVisit(Request $request, $appointment_id)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'notes' => 'nullable|string',
            'payment_amount' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        // Verify the appointment belongs to this doctor
        $appointment = Appointment::whereHas('clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id);
        })->findOrFail($appointment_id);

        $maxOrder = \App\Models\ClinicalVisit::where('appointment_id', $appointment->id)->max('visit_order');
        $nextOrder = $maxOrder ? $maxOrder + 1 : 1;

        \App\Models\ClinicalVisit::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $doctorProfile->id,
            'room_id' => $request->room_id,
            'visit_order' => $nextOrder,
            'is_origin' => false,
            'status' => 'waiting',
            'payment_status' => 'pending',
            'payment_amount' => $request->payment_amount ?? 0,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Đã thêm giám sát lâm sàng mới.');
    }

    public function destroyVisit($visit_id)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $visit = \App\Models\ClinicalVisit::where('doctor_profile_id', $doctorProfile->id)
            ->where('is_origin', false) // Don't allow deleting the origin visit
            ->findOrFail($visit_id);

        $visit->delete();

        return back()->with('success', 'Đã xóa giám sát lâm sàng.');
    }

    public function updateVisit(Request $request, $visit_id)
    {
        $request->validate([
            'findings' => 'nullable|string',
            'status' => 'required|in:waiting,in_progress,completed,refused,redirected',
            'payment_amount' => 'nullable|numeric|min:0',
            'result_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $visit = ClinicalVisit::where('doctor_profile_id', $doctorProfile->id)->findOrFail($visit_id);

        $visit->findings = $request->findings;
        $visit->status = $request->status;
        $visit->payment_amount = $request->payment_amount ?? 0;
        
        if ($request->hasFile('result_files')) {
            $files = $visit->result_files ?? [];
            foreach ($request->file('result_files') as $file) {
                $path = $file->store('clinical_results', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $visit->result_files = $files;
        }
        
        if ($request->status === 'in_progress' && is_null($visit->started_at)) {
            $visit->started_at = now();
        }
        if ($request->status === 'completed' && is_null($visit->completed_at)) {
            $visit->completed_at = now();
        }

        $visit->save();

        return back()->with('success', 'Đã cập nhật thông tin khám lâm sàng.');
    }

    public function processPayment(Request $request, $appointment_id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qr,insurance,waived',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string'
        ]);

        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::whereHas('clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id);
        })->findOrFail($appointment_id);

        DB::beginTransaction();
        try {
            // Cập nhật payment_status của các clinical_visits
            ClinicalVisit::where('appointment_id', $appointment->id)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'collected_by' => $user->id,
                    'payment_method' => $request->payment_method
                ]);

            // Tạo bản ghi Payment
            Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'collected_by' => $user->id,
                'paid_at' => now(),
                'notes' => $request->notes,
                'transaction_id' => 'TXN' . strtoupper(uniqid())
            ]);

            DB::commit();
            return back()->with('success', 'Đã thanh toán thành công ' . number_format($request->amount) . ' VNĐ.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi thanh toán: ' . $e->getMessage());
        }
    }
}
