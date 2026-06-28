<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'redirectToDashboard'])->name('dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/ajax-search', [\App\Http\Controllers\Admin\UserController::class, 'ajaxSearch'])->name('ajax-search');
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('toggle-active');
    });
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/export', [\App\Http\Controllers\Admin\DoctorController::class, 'export'])->name('export');
        Route::get('/import/template', [\App\Http\Controllers\Admin\DoctorController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import', [\App\Http\Controllers\Admin\DoctorController::class, 'import'])->name('import');
        Route::get('/', [\App\Http\Controllers\Admin\DoctorController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\DoctorController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\DoctorController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\DoctorController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\DoctorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\DoctorController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\DoctorController::class, 'toggleActive'])->name('toggle-active');
    });
    Route::prefix('receptionists')->name('receptionists.')->group(function () {
        Route::get('/export', [\App\Http\Controllers\Admin\ReceptionistController::class, 'export'])->name('export');
        Route::get('/import/template', [\App\Http\Controllers\Admin\ReceptionistController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import', [\App\Http\Controllers\Admin\ReceptionistController::class, 'import'])->name('import');
        Route::get('/', [\App\Http\Controllers\Admin\ReceptionistController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\ReceptionistController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\ReceptionistController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\ReceptionistController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ReceptionistController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\ReceptionistController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\ReceptionistController::class, 'toggleActive'])->name('toggle-active');
    });
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/export', [\App\Http\Controllers\Admin\PatientController::class, 'export'])->name('export');
        Route::get('/', [\App\Http\Controllers\Admin\PatientController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\PatientController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PatientController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\PatientController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\PatientController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\PatientController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\PatientController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\PatientController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/export', [\App\Http\Controllers\Admin\CustomerController::class, 'export'])->name('export');
        Route::get('/', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CustomerController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CustomerController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\CustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('specialties')->name('specialties.')->group(function () {
        Route::post('/import', [\App\Http\Controllers\Admin\SpecialtyController::class, 'import'])->name('import');
        Route::get('/export', [\App\Http\Controllers\Admin\SpecialtyController::class, 'export'])->name('export');
        Route::get('/download-template', [\App\Http\Controllers\Admin\SpecialtyController::class, 'downloadTemplate'])->name('download-template');
        
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
        Route::post('/import', [\App\Http\Controllers\Admin\RoomController::class, 'import'])->name('import');
        Route::get('/export', [\App\Http\Controllers\Admin\RoomController::class, 'export'])->name('export');
        Route::get('/download-template', [\App\Http\Controllers\Admin\RoomController::class, 'downloadTemplate'])->name('download-template');

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
        Route::post('/import', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'import'])->name('import');
        Route::get('/export', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'export'])->name('export');
        Route::get('/download-template', [\App\Http\Controllers\Admin\WorkScheduleController::class, 'downloadTemplate'])->name('download-template');

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

    // Giám sát Khám Lâm sàng
    Route::prefix('clinical-visits')->name('clinical-visits.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ClinicalVisitController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\ClinicalVisitController::class, 'show'])->name('show');
    });

    // Nhật ký lịch hẹn
    Route::prefix('appointment-logs')->name('appointment-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AppointmentLogController::class, 'index'])->name('index');
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

    // Chatbot
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        // Intents
        Route::prefix('intents')->name('intents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-active', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'toggleActive'])->name('toggle-active');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'destroy'])->name('destroy');


            // Responses (Nested inside Intents)
            Route::post('/{intent_id}/responses', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'storeResponse'])->name('responses.store');
            Route::put('/{intent_id}/responses/{id}', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'updateResponse'])->name('responses.update');
            Route::patch('/{intent_id}/responses/{id}/toggle-active', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'toggleResponseActive'])->name('responses.toggle-active');
            Route::delete('/{intent_id}/responses/{id}', [\App\Http\Controllers\Admin\ChatbotIntentController::class, 'destroyResponse'])->name('responses.destroy');
        });
        // Sessions
        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ChatSessionController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\ChatSessionController::class, 'show'])->name('show');
            Route::patch('/messages/{id}/flag', [\App\Http\Controllers\Admin\ChatSessionController::class, 'toggleFlag'])->name('messages.flag');
        });
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


    // Thông báo
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/search-users', [\App\Http\Controllers\Admin\NotificationController::class, 'searchUsers'])->name('search-users');
        Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('store');
        Route::delete('/', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/resend', [\App\Http\Controllers\Admin\NotificationController::class, 'resend'])->name('resend');
    });


    // Cài đặt
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('update');
    });
});


// API routes (normally in routes/api.php, placing here for convenience)
Route::prefix('api')->name('api.')->group(function () {
    // Lấy danh sách bác sĩ theo chuyên khoa
    Route::get('/doctors/by-specialty/{specialtyId}', [\App\Http\Controllers\Api\DoctorController::class, 'getBySpecialty'])->name('doctors.by-specialty');

    // Lấy danh lịch làm việc theo bác sĩ và ngày
    Route::get('/work-schedule/by-doctor-date/{doctorId}/{appointmentDate}', [\App\Http\Controllers\Api\WorkScheduleController::class, 'getWorkSchedule'])->name('work-schedule');
    Route::post('/chatbot/message', [\App\Http\Controllers\Api\ChatbotController::class, 'sendMessage'])->name('chatbot.message');
});
