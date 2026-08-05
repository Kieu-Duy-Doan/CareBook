<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\DashboardService;
use App\Services\ReportService;

/**
 * Controller xử lý màn hình Dashboard của Admin
 * Đóng vai trò là cầu nối (Controller Skinny), chỉ nhận Request và gọi Service để xử lý logic
 */
class DashboardController extends Controller
{
    protected $dashboardService;
    protected $reportService;

    /**
     * Tiêm (Inject) DashboardService vào Controller
     * 
     * @param DashboardService $dashboardService
     * @param ReportService $reportService
     */
    public function __construct(DashboardService $dashboardService, ReportService $reportService)
    {
        $this->dashboardService = $dashboardService;
        $this->reportService = $reportService;
    }

    /**
     * Hiển thị giao diện Bảng điều khiển (Dashboard)
     * 
     * @param Request $request
     */
    public function index(Request $request)
    {
        // Xử lý bộ lọc thời gian
        $dateFrom = $request->filled('date_from') 
            ? Carbon::parse($request->date_from)->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $dateTo = $request->filled('date_to') 
            ? Carbon::parse($request->date_to)->endOfDay() 
            : Carbon::today()->endOfDay();

        // 1. Dữ liệu thẻ thông tin (KPI Cards)
        $kpiData = $this->dashboardService->getKpiData($dateFrom, $dateTo);

        // 2. Danh sách Top Bác sĩ & Lịch khám
        $topDoctors = $this->dashboardService->getTopDoctors($dateFrom, $dateTo);
        $todayAppointments = $this->dashboardService->getAppointmentsList($dateFrom, $dateTo);

        // 3. Thống kê doanh thu trong khoảng thời gian
        $revenueStats = $this->reportService->getRevenueStats($dateFrom, $dateTo);

        // Đẩy data ra view hiển thị (Chưa kèm Data biểu đồ vì sẽ fetch qua AJAX)
        return view('admin.dashboard', array_merge($kpiData, [
            'topDoctors' => $topDoctors,
            'todayAppointments' => $todayAppointments,
            'revenueStats' => $revenueStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]));
    }

    /**
     * API trả về dữ liệu biểu đồ cho AJAX
     */
    public function data(Request $request)
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // Lấy query từ Request
        $targetMonth = $request->query('target_month', Carbon::now()->month);
        $targetYear = $request->query('target_year', Carbon::now()->year);

        $trendFilter = $targetMonth === 'all' ? 'year_months' : 'month_days';

        $trendData = $this->dashboardService->getTrendData($trendFilter, $targetMonth, $targetYear);
        $specialtyData = $this->dashboardService->getSpecialtyPieData($startOfMonth);

        $peakHoursData = $this->dashboardService->getPeakHoursData($startOfMonth);
        $revenueMethodData = $this->dashboardService->getRevenueByMethodData($startOfMonth);

        return response()->json([
            'trend' => $trendData,
            'specialty' => $specialtyData,
            'peak_hours' => $peakHoursData,
            'revenue_method' => $revenueMethodData
        ]);
    }
}
