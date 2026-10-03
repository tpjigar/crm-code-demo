<?php

declare(strict_types=1);

use App\Http\Controllers\Portal\ContactController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\IncidentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role.client', 'password.not-expired'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, '__invoke'])
            ->name('dashboard');

        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');

        Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
        Route::get('incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
        Route::post('incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::get('incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    });
