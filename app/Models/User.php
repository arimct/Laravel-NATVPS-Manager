<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'locale',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * Check if 2FA is enabled for this user.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get decrypted 2FA secret.
     */
    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }

    /**
     * Get recovery codes as array.
     * Returns the hashed codes stored in the database.
     * 
     * @return array<string> Array of hashed recovery codes
     */
    public function getRecoveryCodes(): array
    {
        return $this->two_factor_recovery_codes ?? [];
    }

    /**
     * Set recovery codes.
     * Hashes plain text codes before storing (unless already hashed).
     * 
     * @param array<string> $codes Array of recovery codes
     * @param bool $hash Whether to hash the codes (true for plain text, false for already hashed)
     * @return void
     */
    public function setRecoveryCodes(array $codes, bool $hash = true): void
    {
        if ($hash) {
            // Hash each plain text code before storing
            $hashedCodes = array_map(
                fn(string $code) => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::upper(trim($code))),
                $codes
            );
        } else {
            // Codes are already hashed
            $hashedCodes = $codes;
        }

        $this->two_factor_recovery_codes = $hashedCodes;
        $this->save();
    }

    /**
     * Get the NAT VPS instances assigned to this user.
     */
    public function natVps(): HasMany
    {
        return $this->hasMany(NatVps::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
