<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdviserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

$registerPortalRoutes = function () {
Route::view('/welcome', 'welcome')->name('welcome');
Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/student/login', fn() => app(AuthController::class)->showLogin(request(), 'student'))->name('student.login');
    Route::get('/adviser/login', fn() => app(AuthController::class)->showLogin(request(), 'adviser'))->name('adviser.login');
    Route::get('/admin/login', fn() => app(AuthController::class)->showLogin(request(), 'admin'))->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/classroom-details', [RoomController::class, 'details'])->name('rooms.details');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/rooms/{room}/students/{student}', [ChatController::class, 'openStudent'])->name('chat.room.student');
    Route::get('/chat/rooms/{room}', [ChatController::class, 'openRoom'])->name('chat.room');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.messages');

    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
        Route::get('/activities', [StudentController::class, 'activities'])->name('activities.index');
        Route::get('/activities/{activity}', [StudentController::class, 'showActivity'])->name('activities.show');
        Route::get('/activities/{activity}/instruction-file', [StudentController::class, 'downloadInstruction'])->name('activities.instruction.download');
        Route::get('/activities/{activity}/rubric-file', [StudentController::class, 'downloadRubric'])->name('activities.rubric.download');
        Route::post('/activities/{activity}/submit', [StudentController::class, 'submit'])->name('activities.submit');
        Route::get('/submissions', [StudentController::class, 'submissions'])->name('submissions.index');
        Route::get('/submissions/{submission}', [StudentController::class, 'showSubmission'])->name('submissions.show');
        Route::get('/submissions/{submission}/download', [StudentController::class, 'downloadSubmission'])->name('submissions.download');
        Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
        Route::get('/rooms', [RoomController::class, 'studentIndex'])->name('rooms');
        Route::post('/rooms/join', [RoomController::class, 'join'])->name('rooms.join');
        Route::post('/rooms/preview', [RoomController::class, 'previewEnrollment'])->name('rooms.preview');
        Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    });

    Route::prefix('adviser')->name('adviser.')->middleware('role:adviser')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adviser'])->name('dashboard');
        Route::get('/activities', [AdviserController::class, 'activities'])->name('activities.index');
        Route::get('/activities/create', [AdviserController::class, 'createActivity'])->name('activities.create');
        Route::post('/activities', [AdviserController::class, 'storeActivity'])->name('activities.store');
        Route::get('/activities/{activity}/edit', [AdviserController::class, 'editActivity'])->name('activities.edit');
        Route::put('/activities/{activity}', [AdviserController::class, 'updateActivity'])->name('activities.update');
        Route::get('/activities/{activity}', [AdviserController::class, 'showActivity'])->name('activities.show');
        Route::post('/activities/{activity}/toggle-publish', [AdviserController::class, 'togglePublish'])->name('activities.toggle');
        Route::get('/activities/{activity}/instruction-file', [AdviserController::class, 'downloadInstruction'])->name('activities.instruction.download');
        Route::get('/activities/{activity}/rubric-file', [AdviserController::class, 'downloadRubric'])->name('activities.rubric.download');
        Route::get('/activities/{activity}/submissions', [AdviserController::class, 'submissions'])->name('activities.submissions');
        Route::get('/submissions/{submission}', [AdviserController::class, 'showSubmission'])->name('submissions.show');
        Route::get('/submissions/{submission}/download', [AdviserController::class, 'downloadSubmission'])->name('submissions.download');
        Route::post('/submissions/{submission}/ai', [AdviserController::class, 'runAi'])->name('submissions.ai');
        Route::post('/submissions/{submission}/grade', [AdviserController::class, 'finalizeGrade'])->name('submissions.grade');
        Route::get('/reports', [AdviserController::class, 'reports'])->name('reports');
        Route::get('/scores', [AdviserController::class, 'scores'])->name('scores');
        Route::get('/rooms', [RoomController::class, 'adviserIndex'])->name('rooms.index');
        Route::post('/subjects', [RoomController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{subject}', [RoomController::class, 'updateSubject'])->name('subjects.update');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::post('/rooms/{room}/regenerate-key', [RoomController::class, 'regenerateKey'])->name('rooms.regenerate-key');
        Route::post('/rooms/{room}/toggle', [RoomController::class, 'toggle'])->name('rooms.toggle');
        Route::delete('/rooms/{room}/students/{student}', [RoomController::class, 'removeStudent'])->name('rooms.students.remove');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::post('/users/import', [AdminController::class, 'importUsers'])->name('users.import');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');

        Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('subjects.store');
        Route::get('/subjects/{subject}/edit', [AdminController::class, 'editSubject'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [AdminController::class, 'updateSubject'])->name('subjects.update');
        Route::post('/subjects/{subject}/enroll', [AdminController::class, 'enroll'])->name('subjects.enroll');
        Route::delete('/subjects/{subject}/students/{student}', [AdminController::class, 'unenroll'])->name('subjects.unenroll');

        Route::get('/activities', [AdminController::class, 'activities'])->name('activities');
        Route::get('/activities/{activity}', [AdminController::class, 'showActivity'])->name('activities.show');
        Route::get('/activities/{activity}/instruction-file', [AdminController::class, 'downloadInstruction'])->name('activities.instruction.download');
        Route::get('/activities/{activity}/rubric-file', [AdminController::class, 'downloadRubric'])->name('activities.rubric.download');

        Route::get('/submissions', [AdminController::class, 'submissions'])->name('submissions');
        Route::get('/submissions/{submission}', [AdminController::class, 'showSubmission'])->name('submissions.show');
        Route::get('/submissions/{submission}/download', [AdminController::class, 'downloadSubmission'])->name('submissions.download');
        Route::post('/submissions/{submission}/ai', [AdminController::class, 'runAi'])->name('submissions.ai');

        Route::get('/repository', [AdminController::class, 'repository'])->name('repository');
        Route::get('/repository/{item}', [AdminController::class, 'showRepository'])->name('repository.show');
        Route::get('/repository/{item}/download', [AdminController::class, 'downloadRepository'])->name('repository.download');

        Route::get('/monitor', [AdminController::class, 'monitor'])->name('monitor');
        Route::get('/ai-settings', [AdminController::class, 'aiSettings'])->name('ai-settings');
        Route::post('/ai-settings', [AdminController::class, 'saveAiSettings'])->name('ai-settings.save');
        Route::get('/ai-monitor', [AdminController::class, 'aiMonitor'])->name('ai-monitor');
        Route::post('/ai-monitor/test', [AdminController::class, 'testAiConnection'])->name('ai-monitor.test');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit');
    });
});

};
Route::group([], $registerPortalRoutes);
Route::prefix('workspaces/{workspace}')->where(['workspace' => 'student|adviser|admin'])->name('workspace.')->group($registerPortalRoutes);
