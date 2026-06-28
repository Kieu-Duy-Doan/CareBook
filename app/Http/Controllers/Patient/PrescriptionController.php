<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

class PrescriptionController extends Controller
{
    public function show($id)
    {
        return view('patient.prescriptions.show', compact('id'));
    }
}
