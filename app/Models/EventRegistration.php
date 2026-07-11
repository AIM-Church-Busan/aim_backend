<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'planning_center_user_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_NOT_REGISTERED = 'not_registered';
    const STATUS_REGISTERED     = 'registered';
    const STATUS_CANCELLED      = 'cancelled';

    // ─── Relationships ────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function planningCenterUser(): BelongsTo
    {
        return $this->belongsTo(PlanningCenterUser::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeNotRegistered($query)
    {
        return $query->where('status', self::STATUS_NOT_REGISTERED);
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', self::STATUS_REGISTERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isNotRegistered(): bool
    {
        return $this->status === self::STATUS_NOT_REGISTERED;
    }

    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
