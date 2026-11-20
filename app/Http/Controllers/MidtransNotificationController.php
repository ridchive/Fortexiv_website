<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans): JsonResponse
    {
        Log::info('Midtrans webhook HTTP request received.', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $notification = $request->validate([
            'order_id' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'string', 'max:20'],
            'signature_key' => ['required', 'string', 'size:128'],
            'transaction_status' => ['required', 'string', 'max:32'],
            'fraud_status' => ['nullable', 'string', 'max:20'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        if (! $midtrans->hasValidSignature($notification)) {
            Log::warning('Midtrans notification rejected due to an invalid signature.', [
                'order_id' => $notification['order_id'],
            ]);

            return response()->json(['message' => 'Invalid Midtrans notification signature.'], 401);
        }

        Log::info('Midtrans notification received.', [
            'order_id' => $notification['order_id'],
            'transaction_id' => $notification['transaction_id'] ?? null,
            'status_code' => $notification['status_code'],
            'transaction_status' => $notification['transaction_status'],
            'payment_type' => $notification['payment_type'] ?? null,
        ]);

        $transaction = $midtrans->getTransactionStatus($notification['order_id']);
        $midtrans->handleNotification($notification, $transaction);

        return response()->json(['status' => 'ok']);
    }
}
