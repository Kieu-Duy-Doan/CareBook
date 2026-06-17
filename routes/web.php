<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Guest only
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.post');
});

// Auth
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'redirectToDashboard'])->name('dashboard');
});

// Admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('toggle-active');
        });

        Route::prefix('receptionists')->name('receptionists.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReceptionistController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ReceptionistController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\ReceptionistController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\ReceptionistController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ReceptionistController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\ReceptionistController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\ReceptionistController::class, 'toggleActive'])->name('toggle-active');
        });

        Route::prefix('doctors')->name('doctors.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\DoctorController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\DoctorController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\DoctorController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\DoctorController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\DoctorController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\DoctorController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\DoctorController::class, 'toggleActive'])->name('toggle-active');
        });

        Route::prefix('patients')->name('patients.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PatientController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\PatientController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\PatientController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\PatientController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\PatientController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\PatientController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\PatientController::class, 'toggleActive'])->name('toggle-active');
        });

        // Chuyên khoa
        Route::prefix('specialties')->name('specialties.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SpecialtyController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\SpecialtyController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\SpecialtyController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\SpecialtyController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\SpecialtyController::class, 'toggleActive'])->name('toggle-active');
            Route::patch('/{id}/order', [\App\Http\Controllers\Admin\SpecialtyController::class, 'updateOrder'])->name('update-order');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\SpecialtyController::class, 'destroy'])->name('destroy');
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

        // Bài viết
        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\PostController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\PostController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\PostController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\PostController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-publish', [\App\Http\Controllers\Admin\PostController::class, 'togglePublish'])->name('toggle-publish');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('destroy');
        });

        // FAQ
        Route::prefix('faqs')->name('faqs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FaqController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\FaqController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\FaqController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\FaqController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\FaqController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\FaqController::class, 'toggleActive'])->name('toggle-active');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\FaqController::class, 'destroy'])->name('destroy');
        });

        // Chatbot
        Route::prefix('chatbot')->name('chatbot.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ChatbotController::class, 'index'])->name('index');
            Route::post('/intents', [\App\Http\Controllers\Admin\ChatbotController::class, 'storeIntent'])->name('intents.store');
            Route::put('/intents/{id}', [\App\Http\Controllers\Admin\ChatbotController::class, 'updateIntent'])->name('intents.update');
            Route::patch('/intents/{id}/toggle-active', [\App\Http\Controllers\Admin\ChatbotController::class, 'toggleIntentActive'])->name('intents.toggle-active');
            Route::delete('/intents/{id}', [\App\Http\Controllers\Admin\ChatbotController::class, 'destroyIntent'])->name('intents.destroy');

            Route::post('/responses', [\App\Http\Controllers\Admin\ChatbotController::class, 'storeResponse'])->name('responses.store');
            Route::put('/responses/{id}', [\App\Http\Controllers\Admin\ChatbotController::class, 'updateResponse'])->name('responses.update');
            Route::patch('/responses/{id}/toggle-active', [\App\Http\Controllers\Admin\ChatbotController::class, 'toggleResponseActive'])->name('responses.toggle-active');
            Route::delete('/responses/{id}', [\App\Http\Controllers\Admin\ChatbotController::class, 'destroyResponse'])->name('responses.destroy');

            Route::post('/test', [\App\Http\Controllers\Admin\ChatbotController::class, 'testChat'])->name('test');
        });

        // Thông báo
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('store');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
        });

        // Cài đặt
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
            Route::put('/', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('update');
            Route::get('/logs', [\App\Http\Controllers\Admin\SettingController::class, 'logs'])->name('logs');
        });
    });

// API routes (normally in routes/api.php, placing here for convenience)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/doctors/{doctorId}/available-slots', [\App\Http\Controllers\Api\WorkScheduleController::class, 'getAvailableSlots'])->name('doctors.available-slots');

    // Lấy danh sách bác sĩ theo chuyên khoa
    Route::get('/doctors/by-specialty/{specialtyId}', [\App\Http\Controllers\Api\DoctorController::class, 'getBySpecialty'])->name('doctors.by-specialty');

    // Lấy danh sách bác sĩ theo chuyên khoa
    Route::get('/work-schedule/by-doctor-date/{doctorId}/{appointmentDate}', [\App\Http\Controllers\Api\WorkScheduleController::class, 'getWorkSchedule'])->name('work-schedule');
});


