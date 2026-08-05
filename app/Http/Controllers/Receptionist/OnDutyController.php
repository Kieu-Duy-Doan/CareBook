<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class OnDutyController extends Controller
{
    public function index()
    {
        $today = Carbon::now();
        // dayOfWeekIso: Mon=1 ... Sun=7, matches DB day_of_week column
        $dayOfWeek = $today->dayOfWeekIso;

        $schedules = WorkSchedule::with(['doctor.user', 'room'])
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        // Group by room
        $byRoom = $schedules->groupBy('room_id');

        return view('receptionist.on-duty.index', compact('schedules', 'byRoom', 'today'));
    }
}
