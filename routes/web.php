<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HoursController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TrainingPlanController;
use App\Http\Controllers\W9Controller;
use App\Http\Controllers\Webhooks\TwilioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// ─── Public Pages ──────────────────────────────────────────────────────────
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');

// ─── Signed Training Plan Download (SMS link, no login required) ───────────
Route::get('/training-plans/{trainingPlan}/view', [TrainingPlanController::class, 'viewSigned'])
    ->middleware('signed')
    ->name('training-plans.view-signed');

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

    Route::get('/w9/template', [W9Controller::class, 'template'])->name('w9.template');
    Route::post('/w9', [W9Controller::class, 'upload'])->name('w9.upload');
    Route::delete('/w9', [W9Controller::class, 'destroy'])->name('w9.destroy');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/view', [DocumentController::class, 'view'])->name('documents.view');
});

// ─── Admin Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/trainers', [Admin\TrainerController::class, 'index'])->name('trainers.index');
    Route::post('/trainers', [Admin\TrainerController::class, 'store'])->name('trainers.store');
    Route::post('/trainers/import', [Admin\TrainerController::class, 'import'])->name('trainers.import');
    Route::delete('/trainers/{user}', [Admin\TrainerController::class, 'destroy'])->name('trainers.destroy');
    Route::patch('/trainers/{user}', [Admin\TrainerController::class, 'update'])->name('trainers.update');
    Route::patch('/trainers/{user}/pay-rate', [Admin\TrainerController::class, 'updatePayRate'])->name('trainers.pay-rate');
    Route::get('/email', [Admin\EmailController::class, 'index'])->name('email.index');
    Route::post('/email/send', [Admin\EmailController::class, 'send'])->name('email.send');
    Route::get('/sessions', [Admin\AssignmentController::class, 'index'])->name('sessions.index');
    Route::post('/assignments/run', [Admin\AssignmentController::class, 'run'])->name('assignments.run');
    Route::delete('/availabilities/{availability}', [Admin\AssignmentController::class, 'removeTrainer'])->name('availabilities.destroy');
    Route::post('/sessions/{trainingDay}/add-trainer', [Admin\AssignmentController::class, 'addTrainer'])->name('sessions.add-trainer');
    Route::post('/sessions/{trainingDay}/assign-selected', [Admin\AssignmentController::class, 'assignSelected'])->name('sessions.assign-selected');
    Route::get('/training-plans', [Admin\TrainingPlanController::class, 'index'])->name('training-plans.index');
    Route::post('/training-plans', [Admin\TrainingPlanController::class, 'store'])->name('training-plans.store');
    Route::delete('/training-plans/{trainingPlan}', [Admin\TrainingPlanController::class, 'destroy'])->name('training-plans.destroy');
    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [Admin\NotificationController::class, 'send'])->name('notifications.send');
    Route::get('/sms', [Admin\SmsController::class, 'index'])->name('sms.index');
    Route::post('/sms/send', [Admin\SmsController::class, 'send'])->name('sms.send');
    Route::post('/sms/day/{trainingDay}', [Admin\SmsController::class, 'sendToDay'])->name('sms.send-to-day');
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [Admin\ReportController::class, 'export'])->name('reports.export');

    Route::get('/trainers/{user}/w9', [Admin\W9Controller::class, 'download'])->name('trainers.w9.download');
    Route::post('/trainers/{user}/w9-received', [Admin\W9Controller::class, 'markReceived'])->name('trainers.w9.received');

    Route::get('/admins', [Admin\AdminUserController::class, 'index'])->name('admins.index');
    Route::post('/admins', [Admin\AdminUserController::class, 'store'])->name('admins.store');
    Route::delete('/admins/{user}', [Admin\AdminUserController::class, 'destroy'])->name('admins.destroy');

    Route::get('/documents', [Admin\DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [Admin\DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [Admin\DocumentController::class, 'destroy'])->name('documents.destroy');
});

// ─── Twilio Webhook (no auth, CSRF exempt) ────────────────────────────────
Route::post('/webhooks/twilio/sms', [TwilioController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.twilio.sms');

require __DIR__.'/auth.php';
