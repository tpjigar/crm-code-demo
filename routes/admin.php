<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IncidentController;
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
    });
