<?php

namespace App\Http\Controllers\Auth;

use App\Events\TwoFactorFailed;
use App\Events\TwoFactorSuccess;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Show the 2FA challenge form.
     * Requirements: 2.1, 2.4
     */
    public function show(Request $request): View|RedirectResponse
    {
        // Check if there's a user awaiting 2FA verification
        $userId = $request->session()->get('two_factor:user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget('two_factor:user_id');
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify the TOTP code and complete login.
     * Requirements: 2.2, 2.3
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('two_factor:user_id');
        
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', __('app.2fa_session_expired'));
        }

        $user = User::find($userId);
        
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget('two_factor:user_id');
            return redirect()->route('login');
        }

        // Verify the TOTP code
        if (!$this->twoFactorService->verifyCode($user, $request->code)) {
            TwoFactorFailed::dispatch($user);
            return back()->with('error', __('app.2fa_invalid_code'));
        }

        // Dispatch 2FA success event
        TwoFactorSuccess::dispatch($user);

        // Complete the login
        $this->completeLogin($request, $user);

        return redirect()->intended('dashboard')
            ->with('success', __('app.welcome_back', ['name' => $user->name]));
    }

    /**
     * Verify a recovery code and complete login.
     * Requirements: 3.1, 3.2, 3.3
     */
    public function verifyRecovery(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('two_factor:user_id');
        
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', __('app.2fa_session_expired'));
        }

        $user = User::find($userId);
        
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget('two_factor:user_id');
            return redirect()->route('login');
        }

        // Verify and consume the recovery code
        if (!$this->twoFactorService->verifyRecoveryCode($user, $request->recovery_code)) {
            TwoFactorFailed::dispatch($user);
            return back()->with('error', __('app.2fa_invalid_recovery'));
        }

        // Dispatch 2FA success event
        TwoFactorSuccess::dispatch($user);

        // Complete the login
        $this->completeLogin($request, $user);

        // Get remaining recovery codes count and show warning if low
        $remainingCount = $this->twoFactorService->getRemainingRecoveryCodesCount($user);
        
        $redirect = redirect()->intended('dashboard')
            ->with('success', __('app.welcome_back', ['name' => $user->name]));

        // Show warning about remaining recovery codes
        if ($remainingCount > 0) {
            $redirect->with('info', __('app.2fa_codes_remaining', ['count' => $remainingCount]));
        }

        // Show prominent warning if fewer than 3 codes remaining
        if ($remainingCount < 3) {
            $redirect->with('warning', __('app.2fa_codes_warning'));
        }

        return $redirect;
    }

    /**
     * Complete the login process after 2FA verification.
     */
    protected function completeLogin(Request $request, User $user): void
    {
        // Clear the pending 2FA user ID
        $request->session()->forget('two_factor:user_id');
        
        // Log the user in
        Auth::login($user, $request->session()->get('two_factor:remember', false));
        
        // Clear the remember flag
        $request->session()->forget('two_factor:remember');
        
        // Mark session as 2FA authenticated
        $request->session()->put('two_factor:authenticated', true);
        
        // Regenerate session for security
        $request->session()->regenerate();
    }
}
