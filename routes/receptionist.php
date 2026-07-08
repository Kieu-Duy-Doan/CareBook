<?php

use Illuminate\Support\Facades\Route;

Route::prefix('receptionist')->name('receptionist.')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Receptionist\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Receptionist\AuthController::class, 'login'])->name('login.post');
        Route::get('/quen-mat-khau', [\App\Http\Controllers\Receptionist\AuthController::class, 'showForgotPassword'])->name('password.request');
    });

    // Authenticated Receptionist routes
    Route::middleware(['auth', 'role:receptionist'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Receptionist\AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Receptionist\DashboardController::class, 'index'])->name('dashboard');
        
        // Appointments
        Route::resource('appointments', \App\Http\Controllers\Receptionist\AppointmentController::class);
        
        // Patients
        Route::resource('patients', \App\Http\Controllers\Receptionist\PatientController::class);
        
        // Customers
        Route::resource('customers', \App\Http\Controllers\Receptionist\CustomerController::class);
        
        // Clinical Visits
        Route::resource('clinical-visits', \App\Http\Controllers\Receptionist\ClinicalVisitController::class)->only(['index', 'show']);
        
        // Payments
        Route::get('payments/{clinical_visit}', [\App\Http\Controllers\Receptionist\PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments/{clinical_visit}/manual', [\App\Http\Controllers\Receptionist\PaymentController::class, 'storeManual'])->name('payments.storeManual');
        Route::post('payments/{clinical_visit}/payos', [\App\Http\Controllers\Receptionist\PaymentController::class, 'createPayOS'])->name('payments.createPayOS');
        
        // Payments (Updating directly on clinical_visits)
        Route::resource('payments', \App\Http\Controllers\Receptionist\PaymentController::class)->only(['index', 'edit', 'update']);
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Receptionist\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\App\Http\Controllers\Receptionist\ProfileController::class, 'update'])->name('profile.update');
    });
});
