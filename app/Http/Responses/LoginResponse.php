<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect post-login based on the user's primary role.
     * Super admins go to /admin, clients to /portal, everyone else to /.
     */
    public function toResponse($request): RedirectResponse|Response|SymfonyResponse
    {
        if ($request->wantsJson()) {
            return response()->noContent();
        }

        $user = Auth::user();
        $route = $user instanceof User
            ? $user->primaryRole()?->dashboardRoute()
            : null;

        return redirect()->intended($route ? route($route) : config('fortify.home'));
    }
}
