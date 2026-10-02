<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Render the Super Admin dashboard.
     *
     * Metrics are placeholders — real values land once the Clients /
     * Contacts / Incidents models exist in later branches. Keeping the
     * shape stable here means the React page contract doesn't change
     * when the data goes live.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('admin/dashboard', [
            'stats' => [
                'clients' => 0,
                'contacts' => 0,
                'open_incidents' => 0,
                'critical_incidents' => 0,
            ],
        ]);
    }
}
