<?php 

use Illuminate\Support\Facades\Route;

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
?>