<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const GATEWAY_MANUAL = 'manual';

    public const GATEWAY_MIDTRANS = 'midtrans';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
