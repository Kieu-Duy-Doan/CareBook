<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ của bạn.');
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $data = $this->dashboardService->getDoctorDashboardData($today, $startOfMonth, $doctorProfile->id);

        // Cần thêm doctorProfile vào data để view có thể hiển thị
        $data['doctorProfile'] = $doctorProfile;

        return view('doctor.dashboard.index', $data);
    }
}
