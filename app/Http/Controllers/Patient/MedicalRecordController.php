<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

class MedicalRecordController extends Controller
{
    public function index()
    {
        return view('patient.medical-records.index');
    }

    public function show($id)
    {
        return view('patient.medical-records.show', compact('id'));
    }
}
