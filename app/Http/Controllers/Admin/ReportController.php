<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view('admin.reports.index', compact(
            'dateFrom', 'dateTo', 'doctorId', 'specialtyId',
            'appointmentStats', 'shiftStats', 'revenueStats',
            'doctorStats', 'specialtyStats',
            'doctors', 'specialties'
        ));
    }
}
