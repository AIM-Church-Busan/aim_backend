<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'start_time',
        'end_time',
        'due_date',
        'location',
        'location_address',
        'thumbnail_path',
        'thumbnail_url',
        'capacity',
        'remaining_spots',
        'external_link',
        'google_calendar_event_id',
        'is_published',
        'is_banner'
    ];

    protected $casts = [
        'starts_at'    => 'date',
        'ends_at'      => 'date',
        'due_date'     => 'date',
        'start_time'   => 'datetime:H:i',
        'end_time'     => 'datetime:H:i',
        'is_published' => 'boolean',
        'capacity'     => 'integer',
        'remaining_spots' => 'integer',
    ];

    // ─── Model Events ───────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (!is_null($event->capacity) && is_null($event->remaining_spots)) {
                $event->remaining_spots = $event->capacity;
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    public function likes(): HasMany
    {
        return $this->hasMany(EventLike::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /**
     * Only return published events.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Only return upcoming events (starts_at >= today).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now()->toDateString());
    }

    /**
     * Exclude events whose due_date has passed.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('due_date')
              ->orWhere('due_date', '>=', now()->toDateString());
        });
    }

    // ─── Accessors ────────────────────────────────────────────────

    /**
     * Return thumbnail_path (uploaded file) first, fall back to thumbnail_url.
     */
    public function getThumbnailAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        return $this->thumbnail_url;
    }

    /**
     * Check if the event is full.
     */
    public function getIsFullAttribute(): bool
    {
        if (is_null($this->capacity)) {
            return false;
        }

        return $this->remaining_spots <= 0;
    }
}
