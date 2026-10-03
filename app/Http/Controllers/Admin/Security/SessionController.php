<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Services\Security\SessionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function __construct(
        private readonly SessionManager $sessions,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Session::class);

        $paginator = $this->sessions->paginate(
            userId: $request->integer('user_id') ?: null,
        );

        $currentId = $request->session()->getId();

        $paginator->getCollection()->transform(
            fn (Session $s): array => $this->sessions->describe($s, $currentId),
        );

        return Inertia::render('admin/security/sessions/index', [
            'sessions' => $paginator,
            'filters' => ['user_id' => $request->integer('user_id') ?: null],
        ]);
    }

    public function destroy(Request $request, Session $session): RedirectResponse
    {
        $this->authorize('revoke', $session);

        $currentId = $request->session()->getId();

        if ($session->id === $currentId) {
            return back()->with('error', 'You cannot revoke your own active session.');
        }

        $this->sessions->revoke($session->id, $currentId);

        return back()->with('success', 'Session revoked.');
    }
}
