<?php

return [
    'bank_name' => env('PAYMENT_BANK_NAME', ''),
    'account_name' => env('PAYMENT_ACCOUNT_NAME', ''),
    'account_number' => env('PAYMENT_ACCOUNT_NUMBER', ''),
    'po_start' => env('MARKETPLACE_PO_START'),
    'po_end' => env('MARKETPLACE_PO_END'),
    'payment_deadline' => env('MARKETPLACE_PAYMENT_DEADLINE'),
    'event_at' => env('MARKETPLACE_EVENT_AT'),
    'event_location' => env('MARKETPLACE_EVENT_LOCATION'),
    'pickup_instructions' => env('MARKETPLACE_PICKUP_INSTRUCTIONS'),
    'banner' => env('MARKETPLACE_BANNER', 'Satu periode PO untuk semua kelas. Semua pesanan diambil langsung di acara, tidak ada pengiriman.'),
    'terms_version' => env('MARKETPLACE_TERMS_VERSION', 'prototype-1.0'),
];
