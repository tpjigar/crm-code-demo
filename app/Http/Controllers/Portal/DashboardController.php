<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Render the Client Portal dashboard.
     *
     * Scoped to the authenticated user's client_id — any records loaded
     * later will be filtered automatically by ClientTenantScope.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('portal/dashboard', [
            'stats' => [
                'contacts' => 0,
                'open_incidents' => 0,
                'resolved_incidents' => 0,
            ],
        ]);
    }
}
