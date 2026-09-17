<?php

use App\Http\Controllers\App\AiController;
use App\Http\Controllers\App\AttendanceController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\MemberController;
use App\Http\Controllers\App\MembershipController;
use App\Http\Controllers\App\MembershipPlanController;
use App\Http\Controllers\App\PaymentController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\App\TrainerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Platform\PlatformSettingsController;
use App\Http\Controllers\Platform\TenantController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Entry + auth
// ---------------------------------------------------------------------------
Route::get('/', HomeController::class)->name('home');
Route::get('/home', HomeController::class);

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:login')->name('register.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ---------------------------------------------------------------------------
// Platform (super admin) - manages the fleet of gyms
// ---------------------------------------------------------------------------
Route::prefix('platform')
    ->name('platform.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function () {
        Route::get('/', fn () => redirect()->route('platform.tenants.index'))->name('index');

        Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::delete('tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');

        Route::post('tenants/{tenant}/switch', [TenantController::class, 'switchTo'])->name('tenants.switch');
        Route::post('tenants/{tenant}/toggle', [TenantController::class, 'toggle'])->name('tenants.toggle');
        Route::post('tenants/leave', [TenantController::class, 'leave'])->name('tenants.leave');

        Route::get('settings', [PlatformSettingsController::class, 'index'])->name('settings');
        Route::put('settings', [PlatformSettingsController::class, 'update'])->name('settings.update');
    });

// ---------------------------------------------------------------------------
// Gym workspace (tenant database)
// ---------------------------------------------------------------------------
Route::prefix('app')
    ->name('app.')
    ->middleware(['auth', 'tenant', 'require.tenant', 'role:gym_admin,trainer'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('members', MemberController::class);
        Route::resource('trainers', TrainerController::class);
        Route::resource('plans', MembershipPlanController::class)->parameters(['plans' => 'plan']);

        Route::resource('memberships', MembershipController::class);
        Route::post('memberships/{membership}/cancel', [MembershipController::class, 'cancel'])->name('memberships.cancel');

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('attendance/{attendance}/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // AI
        Route::get('ai', [AiController::class, 'index'])->name('ai.index');
        Route::post('ai/workout-plans', [AiController::class, 'storeWorkoutPlan'])->middleware('throttle:ai')->name('ai.workout.store');
        Route::post('ai/assistant', [AiController::class, 'assistant'])->middleware('throttle:ai')->name('ai.assistant');
        Route::get('ai/insights', [AiController::class, 'insights'])->name('ai.insights');

        // Gym settings + staff
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings/staff', [SettingsController::class, 'storeStaff'])->name('settings.staff.store');
        Route::put('settings/staff/{user}', [SettingsController::class, 'updateStaff'])->name('settings.staff.update');
        Route::delete('settings/staff/{user}', [SettingsController::class, 'destroyStaff'])->name('settings.staff.destroy');
    });
