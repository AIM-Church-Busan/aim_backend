<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
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
        'category',
        'is_pinned',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'starts_at'    => 'date',
        'ends_at'      => 'date',
        'due_date'     => 'date',
        'start_time'   => 'datetime:H:i',
        'end_time'     => 'datetime:H:i',
        'published_at' => 'datetime',
        'is_pinned'    => 'boolean',
        'is_published' => 'boolean',
    ];

    const CATEGORY_GENERAL  = 'general';
    const CATEGORY_CHILDREN = 'children';
    const CATEGORY_OFFERING = 'offering';

    const CATEGORIES = [
        self::CATEGORY_GENERAL  => 'General',
        self::CATEGORY_CHILDREN => 'Children',
        self::CATEGORY_OFFERING => 'Offering',
    ];

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('due_date')
              ->orWhere('due_date', '>=', now()->toDateString());
        });
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
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
}
