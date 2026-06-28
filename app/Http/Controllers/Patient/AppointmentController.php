<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('patient.appointments.index');
    }

    public function show($id)
    {
        return view('patient.appointments.show', compact('id'));
    }

    public function cancel($id)
    {
        // Dummy cancel method - the user mentioned they implemented this in another branch.
        // It should dispatch SendCancellationNotificationJob if they haven't done it there.
        return redirect()->back();
    }
}
