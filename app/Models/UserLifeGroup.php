<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLifeGroup extends Model
{
    protected $fillable = [
        'planning_center_user_id',
        'life_group_id',
        'life_group_name',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function planningCenterUser(): BelongsTo
    {
        return $this->belongsTo(PlanningCenterUser::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }
}
