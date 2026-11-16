<?php

namespace Tests\Feature\Console;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncMidtransPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_URL = 'https://api.sandbox.midtrans.com/v2/';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.status_url' => 'https://api.sandbox.midtrans.com',
            'services.midtrans.status_check_batch_size' => 25,
        ]);
    }

    public function test_settled_pending_order_becomes_paid_and_gets_one_ticket(): void
    {
        $order = $this->pendingMidtransOrder('FX-SYNC0001');
        Http::preventStrayRequests();
        Http::fake([self::STATUS_URL.'FX-SYNC0001/status' => Http::response($this->midtransStatusResponse($order, 'settlement'))]);

        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'verified', 'method' => 'qris']);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_expired_pending_order_is_cancelled(): void
    {
        $order = $this->pendingMidtransOrder('FX-SYNC0001');
        Http::preventStrayRequests();
        Http::fake([self::STATUS_URL.'FX-SYNC0001/status' => Http::response($this->midtransStatusResponse($order, 'expire'))]);

        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);
    }

    public function test_orders_without_a_snap_transaction_or_with_manual_payment_are_not_checked(): void
    {
        $withoutSnap = $this->pendingMidtransOrder('FX-SYNC0001', snapRedirectUrl: null);
        $manual = $this->pendingMidtransOrder('FX-SYNC0002');
        $manual->payment()->update(['gateway' => Payment::GATEWAY_MANUAL]);
        Http::preventStrayRequests();
        Http::fake();

        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame('pending_payment', $withoutSnap->fresh()->status);
        $this->assertSame('pending_payment', $manual->fresh()->status);
    }

    public function test_batch_size_limits_each_run_and_least_recently_checked_orders_go_first(): void
    {
        config(['services.midtrans.status_check_batch_size' => 1]);
        $this->freezeTime();
        $first = $this->pendingMidtransOrder('FX-SYNC0001');
        $second = $this->pendingMidtransOrder('FX-SYNC0002');
        $first->payment()->update(['updated_at' => now()->subMinutes(10)]);
        $second->payment()->update(['updated_at' => now()->subMinutes(20)]);
        Http::preventStrayRequests();
        Http::fake([
            self::STATUS_URL.'FX-SYNC0001/status' => Http::response($this->midtransStatusResponse($first, 'pending')),
            self::STATUS_URL.'FX-SYNC0002/status' => Http::response($this->midtransStatusResponse($second, 'pending')),
        ]);

        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();
        Http::assertSentCount(1);
        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === self::STATUS_URL.'FX-SYNC0002/status');

        $this->travel(1)->minutes();
        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();
        Http::assertSentCount(2);
        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === self::STATUS_URL.'FX-SYNC0001/status');
    }

    public function test_unknown_midtrans_transaction_keeps_the_order_pending_and_the_run_succeeds(): void
    {
        $order = $this->pendingMidtransOrder('FX-SYNC0001');
        Http::preventStrayRequests();
        Http::fake([self::STATUS_URL.'FX-SYNC0001/status' => Http::response(['status_code' => '404', 'status_message' => "Transaction doesn't exist."], 404)]);

        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
    }

    public function test_nothing_is_checked_without_a_server_key(): void
    {
        config(['services.midtrans.server_key' => null]);
        $this->pendingMidtransOrder('FX-SYNC0001');
        Http::preventStrayRequests();
        Http::fake();

        $this->artisan('marketplace:sync-midtrans-payments')->assertSuccessful();

        Http::assertNothingSent();
    }

    private function pendingMidtransOrder(string $orderCode, ?string $snapRedirectUrl = 'https://app.sandbox.midtrans.com/snap/v4/redirection/token'): Order
    {
        $order = Order::create([
            'order_code' => $orderCode,
            'buyer_id' => User::factory()->create()->id,
            'status' => 'pending_payment',
            'total_price' => 14000,
            'pickup_date' => now()->addDay(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'pending',
            'snap_token' => $snapRedirectUrl ? 'token' : null,
            'snap_redirect_url' => $snapRedirectUrl,
        ]);

        return $order;
    }

    /**
     * @return array<string, string>
     */
    private function midtransStatusResponse(Order $order, string $transactionStatus): array
    {
        return [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '14000.00',
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-'.$order->order_code,
        ];
    }
}
