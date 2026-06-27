<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningCenterUser extends Model
{
    protected $fillable = [
        'planning_center_id',
        'name',
        'email',
        'avatar_url',
        'role',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function lifeGroups(): HasMany
    {
        return $this->hasMany(UserLifeGroup::class);
    }

    public function eventLikes(): HasMany
    {
        return $this->hasMany(EventLike::class);
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Check if the user is a leader in any Life Group.
     */
    public function isLifeGroupLeader(): bool
    {
        return $this->lifeGroups()->where('role', 'leader')->exists();
    }

    /**
     * Get the user's Life Group names.
     */
    public function getLifeGroupNamesAttribute(): array
    {
        return $this->lifeGroups->pluck('life_group_name')->toArray();
    }
}
