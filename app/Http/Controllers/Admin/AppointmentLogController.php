<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentLog;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentLogController extends Controller
{
    /**
     * Display a listing of the appointment logs.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AppointmentLog::with(['changedBy', 'appointment']);

        // Filter by Appointment ID
        if ($request->filled('appointment_id')) {
            $query->where('appointment_id', $request->input('appointment_id'));
        }

        // Filter by Changed By User
        if ($request->filled('changed_by')) {
            $query->where('changed_by', $request->input('changed_by'));
        }

        // Filter by Date From
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        // Filter by Date To
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get admins and receptionists for the filter dropdown
        $users = User::whereIn('role', ['admin', 'receptionist', 'doctor'])->get();

        return view('admin.appointment-logs.index', compact('logs', 'users'));
    }
}
