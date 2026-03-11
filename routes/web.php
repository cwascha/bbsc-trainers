<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HoursController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TrainingPlanController;
use App\Http\Controllers\Webhooks\TwilioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// ─── Public Pages ──────────────────────────────────────────────────────────
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');

// ─── Trainer Routes ────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/availability', [AvailabilityController::class, 'store'])->name('availability.store');
    Route::delete('/availability/{availability}', [AvailabilityController::class, 'destroy'])->name('availability.destroy');
    Route::get('/hours', [HoursController::class, 'index'])->name('hours.index');
    Route::get('/training-plans', [TrainingPlanController::class, 'index'])->name('training-plans.index');
    Route::get('/training-plans/{trainingPlan}/download', [TrainingPlanController::class, 'download'])->name('training-plans.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Admin Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/trainers', [Admin\TrainerController::class, 'index'])->name('trainers.index');
    Route::get('/sessions', [Admin\AssignmentController::class, 'index'])->name('sessions.index');
    Route::post('/assignments/run', [Admin\AssignmentController::class, 'run'])->name('assignments.run');
    Route::get('/training-plans', [Admin\TrainingPlanController::class, 'index'])->name('training-plans.index');
    Route::post('/training-plans', [Admin\TrainingPlanController::class, 'store'])->name('training-plans.store');
    Route::delete('/training-plans/{trainingPlan}', [Admin\TrainingPlanController::class, 'destroy'])->name('training-plans.destroy');
    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [Admin\NotificationController::class, 'send'])->name('notifications.send');
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [Admin\ReportController::class, 'export'])->name('reports.export');
});

// ─── Twilio Webhook (no auth, CSRF exempt) ────────────────────────────────
Route::post('/webhooks/twilio/sms', [TwilioController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.twilio.sms');

require __DIR__.'/auth.php';
