<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsVersion extends Model
{
    protected $fillable = [
        'version_label',
        'sections',
        'status',
        'summary',
        'changed_sections',
        'created_by',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'changed_sections' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public static function currentPublished(): self
    {
        return static::query()
            ->where('status', 'published')
            ->latest('id')
            ->firstOrFail();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
