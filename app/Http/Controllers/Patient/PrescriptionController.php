<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

class PrescriptionController extends Controller
{
    public function index()
    {
        return redirect()->route('patient.records.index');
    }

    public function show($id)
    {
        return redirect()->route('patient.records.index');
    }
}
