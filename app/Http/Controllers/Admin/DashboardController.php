<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\DashboardService;

/**
 * Controller xử lý màn hình Dashboard của Admin
 * Đóng vai trò là cầu nối (Controller Skinny), chỉ nhận Request và gọi Service để xử lý logic
 */
class DashboardController extends Controller
{
    protected $dashboardService;

    /**
     * Tiêm (Inject) DashboardService vào Controller
     * 
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Hiển thị giao diện Bảng điều khiển (Dashboard)
     * 
     * @param Request $request
     */
    public function index(Request $request)
    {
        // Khởi tạo mốc thời gian hiện tại và đầu tháng làm chuẩn
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Dữ liệu thẻ thông tin (KPI Cards)
        $kpiData = $this->dashboardService->getKpiData($today, $startOfMonth);

        // 2. Danh sách Top Bác sĩ & Lịch khám hôm nay
        $topDoctors = $this->dashboardService->getTopDoctors($startOfMonth);
        $todayAppointments = $this->dashboardService->getTodayAppointments($today);

        // Đẩy data ra view hiển thị (Chưa kèm Data biểu đồ vì sẽ fetch qua AJAX)
        return view('admin.dashboard', array_merge($kpiData, [
            'topDoctors' => $topDoctors,
            'todayAppointments' => $todayAppointments,
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

        return response()->json([
            'trend' => $trendData,
            'specialty' => $specialtyData
        ]);
    }
}
