<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
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
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->middleware('role:youth')->name('dashboard');
    Route::view('/officer', 'officer.dashboard')->middleware('role:officer')->name('officer.dashboard');
    Route::view('/federation', 'federation.dashboard')->middleware('role:federation')->name('federation.dashboard');
    Route::view('/admin', 'admin.dashboard')->middleware('role:administrator')->name('admin.dashboard');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
