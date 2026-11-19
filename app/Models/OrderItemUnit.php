<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemUnit extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['delivered_at' => 'datetime'];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
