<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        // Mặc định lọc theo tháng hiện tại
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::today();

        $doctorId = $request->filled('doctor_id') ? (int)$request->doctor_id : null;
        $specialtyId = $request->filled('specialty_id') ? (int)$request->specialty_id : null;

        // Lấy dữ liệu thống kê
        $appointmentStats = $this->reportService->getAppointmentStats($dateFrom, $dateTo, $doctorId, $specialtyId);
        $shiftStats = $this->reportService->getAppointmentsByShift($dateFrom, $dateTo, $doctorId, $specialtyId);
        $revenueStats = $this->reportService->getRevenueStats($dateFrom, $dateTo, $doctorId, $specialtyId);
        $doctorStats = $this->reportService->getStatsByDoctor($dateFrom, $dateTo, $specialtyId);
        $specialtyStats = $this->reportService->getStatsBySpecialty($dateFrom, $dateTo);

        // Dữ liệu cho bộ lọc
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Drill-down: Khi có bộ lọc bác sĩ hoặc chuyên khoa, load danh sách lịch hẹn chi tiết
        $detailRows = null;
        if ($doctorId || $specialtyId) {
            $q = Appointment::with(['patientProfile', 'doctor.user', 'specialty', 'payments' => fn($p) => $p->where('status', 'paid')])
                ->whereDate('appointment_date', '>=', $dateFrom->toDateString())
                ->whereDate('appointment_date', '<=', $dateTo->toDateString());
            if ($doctorId) $q->where('doctor_profile_id', $doctorId);
            if ($specialtyId) $q->where('specialty_id', $specialtyId);
            $detailRows = $q->latest('appointment_date')->latest('appointment_time')->limit(200)->get();
        }

        return view('admin.reports.index', compact(
            'dateFrom', 'dateTo', 'doctorId', 'specialtyId',
            'appointmentStats', 'shiftStats', 'revenueStats',
            'doctorStats', 'specialtyStats',
            'doctors', 'specialties',
            'detailRows'
        ));
    }

    public function exportCsv(Request $request)
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
        $doctorId = $request->filled('doctor_id') ? (int)$request->doctor_id : null;
        $specialtyId = $request->filled('specialty_id') ? (int)$request->specialty_id : null;

        $q = Appointment::with(['patientProfile', 'doctor.user', 'specialty', 'payments' => fn($p) => $p->where('status', 'paid')])
            ->whereDate('appointment_date', '>=', $dateFrom->toDateString())
            ->whereDate('appointment_date', '<=', $dateTo->toDateString());
        if ($doctorId) $q->where('doctor_profile_id', $doctorId);
        if ($specialtyId) $q->where('specialty_id', $specialtyId);
        $appointments = $q->latest('appointment_date')->get();

        $filename = 'bao-cao-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($appointments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Mã LH', 'Bệnh nhân', 'Bác sĩ', 'Chuyên khoa', 'Ngày khám', 'Giờ', 'Trạng thái', 'Tổng thu (₫)']);
            foreach ($appointments as $a) {
                fputcsv($file, [
                    $a->appointment_code,
                    $a->patientProfile->full_name ?? '',
                    $a->doctor->full_title ?? '',
                    $a->specialty->name ?? '',
                    $a->appointment_date?->format('d/m/Y'),
                    substr($a->appointment_time ?? '', 0, 5),
                    $a->status,
                    number_format($a->payments->sum('amount'), 0, ',', '.'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
