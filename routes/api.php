<?php

use App\Http\Controllers\MidtransNotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notifications', MidtransNotificationController::class)
    ->middleware('throttle:60,1')
    ->name('midtrans.notifications');
