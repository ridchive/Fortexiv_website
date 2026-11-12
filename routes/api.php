<?php

use App\Http\Controllers\MidtransNotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notifications', MidtransNotificationController::class)
    ->name('midtrans.notifications');
