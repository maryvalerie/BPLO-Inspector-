<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Inspector\DashboardController as InspectorDashboardController;
use App\Http\Controllers\Inspector\PermitController as InspectorPermitController;
use App\Http\Controllers\PublicPages\VerificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inspector/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])
        ->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:inspector'])->prefix('inspector')->name('inspector.')->group(function (): void {
    Route::get('/dashboard', [InspectorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/permits', [InspectorPermitController::class, 'index'])->name('permits.index');
    Route::get('/permits/{permit}', [InspectorPermitController::class, 'show'])->name('permits.show');
});

Route::get('/verify', [VerificationController::class, 'lookup'])->name('verify.lookup');
Route::get('/verify/{token}', [VerificationController::class, 'show'])
    ->middleware('throttle:verify')
    ->name('verify.show');
