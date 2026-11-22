<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'terms_accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function effectivePaymentDeadline(): ?Carbon
    {
        if ($this->expires_at) {
            return $this->expires_at;
        }

        $timeoutHours = config('marketplace.payment_timeout_hours', 24);

        return $this->created_at ? $this->created_at->copy()->addHours($timeoutHours) : null;
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function termsVersion(): BelongsTo
    {
        return $this->belongsTo(TermsVersion::class, 'terms_version_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(OrderFulfillment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }
}
