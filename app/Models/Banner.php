<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'category',
        'position',
        'order',
        'image_url',
        'url',
        'due_date',
    ];

    protected $casts = [
        'due_date'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 만료되지 않은 배너만 조회
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('due_date')
              ->orWhere('due_date', '>=', now()->toDateString());
        });
    }
}
