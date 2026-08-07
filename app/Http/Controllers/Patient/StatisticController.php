<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PatientStatisticService;

class StatisticController extends Controller
{
    public function __construct(private PatientStatisticService $statisticService)
    {
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year', date('Y'))
        ];

        $data = $this->statisticService->getStatistics($userId, $filters);

        return view('patient.statistics.index', $data);
    }
}
