<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarbonImpactAdjustment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'previous_contribution' => 'decimal:6',
            'corrected_contribution' => 'decimal:6',
            'adjustment' => 'decimal:6',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
