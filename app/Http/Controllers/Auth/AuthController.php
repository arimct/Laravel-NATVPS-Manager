<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     * Requirements: 2.1 - Redirect to 2FA challenge if user has 2FA enabled
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user has 2FA enabled
            if ($user->hasTwoFactorEnabled()) {
                // Log out the user - they need to complete 2FA first
                Auth::logout();
                
                // Store user ID and remember preference in session for 2FA challenge
                $request->session()->put('two_factor:user_id', $user->id);
                $request->session()->put('two_factor:remember', $request->boolean('remember'));
                
                // Regenerate session for security
                $request->session()->regenerate();
                
                return redirect()->route('two-factor.challenge');
            }
            
            // No 2FA enabled - complete normal login
            $request->session()->regenerate();
            
            // Flash message before redirect to ensure it persists
            session()->flash('success', __('app.welcome_back', ['name' => $user->name]));

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => __('app.invalid_credentials'),
        ])->onlyInput('email')
            ->with('error', __('app.invalid_credentials'));
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', __('app.logout_success'));
    }
}
