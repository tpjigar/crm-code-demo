<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Admin\Security\AuditLogController;
use App\Http\Controllers\Admin\Security\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role.super_admin', 'password.not-expired'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, '__invoke'])
            ->name('dashboard');

        Route::resource('clients', ClientController::class);
        Route::resource('contacts', ContactController::class);
        Route::resource('incidents', IncidentController::class);
        Route::post('incidents/{incident}/transition', [IncidentController::class, 'transition'])
            ->name('incidents.transition');
        Route::post('incidents/{incident}/assign', [IncidentController::class, 'assign'])
            ->name('incidents.assign');

        Route::prefix('security')->name('security.')->group(function (): void {
            Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
            Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
            Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
        });
    });
