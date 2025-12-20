<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    /**
     * Number of recovery codes to generate.
     */
    public const RECOVERY_CODE_COUNT = 8;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for 2FA setup.
     * 
     * @return string The generated secret key
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for authenticator app.
     * 
     * @param User $user The user to generate QR code for
     * @param string $secret The secret key
     * @return string The QR code as SVG data URI
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        $appName = config('app.name', 'Laravel');
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $appName,
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrCodeUrl);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Verify a TOTP code against user's secret.
     * Accepts codes within a reasonable time window to account for clock drift.
     * 
     * @param User $user The user to verify code for
     * @param string $code The TOTP code to verify
     * @return bool True if code is valid, false otherwise
     */
    public function verifyCode(User $user, string $code): bool
    {
        $secret = $user->getTwoFactorSecret();
        
        if (!$secret) {
            return false;
        }

        // Set window to 1 (allows ±30 seconds for clock drift)
        // Each window is 30 seconds, so window=1 means current + 1 previous + 1 next
        $this->google2fa->setWindow(1);

        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Verify a TOTP code against a specific secret.
     * Used during setup before the secret is saved to the user.
     * 
     * @param string $secret The secret key
     * @param string $code The TOTP code to verify
     * @return bool True if code is valid, false otherwise
     */
    public function verifyCodeWithSecret(string $secret, string $code): bool
    {
        // Set window to 1 (allows ±30 seconds for clock drift)
        $this->google2fa->setWindow(1);

        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Get the current valid TOTP code for a secret.
     * Useful for testing purposes.
     * 
     * @param string $secret The secret key
     * @return string The current TOTP code
     */
    public function getCurrentCode(string $secret): string
    {
        return $this->google2fa->getCurrentOtp($secret);
    }

    /**
     * Generate recovery codes for 2FA backup.
     * Creates 8 unique recovery codes.
     * 
     * @return array<string> Array of plain text recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            // Generate a unique recovery code in format: XXXX-XXXX-XXXX
            $codes[] = $this->generateSingleRecoveryCode();
        }
        
        return $codes;
    }

    /**
     * Generate a single recovery code.
     * 
     * @return string Recovery code in format XXXX-XXXX-XXXX
     */
    protected function generateSingleRecoveryCode(): string
    {
        return sprintf(
            '%s-%s-%s',
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4))
        );
    }

    /**
     * Verify and consume a recovery code.
     * If valid, the code is removed from the user's available codes.
     * 
     * @param User $user The user to verify recovery code for
     * @param string $code The recovery code to verify
     * @return bool True if code is valid and consumed, false otherwise
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $storedCodes = $user->getRecoveryCodes();
        
        if (empty($storedCodes)) {
            return false;
        }

        // Normalize the input code (uppercase, trim)
        $normalizedCode = Str::upper(trim($code));

        foreach ($storedCodes as $index => $hashedCode) {
            if (Hash::check($normalizedCode, $hashedCode)) {
                // Remove the used code
                unset($storedCodes[$index]);
                
                // Re-index and save remaining codes
                $user->setRecoveryCodes(array_values($storedCodes), false);
                
                return true;
            }
        }

        return false;
    }

    /**
     * Get the count of remaining recovery codes for a user.
     * 
     * @param User $user The user to check
     * @return int Number of remaining recovery codes
     */
    public function getRemainingRecoveryCodesCount(User $user): int
    {
        return count($user->getRecoveryCodes());
    }

    /**
     * Regenerate recovery codes for a user.
     * Generates new codes and invalidates all previous codes.
     * 
     * @param User $user The user to regenerate codes for
     * @return array<string> Array of new plain text recovery codes
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $newCodes = $this->generateRecoveryCodes();
        
        // Hash and store the new codes
        $user->setRecoveryCodes($newCodes);
        
        return $newCodes;
    }

    /**
     * Enable 2FA for a user.
     * Saves the encrypted secret and generates recovery codes.
     * 
     * @param User $user The user to enable 2FA for
     * @param string $secret The secret key to save
     * @return array<string> Array of plain text recovery codes
     */
    public function enable(User $user, string $secret): array
    {
        // Encrypt and save the secret
        $user->two_factor_secret = encrypt($secret);
        
        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        
        // Hash and store recovery codes
        $user->setRecoveryCodes($recoveryCodes);
        
        // Mark 2FA as confirmed
        $user->two_factor_confirmed_at = now();
        $user->save();
        
        return $recoveryCodes;
    }

    /**
     * Disable 2FA for a user.
     * Clears all 2FA data including secret and recovery codes.
     * 
     * @param User $user The user to disable 2FA for
     * @return void
     */
    public function disable(User $user): void
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
    }
}
