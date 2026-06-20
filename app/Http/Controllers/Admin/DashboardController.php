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
     * @param Request $request Chứa các tham số query (ví dụ: trend=day|month|year)
     */
    public function index(Request $request)
    {
        // Khởi tạo mốc thời gian hiện tại và đầu tháng làm chuẩn
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Dữ liệu thẻ thông tin (KPI Cards)
        // Gọi service tính toán: Số lịch khám, tăng trưởng, bệnh nhân mới, tỷ lệ hoàn thành...
        $kpiData = $this->dashboardService->getKpiData($today, $startOfMonth);

        // 2. Dữ liệu biểu đồ xu hướng (Trend Chart)
        // Mặc định là tháng hiện tại và năm hiện tại
        $targetMonth = $request->query('target_month', Carbon::now()->month);
        $targetYear = $request->query('target_year', Carbon::now()->year);
        
        // Nếu chọn "Cả năm" (all) thì xem theo tháng, ngược lại xem theo ngày trong tháng đó
        $trendFilter = $targetMonth === 'all' ? 'year_months' : 'month_days';
        
        $trendData = $this->dashboardService->getTrendData($trendFilter, $targetMonth, $targetYear);

        // 3. Phân bổ chuyên khoa (Pie Chart)
        // Gọi service để đếm số lịch khám chia theo từng khoa trong tháng
        $specialtyData = $this->dashboardService->getSpecialtyPieData($startOfMonth);

        // 4. Danh sách Top Bác sĩ & Lịch khám hôm nay
        // Gọi service lấy top 5 bác sĩ bận rộn nhất và 10 lịch khám tiếp theo trong ngày
        $topDoctors = $this->dashboardService->getTopDoctors($startOfMonth);
        $todayAppointments = $this->dashboardService->getTodayAppointments($today);

        // Gộp tất cả data (mảng) lại và đẩy ra view hiển thị
        return view('admin.dashboard', array_merge($kpiData, $trendData, $specialtyData, [
            'topDoctors' => $topDoctors,
            'todayAppointments' => $todayAppointments,
        ]));
    }
}
