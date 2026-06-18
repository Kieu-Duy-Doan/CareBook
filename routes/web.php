<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::prefix('specialties')->name('specialties.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SpecialtyController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\SpecialtyController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\SpecialtyController::class, 'show'])->name('show');
        Route::put('/{id}', [\App\Http\Controllers\Admin\SpecialtyController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\SpecialtyController::class, 'toggleActive'])->name('toggle-active');
        Route::patch('/{id}/order', [\App\Http\Controllers\Admin\SpecialtyController::class, 'updateOrder'])->name('update-order');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\SpecialtyController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/doctors', [\App\Http\Controllers\Admin\SpecialtyController::class, 'addDoctor'])->name('add-doctor');
        Route::post('/{id}/doctors/remove', [\App\Http\Controllers\Admin\SpecialtyController::class, 'removeDoctor'])->name('remove-doctor');
        Route::post('/{id}/rooms', [\App\Http\Controllers\Admin\SpecialtyController::class, 'addRoom'])->name('add-room');
        Route::post('/{id}/rooms/remove', [\App\Http\Controllers\Admin\SpecialtyController::class, 'removeRoom'])->name('remove-room');
    });

    // Phòng khám
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RoomController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\RoomController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\RoomController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\RoomController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\RoomController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\RoomController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\RoomController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\RoomController::class, 'destroy'])->name('destroy');
    });

    // Lịch làm việc
    Route::prefix('work-schedules')->name('work-schedules.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'show'])->name('show');
        Route::put('/{id}', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'destroy'])->name('destroy');

        // Ngoại lệ
        Route::post('/overrides', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'storeOverride'])->name('overrides.store');
        Route::delete('/overrides/{id}', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'destroyOverride'])->name('overrides.destroy');
        Route::get('/showoverrides/{id}', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'showOverride'])->name('showOverride');
    });

    // Lịch hẹn
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AppointmentController::class, 'index'])->name('index');
        Route::get('/calendar', [\App\Http\Controllers\Admin\AppointmentController::class, 'calendar'])->name('calendar');
        Route::get('/export-csv', [\App\Http\Controllers\Admin\AppointmentController::class, 'exportCsv'])->name('export-csv');
        Route::get('/create', [\App\Http\Controllers\Admin\AppointmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AppointmentController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\AppointmentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AppointmentController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [\App\Http\Controllers\Admin\AppointmentController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AppointmentController::class, 'destroy'])->name('destroy');
    });
});
