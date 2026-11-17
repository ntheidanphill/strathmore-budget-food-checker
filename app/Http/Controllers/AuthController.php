<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuthController - login and logout for Vendor Managers and Admins (R2).
 * Students never log in. Passwords are checked by Laravel's Auth::attempt(),
 * which compares against the HASHED password in the database (R5).
 */
class AuthController extends Controller
{
    /** GET /login - show the login form. */
    public function showLogin()
    {
        // Already logged in? Go straight to your own home page.
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    /** POST /login - check username + password. */
    public function login(Request $request): RedirectResponse
    {
        // 1. Server-side validation (API brief 5.4)
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Try to log in. 'is_active' => true means deactivated accounts are refused.
        $attempt = Auth::attempt(
            ['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true],
            $request->boolean('remember')
        );

        if ($attempt) {
            // 3. Regenerate the session id to prevent "session fixation" attacks
            $request->session()->regenerate();

            // 4. Go where the user wanted, or to their role's home page
            return redirect()->intended($this->homeFor(Auth::user()));
        }

        // Same message for every failure so attackers cannot learn which usernames exist
        return back()
            ->withErrors(['username' => 'Invalid username or password, or the account is deactivated.'])
            ->onlyInput('username');
    }

    /** POST /logout - end the session safely. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();       // destroy the session data
        $request->session()->regenerateToken();  // new CSRF token

        return redirect()->route('search')->with('success', 'You have been logged out.');
    }

    /** Where each role lands after login. */
    private function homeFor(User $user): string
    {
        return $user->isAdmin() ? route('admin.dashboard') : route('vendor.dashboard');
    }
}
