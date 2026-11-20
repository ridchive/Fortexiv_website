<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'carbon_footprint_value_at_purchase' => 'decimal:6',
            'carbon_baseline_emission_factor_at_purchase' => 'decimal:6',
            'carbon_contribution' => 'decimal:6',
            'carbon_legacy_product_footprint_total' => 'decimal:6',
            'carbon_quantity_at_purchase' => 'integer',
            'carbon_eligible_at' => 'datetime',
        ];
    }

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

    public function carbonImpactAdjustments(): HasMany
    {
        return $this->hasMany(CarbonImpactAdjustment::class);
    }
}
