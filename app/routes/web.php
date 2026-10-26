<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\StaffSessionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Federation;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Officer;
use App\Http\Controllers\PublicEvidenceController;
use App\Http\Controllers\ReportPrintController;
use App\Http\Controllers\TransparencyController;
use App\Http\Controllers\Youth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/privacy', 'privacy')->name('privacy');
Route::get('/transparency', [TransparencyController::class, 'index'])->name('transparency.index');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/transparency/evidence/{evidence}', [PublicEvidenceController::class, 'show'])->name('transparency.evidence');
Route::get('/transparency/{barangay}', [TransparencyController::class, 'show'])->name('transparency.show');

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
    Route::middleware('role:youth')->group(function () {
        Route::get('/dashboard', Youth\DashboardController::class)->name('dashboard');
        Route::post('/events/{event}/registration', [Youth\EventRegistrationController::class, 'store'])->name('events.registration.store');
        Route::delete('/events/{event}/registration', [Youth\EventRegistrationController::class, 'destroy'])->name('events.registration.destroy');
        Route::post('/feedback', [Youth\FeedbackController::class, 'store'])->name('feedback.store');
        Route::get('/surveys/{survey}', [Youth\SurveyResponseController::class, 'create'])->name('surveys.answer');
        Route::post('/surveys/{survey}', [Youth\SurveyResponseController::class, 'store'])->name('surveys.responses.store');
    });

    Route::middleware('role:officer')->prefix('officer')->name('officer.')->group(function () {
        Route::get('/', Officer\DashboardController::class)->name('dashboard');
        Route::get('plans', [Officer\PlanController::class, 'index'])->name('plans.index');

        Route::resource('cbydps', Officer\CbydpController::class)->only(['create', 'store', 'show']);
        Route::post('cbydps/{cbydp}/priorities', [Officer\CbydpPriorityController::class, 'store'])->name('cbydps.priorities.store');
        Route::delete('cbydps/{cbydp}/priorities/{priority}', [Officer\CbydpPriorityController::class, 'destroy'])
            ->scopeBindings()
            ->name('cbydps.priorities.destroy');
        Route::post('cbydps/{cbydp}/approval', [Officer\CbydpApprovalController::class, 'store'])->name('cbydps.approval.store');
        Route::get('cbydps/{cbydp}/resolution', [Officer\CbydpApprovalController::class, 'show'])->name('cbydps.resolution');

        Route::resource('abyips', Officer\AbyipController::class)->only(['create', 'store', 'show']);
        Route::post('abyips/{abyip}/approval', [Officer\AbyipApprovalController::class, 'store'])->name('abyips.approval.store');
        Route::get('abyips/{abyip}/resolution', [Officer\AbyipApprovalController::class, 'show'])->name('abyips.resolution');
        Route::post('abyips/{abyip}/publication', [Officer\AbyipPublicationController::class, 'store'])->name('abyips.publication.store');
        Route::delete('abyips/{abyip}/publication', [Officer\AbyipPublicationController::class, 'destroy'])->name('abyips.publication.destroy');
        Route::post('abyips/{abyip}/amendments', [Officer\AbyipAmendmentController::class, 'store'])->name('abyips.amendments.store');
        Route::post('abyips/{abyip}/amendments/approval', [Officer\AbyipAmendmentController::class, 'approve'])->name('abyips.amendments.approve');
        Route::get('amendments/{amendment}/resolution', [Officer\AbyipAmendmentController::class, 'resolution'])->name('amendments.resolution');
        Route::get('abyips/{abyip}/report', [Officer\AccomplishmentReportController::class, 'show'])->name('abyips.report');
        Route::get('abyips/{abyip}/report/print', [Officer\AccomplishmentReportController::class, 'print'])->name('abyips.report.print');
        Route::post('abyips/{abyip}/report', [Officer\AccomplishmentReportController::class, 'store'])->name('abyips.report.store');
        Route::get('disclosure', [Officer\DisclosureController::class, 'index'])->name('disclosure.index');

        Route::resource('events', Officer\EventController::class)->only(['index', 'create', 'store', 'show']);
        Route::patch('events/{event}/registrations/{registration}', [Officer\EventAttendanceController::class, 'update'])
            ->scopeBindings()
            ->name('events.registrations.update');
        Route::get('feedback', [Officer\FeedbackController::class, 'index'])->name('feedback.index');
        Route::resource('surveys', Officer\SurveyController::class)->only(['index', 'store', 'show']);
        Route::post('surveys/{survey}/questions', [Officer\SurveyQuestionController::class, 'store'])->name('surveys.questions.store');
        Route::delete('surveys/{survey}/questions/{question}', [Officer\SurveyQuestionController::class, 'destroy'])
            ->scopeBindings()
            ->name('surveys.questions.destroy');
        Route::patch('surveys/{survey}/status', [Officer\SurveyStatusController::class, 'update'])->name('surveys.status.update');
        Route::patch('feedback/{feedback}', [Officer\FeedbackController::class, 'update'])->name('feedback.update');

        Route::get('ppas', [Officer\PpaController::class, 'index'])->name('ppas.index');
        Route::get('ppas-export', [Officer\ExportController::class, 'ppas'])->name('ppas.export');
        Route::get('events/{event}/registrants-export', [Officer\ExportController::class, 'eventRegistrations'])->name('events.export');
        Route::get('abyips/{abyip}/ppas/create', [Officer\PpaController::class, 'create'])->name('ppas.create');
        Route::post('abyips/{abyip}/ppas', [Officer\PpaController::class, 'store'])->name('ppas.store');
        Route::resource('ppas', Officer\PpaController::class)->only(['show', 'edit', 'update', 'destroy']);
        Route::patch('ppas/{ppa}/status', [Officer\PpaStatusController::class, 'update'])->name('ppas.status.update');
        Route::patch('ppas/{ppa}/spending', [Officer\PpaSpendingController::class, 'update'])->name('ppas.spending.update');
        Route::post('ppas/{ppa}/milestones', [Officer\MilestoneController::class, 'store'])->name('ppas.milestones.store');
        Route::patch('ppas/{ppa}/milestones/{milestone}', [Officer\MilestoneController::class, 'update'])
            ->scopeBindings()
            ->name('ppas.milestones.update');
        Route::post('ppas/{ppa}/evidence', [Officer\EvidenceController::class, 'store'])->name('ppas.evidence.store');
        Route::scopeBindings()->group(function () {
            Route::get('ppas/{ppa}/evidence/{evidence}', [Officer\EvidenceController::class, 'show'])->name('ppas.evidence.show');
            Route::patch('ppas/{ppa}/evidence/{evidence}', [Officer\EvidenceController::class, 'update'])->name('ppas.evidence.update');
            Route::delete('ppas/{ppa}/evidence/{evidence}', [Officer\EvidenceController::class, 'destroy'])->name('ppas.evidence.destroy');
        });

        Route::resource('meetings', Officer\MeetingController::class)->only(['index', 'create', 'store', 'show']);
        Route::put('meetings/{meeting}/attendance', [Officer\MeetingAttendanceController::class, 'update'])->name('meetings.attendance.update');
        Route::post('meetings/{meeting}/minutes', [Officer\MeetingMinutesController::class, 'store'])->name('meetings.minutes.store');
        Route::get('meetings/{meeting}/minutes', [Officer\MeetingMinutesController::class, 'show'])->name('meetings.minutes.show');
        Route::resource('resolutions', Officer\ResolutionController::class)->only(['index', 'store', 'show']);
    });

    Route::middleware('role:federation')->prefix('federation')->name('federation.')->group(function () {
        Route::get('/', Federation\DashboardController::class)->name('dashboard');
        Route::get('compliance-export', [Federation\DashboardController::class, 'export'])->name('compliance.export');
        Route::get('barangays/{barangay}', [Federation\BarangayController::class, 'show'])->name('barangays.show');
        Route::post('barangays/{barangay}/remarks', [Federation\MonitoringRemarkController::class, 'store'])->name('barangays.remarks.store');
        Route::get('reports', [Federation\AccomplishmentReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [Federation\AccomplishmentReportController::class, 'show'])->name('reports.show');
        Route::patch('reports/{report}', [Federation\AccomplishmentReportController::class, 'update'])->name('reports.update');
    });

    Route::middleware('role:administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);
        Route::get('barangays', [Admin\BarangayController::class, 'index'])->name('barangays.index');
        Route::patch('barangays/{barangay}', [Admin\BarangayController::class, 'update'])->name('barangays.update');
        Route::get('audit-trail', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('backups', [Admin\BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [Admin\BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{backup}', [Admin\BackupController::class, 'show'])->name('backups.show');
        Route::delete('backups/{backup}', [Admin\BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('backups/{backup}/restore', [Admin\BackupController::class, 'restore'])->name('backups.restore');
        Route::get('audit-trail/export', [Admin\AuditLogController::class, 'export'])->name('audit-logs.export');
        Route::get('users-export', Admin\UserExportController::class)->name('users.export');
    });

    Route::get('/reports/{report}/print', [ReportPrintController::class, 'show'])->middleware('role:officer,federation')->name('reports.print');

    Route::get('/account', [Account\ProfileController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [Account\ProfileController::class, 'update'])->middleware('throttle:6,1')->name('account.update');
    Route::put('/account/password', [Account\PasswordController::class, 'update'])->middleware('throttle:6,1')->name('account.password.update');
    Route::get('/account/data', Account\PersonalDataController::class)->middleware('role:youth')->name('account.data');
    Route::delete('/account', [Account\ProfileController::class, 'destroy'])->middleware(['role:youth', 'throttle:6,1'])->name('account.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/read', [NotificationController::class, 'update'])->name('notifications.read');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
