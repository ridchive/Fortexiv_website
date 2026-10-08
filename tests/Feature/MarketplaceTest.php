<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_fortexiv_project_showcase_and_only_published_in_stock_products(): void
    {
        $seller = $this->seller();
        Product::create(['seller_id' => $seller->id, 'name' => 'Published tote', 'slug' => 'published-tote', 'description' => 'Handmade tote', 'price' => 14000, 'stock' => 2, 'status' => 'published']);
        Product::create(['seller_id' => $seller->id, 'name' => 'Draft mug', 'slug' => 'draft-mug', 'description' => 'Handmade mug', 'price' => 9000, 'stock' => 2, 'status' => 'draft']);
        Product::create(['seller_id' => $seller->id, 'name' => 'Sold out card', 'slug' => 'sold-out-card', 'description' => 'Handmade card', 'price' => 9000, 'stock' => 0, 'status' => 'published']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('What students are building this term')
            ->assertSee('Published tote')
            ->assertSee('Rp 14.000')
            ->assertDontSee('Draft mug')
            ->assertDontSee('Sold out card');

        $this->get(route('products.show', 'published-tote'))
            ->assertOk()
            ->assertSee('Published tote')
            ->assertSee('Sign in to add to cart');
    }

    public function test_seller_registration_creates_a_seller_profile_without_exposing_admin_role(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Student Seller',
            'email' => 'seller@example.test',
            'role' => 'seller',
            'store_name' => 'Class 3 Makers',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('seller.dashboard'));

        $seller = User::where('email', 'seller@example.test')->firstOrFail();
        $this->assertSame('seller', $seller->role);
        $this->assertSame('Class 3 Makers', $seller->seller->store_name);
        $this->assertTrue(password_verify('secure-password', $seller->password));

        $this->post(route('logout'));
        $this->post(route('register.store'), [
            'name' => 'Injected Admin',
            'email' => 'admin@example.test',
            'role' => 'admin',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertSessionHasErrors('role');
    }

    public function test_buyers_can_sign_in_and_log_out_with_session_regeneration(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer-login@example.test',
            'password' => 'correct-horse-battery-staple',
        ]);

        $this->get(route('login'))->assertOk();
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-horse-battery-staple',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_buyer_can_checkout_upload_transfer_proof_and_receive_single_use_pickup_ticket(): void
    {
        Storage::fake('local');
        config([
            'marketplace.bank_name' => 'BCA',
            'marketplace.account_name' => 'FORTEXIV',
            'marketplace.account_number' => '1234567890',
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'recycled-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 3, 'status' => 'published']);

        $this->actingAs($buyer)->get(route('products.show', $product))->assertOk()->assertSee('Add to cart');
        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])->assertRedirect(route('cart.index'));
        $this->actingAs($buyer)->post(route('checkout'), ['pickup_date' => now()->addDay()->toDateString()])
            ->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertSame('FX-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT), $order->order_code);
        $this->assertSame(28000, $order->total_price);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(14000, $order->items->first()->price_at_order);

        $this->actingAs($buyer)->post(route('payments.store', $order), [
            'method' => 'Transfer BCA',
            'proof' => UploadedFile::fake()->createWithContent('receipt.pdf', "%PDF-1.4\ntransfer receipt\n%%EOF"),
        ])->assertRedirect();
        $this->assertSame('waiting_verification', $order->fresh()->status);

        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $order->fresh()->payment;
        $this->actingAs($admin)->post(route('admin.payments.verify', $payment))->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->ticket);

        $this->actingAs($buyer)->get(route('tickets.show', $order))->assertOk()->assertSee($order->fresh()->ticket->ticket_code);

        $this->actingAs($admin)->post(route('admin.pickup.validate'), ['ticket_code' => $order->fresh()->ticket->ticket_code])->assertRedirect();
        $this->assertSame('picked_up', $order->fresh()->status);
        $this->actingAs($admin)->from(route('admin.pickup'))->post(route('admin.pickup.validate'), ['ticket_code' => $order->fresh()->ticket->ticket_code])->assertSessionHasErrors('ticket_code');
    }

    public function test_rejecting_a_manual_payment_returns_reserved_stock_once(): void
    {
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'seed-kit', 'description' => 'Seeds', 'price' => 7000, 'stock' => 0, 'status' => 'published']);
        $buyer = User::factory()->create();
        $order = Order::create(['order_code' => 'FX-TEST0001', 'buyer_id' => $buyer->id, 'status' => 'waiting_verification', 'total_price' => 7000, 'pickup_date' => now()->addDay()]);
        $order->items()->create(['product_id' => $product->id, 'seller_id' => $seller->id, 'product_name' => $product->name, 'quantity' => 1, 'price_at_order' => 7000, 'subtotal' => 7000]);
        $payment = $order->payment()->create(['proof_image' => 'payment-proofs/receipt.pdf', 'method' => 'Transfer', 'status' => 'pending']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), ['notes' => 'Amount does not match'])->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), ['notes' => 'Duplicate'])->assertUnprocessable();
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_buyers_cannot_access_another_buyers_order_or_admin_routes(): void
    {
        $owner = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $order = Order::create(['order_code' => 'FX-PRIVATE01', 'buyer_id' => $owner->id, 'status' => 'pending_payment', 'total_price' => 1000, 'pickup_date' => now()->addDay()]);

        $this->actingAs($otherBuyer)->get(route('orders.show', $order))->assertNotFound();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_sellers_cannot_edit_another_sellers_product(): void
    {
        $owner = $this->seller('owner@example.test');
        $otherSeller = $this->seller('other@example.test');
        $product = Product::create(['seller_id' => $owner->id, 'name' => 'Private product', 'slug' => 'private-product', 'description' => 'Private', 'price' => 1000, 'stock' => 1, 'status' => 'draft']);

        $this->actingAs($otherSeller->user)->get(route('seller.products.edit', $product))->assertNotFound();
    }

    public function test_buyer_seller_and_admin_pages_render_for_their_own_roles(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->get(route('account'))->assertOk()->assertSee('Account information');
        $this->actingAs($buyer)->get(route('cart.index'))->assertOk();
        $this->actingAs($buyer)->get(route('orders.index'))->assertOk();

        $seller = $this->seller('pages-seller@example.test');
        $this->actingAs($seller->user)->get(route('seller.dashboard'))->assertOk();
        $this->actingAs($seller->user)->get(route('seller.products.index'))->assertOk();
        $this->actingAs($seller->user)->get(route('seller.products.create'))->assertOk();
        $this->actingAs($seller->user)->get(route('seller.orders'))->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.payments'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users'))->assertOk();
        $this->actingAs($admin)->get(route('admin.pickup'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports'))->assertOk();
    }

    public function test_checkout_does_not_create_an_order_when_stock_is_no_longer_available(): void
    {
        config([
            'marketplace.bank_name' => 'BCA',
            'marketplace.account_name' => 'FORTEXIV',
            'marketplace.account_number' => '1234567890',
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Low stock item', 'slug' => 'low-stock-item', 'description' => 'Limited stock', 'price' => 14000, 'stock' => 1, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($buyer)->from(route('cart.index'))->post(route('checkout'), ['pickup_date' => now()->addDay()->toDateString()])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_checkout_is_disabled_until_manual_transfer_details_are_configured(): void
    {
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'seed-kit', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Checkout is temporarily unavailable')
            ->assertDontSee('Place order');
        $this->actingAs($buyer)->post(route('checkout'), ['pickup_date' => now()->addDay()->toDateString()])
            ->assertStatus(503);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    private function seller(string $email = 'seller@example.test'): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'email' => $email]);

        return Seller::create(['user_id' => $user->id, 'store_name' => 'Class Makers']);
    }
}
