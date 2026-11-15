<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_start',
        'po_end',
        'payment_deadline',
        'event_at',
        'event_location',
        'pickup_instructions',
        'banner',
        'change_reason',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'po_start' => 'datetime',
            'po_end' => 'datetime',
            'payment_deadline' => 'datetime',
            'event_at' => 'datetime',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): ?self
    {
        return static::query()->with('updater')->latest('id')->first();
    }
}
