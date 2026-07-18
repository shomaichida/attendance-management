<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // 勤怠一覧
    Route::get('/attendances', [AttendanceController::class, 'index'])
        ->name('attendances.index');

    // 勤怠詳細
    Route::get('/attendances/{attendance}', [AttendanceController::class, 'show'])
        ->name('attendances.show');

    // 勤怠更新
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])
        ->name('attendances.update');

    // 出退勤
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clock-in');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clock-out');

    // 休憩
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break-start');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break-end');

    // プロフィール
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// =========================
// 管理者
// =========================
Route::prefix('admin')
    ->name('admin.')
    ->middleware('admin')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])
            ->name('dashboard');

        Route::get('/employees', [AdminController::class, 'employees'])
            ->name('employees.index');

        Route::get('/employees/{user}', [AdminController::class, 'employeeShow'])
            ->name('employees.show');

        Route::get('/employees/{user}/attendances/{attendance}', [AdminController::class, 'attendanceShow'])
            ->scopeBindings()
            ->name('employees.attendances.show');

        Route::get('/employees/{user}/attendances/{attendance}/edit', [AdminController::class, 'attendanceEdit'])
            ->scopeBindings()
            ->name('employees.attendances.edit');

        Route::put('/employees/{user}/attendances/{attendance}', [AdminController::class, 'attendanceUpdate'])
            ->scopeBindings()
            ->name('employees.attendances.update');
    });

require __DIR__.'/auth.php';
