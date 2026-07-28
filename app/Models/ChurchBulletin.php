<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Policy:
 * - pdf_path is a storage-disk-relative path. Always resolve the downloadable
 *   URL through the pdf_url accessor rather than concatenating paths
 *   elsewhere in the app.
 */
class ChurchBulletin extends Model
{
    protected $fillable = ['title', 'content', 'pdf_path', 'thumbnail_path', 'thumbnail_url'];

    protected $appends = ['pdf_url'];

    protected $hidden = ['pdf_path', 'thumbnail_path'];

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? Storage::disk('public')->url($this->pdf_path) : null;
    }

    public function getThumbnailUrlAttribute(?string $value): ?string
    {
        return $value ?: ($this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }
}
