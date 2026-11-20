<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(OrderFulfillment::class, 'order_fulfillment_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(OrderItemUnit::class)->orderBy('id');
    }
}
