<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\StaffSessionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/privacy', 'privacy')->name('privacy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/sk-access', [StaffSessionController::class, 'create'])->name('staff.login');
    Route::post('/sk-access', [StaffSessionController::class, 'store'])->name('staff.login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/dashboard', 'dashboard')->middleware('role:youth')->name('dashboard');
    Route::view('/officer', 'officer.dashboard')->middleware('role:officer')->name('officer.dashboard');
    Route::view('/federation', 'federation.dashboard')->middleware('role:federation')->name('federation.dashboard');

    Route::middleware('role:administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);
        Route::get('barangays', [Admin\BarangayController::class, 'index'])->name('barangays.index');
        Route::patch('barangays/{barangay}', [Admin\BarangayController::class, 'update'])->name('barangays.update');
        Route::get('audit-trail', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
