<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;  // 이게 빠진 것!

class PlanningCenterUser extends Model
{
    use HasApiTokens;

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

    public function isLifeGroupLeader(): bool
    {
        return $this->lifeGroups()->where('role', 'leader')->exists();
    }

    public function getLifeGroupNamesAttribute(): array
    {
        return $this->lifeGroups->pluck('life_group_name')->toArray();
    }
}
