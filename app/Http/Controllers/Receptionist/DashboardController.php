<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
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
        $today = Carbon::today();
        $data = $this->dashboardService->getReceptionistDashboardData($today);

        return view('receptionist.dashboard', $data);
    }
}
