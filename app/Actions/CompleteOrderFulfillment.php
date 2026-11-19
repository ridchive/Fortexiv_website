<?php

namespace App\Actions;

use App\Models\OrderFulfillment;
use App\Models\Seller;
use App\Models\User;

class CompleteOrderFulfillment
{
    public function __construct(private CompleteOrderQr $completeOrderQr) {}

    public function handle(string $pickupCode, Seller $seller, User $actor): OrderFulfillment
    {
        return $this->completeOrderQr->completeClass(strtoupper(trim($pickupCode)), $seller, $actor);
    }
}
