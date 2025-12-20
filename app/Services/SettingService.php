<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * Get app name.
     */
    public function appName(): string
    {
        return Setting::get('app_name', config('app.name'));
    }

    /**
     * Get app logo URL.
     */
    public function appLogo(): ?string
    {
        $logo = Setting::get('app_logo');
        return $logo ? Storage::url($logo) : null;
    }

    /**
     * Get app favicon URL.
     */
    public function appFavicon(): ?string
    {
        $favicon = Setting::get('app_favicon');
        return $favicon ? Storage::url($favicon) : null;
    }

    /**
     * Check if email is enabled.
     */
    public function isMailEnabled(): bool
    {
        return Setting::get('mail_enabled', false);
    }

    /**
     * Get mail configuration array.
     */
    public function getMailConfig(): array
    {
        return [
            'host' => Setting::get('mail_host', 'smtp.gmail.com'),
            'port' => Setting::get('mail_port', 587),
            'username' => Setting::get('mail_username'),
            'password' => Setting::get('mail_password'),
            'encryption' => Setting::get('mail_encryption', 'tls'),
            'from' => [
                'address' => Setting::get('mail_from_address', 'noreply@example.com'),
                'name' => Setting::get('mail_from_name', 'NAT VPS Manager'),
            ],
        ];
    }

    /**
     * Check if notification is enabled.
     */
    public function isNotificationEnabled(string $type): bool
    {
        if (!$this->isMailEnabled()) {
            return false;
        }

        return Setting::get('notify_' . $type, false);
    }

    /**
     * Update settings by group.
     */
    public function updateByGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
    }

    /**
     * Upload file setting.
     */
    public function uploadFile(string $key, $file): string
    {
        // Delete old file if exists
        $oldFile = Setting::get($key);
        if ($oldFile && Storage::exists($oldFile)) {
            Storage::delete($oldFile);
        }

        // Store new file
        $path = $file->store('settings', 'public');
        Setting::set($key, $path);

        return $path;
    }
}
