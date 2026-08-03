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

    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ của bạn.');
        }

        $fromDateStr = $request->input('from_date', Carbon::today()->format('Y-m-d'));
        $toDateStr = $request->input('to_date', Carbon::today()->format('Y-m-d'));

        $fromDate = Carbon::parse($fromDateStr)->startOfDay();
        $toDate = Carbon::parse($toDateStr)->endOfDay();

        $data = $this->dashboardService->getDoctorDashboardData($fromDate, $toDate, $doctorProfile->id);

        // Cần thêm doctorProfile vào data để view có thể hiển thị
        $data['doctorProfile'] = $doctorProfile;
        $data['fromDate'] = $fromDateStr;
        $data['toDate'] = $toDateStr;

        return view('doctor.dashboard.index', $data);
    }
}
