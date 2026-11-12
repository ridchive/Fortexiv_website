<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans): JsonResponse
    {
        $notification = $request->validate([
            'order_id' => ['required', 'string', 'max:24'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'string', 'max:20'],
            'signature_key' => ['required', 'string', 'size:128'],
            'transaction_status' => ['required', 'string', 'max:32'],
            'fraud_status' => ['nullable', 'string', 'max:20'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        abort_unless($midtrans->hasValidSignature($notification), 403, 'Invalid Midtrans signature.');

        $transaction = $midtrans->getTransactionStatus($notification['order_id']);
        $midtrans->handleNotification($notification, $transaction);

        return response()->json(['status' => 'ok']);
    }
}
