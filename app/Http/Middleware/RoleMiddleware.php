<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware - the "bouncer" at the door of protected pages.
 * Used in routes like:  ->middleware(['auth', 'role:admin,vendor_manager'])
 * Everything after "role:" is the list of roles that may pass.
 *
 * Why it matters (R5 / API brief 5.6): permissions are enforced by the SERVER.
 * Even if someone types an admin URL by hand, this check still blocks them.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Not logged in at all -> send to the login page
        if (! $user) {
            return redirect()->route('login');
        }

        // Account was deactivated by the Admin while logged in -> kick them out now (R4)
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Your account has been deactivated. Contact the administrator.']);
        }

        // Logged in but wrong role -> 403 Forbidden
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have permission to open this page.');
        }

        return $next($request);
    }
}
