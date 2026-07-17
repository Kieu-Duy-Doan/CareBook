<?php

use Illuminate\Support\Facades\Route;

Route::prefix('doctor')->name('doctor.')->group(function () {
    // Guest routes (Đã chuyển sang form chung ở /login)
    // Route::middleware('guest')->group(function () {
    //     Route::get('/login', [\App\Http\Controllers\Doctor\AuthController::class, 'showLogin'])->name('login');
    //     Route::post('/login', [\App\Http\Controllers\Doctor\AuthController::class, 'login'])->name('login.post');
    //     Route::get('/quen-mat-khau', [\App\Http\Controllers\Doctor\AuthController::class, 'showForgotPassword'])->name('password.request');
    // });

    // Authenticated Doctor routes
    Route::middleware(['auth', 'role:doctor'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Doctor\AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Doctor\DashboardController::class, 'index'])->name('dashboard');

        // Appointments
        Route::get('appointments', [\App\Http\Controllers\Doctor\AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'show'])->name('appointments.show');
        Route::post('appointments/{appointment}/status', [\App\Http\Controllers\Doctor\AppointmentController::class, 'updateStatus'])->name('appointments.update-status');

        // Clinical Visits
        Route::get('clinical-visits', [\App\Http\Controllers\Doctor\ClinicalVisitController::class, 'index'])->name('clinical-visits.index');
        Route::get('clinical-visits/{appointment}', [\App\Http\Controllers\Doctor\ClinicalVisitController::class, 'show'])->name('clinical-visits.show');
        Route::put('clinical-visits/{visit}', [\App\Http\Controllers\Doctor\ClinicalVisitController::class, 'updateVisit'])->name('clinical-visits.update');
        Route::post('clinical-visits/{appointment}/store-visit', [\App\Http\Controllers\Doctor\ClinicalVisitController::class, 'storeVisit'])->name('clinical-visits.store-visit');
        Route::delete('clinical-visits/{visit}', [\App\Http\Controllers\Doctor\ClinicalVisitController::class, 'destroyVisit'])->name('clinical-visits.destroy-visit');


        // Payments
        Route::get('payments', [\App\Http\Controllers\Doctor\PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{appointment}/checkout', [\App\Http\Controllers\Doctor\PaymentController::class, 'checkout'])->name('payments.checkout');
        Route::get('payments/{appointment}', [\App\Http\Controllers\Doctor\PaymentController::class, 'show'])->name('payments.show');
        Route::get('payments/{appointment}/print-referral', [\App\Http\Controllers\Doctor\PaymentController::class, 'printReferralSlip'])->name('payments.print-referral');
        Route::get('payments/{appointment}/print-prescription', [\App\Http\Controllers\Doctor\PaymentController::class, 'printPrescription'])->name('payments.print-prescription');

        // Customer Display (Màn hình phụ)
        Route::get('/customer-display', [\App\Http\Controllers\Doctor\CustomerDisplayController::class, 'index'])->name('customer-display.index');
        Route::get('/customer-display/status', [\App\Http\Controllers\Doctor\CustomerDisplayController::class, 'status'])->name('customer-display.status');

        // Patient History
        Route::get('patient-history', [\App\Http\Controllers\Doctor\PatientHistoryController::class, 'index'])->name('patient-history.index');
        Route::get('patient-history/{patient}', [\App\Http\Controllers\Doctor\PatientHistoryController::class, 'show'])->name('patient-history.show');

        // Work Schedules
        Route::get('work-schedules', [\App\Http\Controllers\Doctor\WorkScheduleController::class, 'index'])->name('work-schedules.index');
        Route::get('work-schedules/{schedule}', [\App\Http\Controllers\Doctor\WorkScheduleController::class, 'show'])->name('work-schedules.show');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Doctor\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\App\Http\Controllers\Doctor\ProfileController::class, 'update'])->name('profile.update');

        // Medical Records
        Route::get('appointments/{appointment}/medical-record/create', [\App\Http\Controllers\Doctor\MedicalRecordController::class, 'create'])->name('medical-records.create');
        Route::post('appointments/{appointment}/medical-record', [\App\Http\Controllers\Doctor\MedicalRecordController::class, 'store'])->name('medical-records.store');
        Route::get('medical-records/{medical_record}', [\App\Http\Controllers\Doctor\MedicalRecordController::class, 'show'])->name('medical-records.show');
        Route::get('medical-records/{medical_record}/edit', [\App\Http\Controllers\Doctor\MedicalRecordController::class, 'edit'])->name('medical-records.edit');
        Route::put('medical-records/{medical_record}', [\App\Http\Controllers\Doctor\MedicalRecordController::class, 'update'])->name('medical-records.update');

        // Prescriptions
        Route::get('medical-records/{medical_record}/prescription/create', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('medical-records/{medical_record}/prescription', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::get('prescriptions/{prescription}/edit', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::put('prescriptions/{prescription}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'update'])->name('prescriptions.update');
        Route::delete('prescriptions/{prescription}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
    });
});
