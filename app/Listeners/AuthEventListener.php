<?php

namespace App\Listeners;

use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class AuthEventListener
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected Request $request
    ) {}

    /**
     * Handle successful login events.
     * Requirements: 1.1
     */
    public function handleLogin(Login $event): void
    {
        $this->auditLogService->log(
            action: 'auth.login',
            actor: $event->user,
            subject: $event->user,
            properties: [],
            ipAddress: $this->request->ip(),
            userAgent: $this->request->userAgent()
        );
    }

    /**
     * Handle failed login events.
     * Requirements: 1.2
     */
    public function handleFailed(Failed $event): void
    {
        $this->auditLogService->log(
            action: 'auth.login_failed',
            actor: null,
            subject: $event->user,
            properties: [
                'email' => $event->credentials['email'] ?? null,
            ],
            ipAddress: $this->request->ip(),
            userAgent: $this->request->userAgent()
        );
    }

    /**
     * Handle logout events.
     * Requirements: 1.3
     */
    public function handleLogout(Logout $event): void
    {
        $this->auditLogService->log(
            action: 'auth.logout',
            actor: $event->user,
            subject: $event->user,
            properties: [],
            ipAddress: $this->request->ip(),
            userAgent: $this->request->userAgent()
        );
    }

    /**
     * Handle 2FA success events.
     * Requirements: 1.4
     */
    public function handleTwoFactorSuccess(\App\Events\TwoFactorSuccess $event): void
    {
        $this->auditLogService->log(
            action: 'auth.2fa_success',
            actor: $event->user,
            subject: $event->user,
            properties: [],
            ipAddress: $this->request->ip(),
            userAgent: $this->request->userAgent()
        );
    }

    /**
     * Handle 2FA failed events.
     * Requirements: 1.5
     */
    public function handleTwoFactorFailed(\App\Events\TwoFactorFailed $event): void
    {
        $this->auditLogService->log(
            action: 'auth.2fa_failed',
            actor: $event->user,
            subject: $event->user,
            properties: [],
            ipAddress: $this->request->ip(),
            userAgent: $this->request->userAgent()
        );
    }
}
