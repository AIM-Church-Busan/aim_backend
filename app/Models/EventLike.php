<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLike extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'planning_center_user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function planningCenterUser(): BelongsTo
    {
        return $this->belongsTo(PlanningCenterUser::class);
    }
}
