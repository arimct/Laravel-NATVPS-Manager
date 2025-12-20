<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * We only use created_at, no updated_at (append-only).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'action',
        'actor_id',
        'actor_type',
        'subject_id',
        'subject_type',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and add immutability constraints.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automatically set created_at on creation (server-side timestamp)
        static::creating(function (AuditLog $model) {
            $model->created_at = now();
        });

        // Prevent updates - append-only storage
        static::updating(function (AuditLog $model) {
            return false;
        });

        // Only allow deletion via console (cleanup command)
        static::deleting(function (AuditLog $model) {
            if (!app()->runningInConsole()) {
                return false;
            }
        });
    }

    /**
     * Get the actor (user who performed the action).
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subject (entity affected by the action).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
