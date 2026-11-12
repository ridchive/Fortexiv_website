<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_fortexiv_project_showcase_and_catalog_hides_unpublished_products(): void
    {
        $project = Project::create([
            'class_name' => 'Class 12A',
            'grade' => 12,
            'title' => 'Green Economy',
            'introduction' => 'A project introduction.',
            'story' => 'A project story.',
            'outcome' => 'A project outcome.',
        ]);
        $seller = $this->seller();
        Product::create(['seller_id' => $seller->id, 'name' => 'Published tote', 'slug' => 'published-tote', 'description' => 'Handmade tote', 'price' => 14000, 'stock' => 2, 'status' => 'published']);
        Product::create(['seller_id' => $seller->id, 'name' => 'Draft mug', 'slug' => 'draft-mug', 'description' => 'Handmade mug', 'price' => 9000, 'stock' => 2, 'status' => 'draft']);
        Product::create(['seller_id' => $seller->id, 'name' => 'Sold out card', 'slug' => 'sold-out-card', 'description' => 'Handmade card', 'price' => 9000, 'stock' => 0, 'status' => 'published']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Konsumsi Bijak, Bumi Lestari.')
            ->assertSee('Catatan dari tim kelas')
            ->assertSee('Aula Utama SAI');

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Published tote')
            ->assertSee('Sold out card')
            ->assertDontSee('Draft mug')
            ->assertSee('Rp 14.000');

        $this->get(route('products.show', 'published-tote'))
            ->assertOk()
            ->assertSee('Published tote')
            ->assertSee('Masuk untuk memesan');
    }

    public function test_homepage_links_use_the_forwarded_https_host_in_local_environments(): void
    {
        $this->get('/', [
            'X-Forwarded-Host' => 'fortexiv.example.test',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ])
            ->assertOk()
            ->assertSee('href="https://fortexiv.example.test/projects"', false);
    }

    public function test_homepage_shows_all_published_project_stories(): void
    {
        foreach (range(1, 5) as $number) {
            Project::create([
                'class_name' => "Class 12{$number}",
                'grade' => 12,
                'title' => "Project Story {$number}",
                'introduction' => "Introduction {$number}.",
                'story' => "Story {$number}.",
                'outcome' => "Outcome {$number}.",
                'is_published' => true,
            ]);
        }

        Project::create([
            'class_name' => 'Unpublished class',
            'grade' => 12,
            'title' => 'Unpublished project story',
            'introduction' => 'This project is not published.',
            'story' => 'Draft story.',
            'outcome' => 'Draft outcome.',
            'is_published' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Project Story 1')
            ->assertSee('Project Story 2')
            ->assertSee('Project Story 3')
            ->assertSee('Project Story 4')
            ->assertSee('Project Story 5')
            ->assertSee('story-strip', false)
            ->assertSee('Introduction 1.')
            ->assertSee('Baca proyek')
            ->assertDontSee('Unpublished project story');
    }

    public function test_project_back_to_showcase_link_uses_a_relative_url(): void
    {
        $project = Project::create([
            'class_name' => 'Class 12A',
            'grade' => 12,
            'title' => 'Green Economy',
            'introduction' => 'A project introduction.',
            'story' => 'A project story.',
            'outcome' => 'A project outcome.',
        ]);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('<a href="/">Beranda</a>', false)
            ->assertSee('Produk dari proyek ini');
    }

    public function test_seller_registration_creates_a_seller_profile_without_exposing_admin_role(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Student Seller',
            'email' => 'seller@example.test',
            'role' => 'seller',
            'store_name' => 'Class 3 Makers',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('verification.notice'));

        $seller = User::where('email', 'seller@example.test')->firstOrFail();
        $this->assertSame('seller', $seller->role);
        $this->assertSame('Class 3 Makers', $seller->seller->store_name);
        $this->assertTrue(password_verify('secure-password', $seller->password));
        $this->assertNull($seller->email_verified_at);
        Notification::assertSentTo($seller, VerifyEmail::class);

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

    public function test_project_story_links_to_its_products_and_catalog_filters_by_class_and_category(): void
    {
        $seller = $this->seller();
        $project = Project::create([
            'class_name' => 'XI Kriya',
            'grade' => 11,
            'title' => 'Dari Jelantah Jadi Cahaya',
            'introduction' => 'Mengolah limbah menjadi karya.',
            'story' => 'Cerita lengkap proyek kelas.',
            'outcome' => 'Produk kriya ramah lingkungan.',
        ]);
        $seller->update(['project_id' => $project->id]);
        $category = Category::create(['name' => 'Aksesori', 'slug' => 'aksesori']);
        $product = Product::create([
            'seller_id' => $seller->id,
            'project_id' => $project->id,
            'category_id' => $category->id,
            'name' => 'Lilin Jelantah',
            'slug' => 'lilin-jelantah',
            'description' => 'Lilin dalam gelas kaca bekas.',
            'price' => 22000,
            'stock' => 2,
            'status' => 'published',
        ]);
        $otherProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Buku Catatan',
            'slug' => 'buku-catatan',
            'description' => 'Kertas daur ulang.',
            'price' => 12000,
            'stock' => 2,
            'status' => 'published',
        ]);
        $soldOutProduct = Product::create([
            'seller_id' => $seller->id,
            'project_id' => $project->id,
            'category_id' => $category->id,
            'name' => 'Lilin habis',
            'slug' => 'lilin-habis',
            'description' => 'Stok sudah habis.',
            'price' => 9000,
            'stock' => 0,
            'status' => 'published',
        ]);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Cerita lengkap proyek kelas.')
            ->assertSee('Lilin Jelantah')
            ->assertSee(route('products.show', $product), false);
        $this->get(route('catalog.index', ['class' => $project->id, 'category' => $category->id]))
            ->assertOk()
            ->assertSee('Lilin Jelantah')
            ->assertSee('Lilin habis')
            ->assertDontSee('Buku Catatan');
        $this->get(route('catalog.index', ['class' => $project->id, 'category' => $category->id, 'stock' => 'available']))
            ->assertOk()
            ->assertSee('Lilin Jelantah')
            ->assertDontSee('Lilin habis');
        $this->get(route('catalog.index', ['class' => $project->id, 'category' => $category->id, 'stock' => 'sold_out']))
            ->assertOk()
            ->assertSee('Lilin habis')
            ->assertDontSee('Lilin Jelantah');
    }

    public function test_buy_now_checks_out_only_the_selected_product_and_preserves_the_cart(): void
    {
        config([
            'marketplace.bank_name' => 'BCA',
            'marketplace.account_name' => 'FORTEXIV',
            'marketplace.account_number' => '1234567890',
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $cartProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk keranjang',
            'slug' => 'produk-keranjang',
            'description' => 'Tidak ikut checkout langsung.',
            'price' => 7000,
            'stock' => 3,
            'status' => 'published',
        ]);
        $buyNowProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk beli sekarang',
            'slug' => 'produk-beli-sekarang',
            'description' => 'Produk yang dipilih langsung.',
            'price' => 14000,
            'stock' => 3,
            'status' => 'published',
        ]);
        $buyer->cartItems()->create(['product_id' => $cartProduct->id, 'quantity' => 1]);

        $this->actingAs($buyer)
            ->get(route('checkout.show', ['mode' => 'now', 'product' => $buyNowProduct->id, 'quantity' => 2]))
            ->assertOk()
            ->assertSee('Produk beli sekarang')
            ->assertDontSee('Produk keranjang');
        $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'now',
            'product_id' => $buyNowProduct->id,
            'quantity' => 2,
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertCount(1, $order->items);
        $this->assertSame($buyNowProduct->id, $order->items->first()->product_id);
        $this->assertSame(3, $cartProduct->fresh()->stock);
        $this->assertSame(1, $buyNowProduct->fresh()->stock);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $buyer->id,
            'product_id' => $cartProduct->id,
            'quantity' => 1,
        ]);
    }

    public function test_checkout_requires_terms_acceptance_without_reserving_stock(): void
    {
        config([
            'marketplace.bank_name' => 'BCA',
            'marketplace.account_name' => 'FORTEXIV',
            'marketplace.account_number' => '1234567890',
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Kartu ucapan',
            'slug' => 'kartu-ucapan',
            'description' => 'Kartu daur ulang.',
            'price' => 5000,
            'stock' => 2,
            'status' => 'published',
        ]);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->from(route('checkout.show', ['mode' => 'cart']))->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
        ])->assertSessionHasErrors([
            'accepted_terms' => 'Setujui Terms of Service sebelum mengonfirmasi pesanan.',
        ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_products_remain_visible_before_the_po_opens_but_cannot_be_added_to_cart(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        config([
            'marketplace.po_start' => '2026-10-27 08:00:00',
            'marketplace.po_end' => '2026-11-01 18:00:00',
            'marketplace.event_at' => '2026-11-10 09:00:00',
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk PO mendatang',
            'slug' => 'produk-po-mendatang',
            'description' => 'Akan tersedia saat PO buka.',
            'price' => 8000,
            'stock' => 2,
            'status' => 'published',
        ]);

        $this->get(route('catalog.index'))->assertOk()->assertSee('PO belum dibuka')->assertSee($product->name);
        $this->actingAs($buyer)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Pemesanan belum tersedia')
            ->assertDontSee('Beli Sekarang');
        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1])->assertUnprocessable();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_catalog_shows_the_closed_po_state_without_hiding_products(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        config([
            'marketplace.po_start' => '2026-10-20 08:00:00',
            'marketplace.po_end' => '2026-10-25 18:00:00',
            'marketplace.event_at' => '2026-11-10 09:00:00',
        ]);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk PO ditutup',
            'slug' => 'produk-po-ditutup',
            'description' => 'Tetap tampil sebagai informasi.',
            'price' => 8000,
            'stock' => 2,
            'status' => 'published',
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('PO ditutup')
            ->assertSee($product->name);
    }

    public function test_buyer_can_checkout_upload_transfer_proof_and_receive_a_class_pickup_ticket(): void
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

        $this->actingAs($buyer)->get(route('products.show', $product))->assertOk()->assertSee('Tambah ke Keranjang');
        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])->assertRedirect(route('cart.index'));
        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('Kontak pengambil')
            ->assertSee('Terms of Service');
        $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])
            ->assertRedirect();

        $order = Order::with('items', 'fulfillments')->firstOrFail();
        $this->assertSame('FX-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT), $order->order_code);
        $this->assertSame(28000, $order->total_price);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(14000, $order->items->first()->price_at_order);
        $this->assertNotNull($order->terms_accepted_at);
        $this->assertSame('prototype-1.0', $order->terms_version);
        $this->assertNotEmpty($order->fulfillments->first()->pickup_code);

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

        $pickupCode = $order->fresh()->fulfillments->first()->pickup_code;
        $this->actingAs($buyer)->get(route('tickets.show', $order))
            ->assertOk()
            ->assertSee($pickupCode)
            ->assertSee('tidak dapat dipindai');

        $this->actingAs($admin)->from(route('admin.pickup'))->post(route('admin.pickup.validate'), [
            'pickup_code' => $pickupCode,
            'seller_id' => $seller->id,
        ])
            ->assertRedirect(route('admin.pickup'))
            ->assertSessionHas('pickup_group', [
                'order_code' => $order->order_code,
                'buyer_name' => $buyer->name,
                'seller_name' => $seller->store_name,
            ]);
        $this->assertSame('picked_up', $order->fresh()->status);
        $this->actingAs($admin)->withSession(['pickup_group' => [
            'order_code' => $order->order_code,
            'buyer_name' => $buyer->name,
            'seller_name' => $seller->store_name,
        ]])->get(route('admin.pickup'))
            ->assertOk()
            ->assertSee('Validasi pickup grup kelas')
            ->assertSee('Class Makers');
        $this->actingAs($admin)->from(route('admin.pickup'))->post(route('admin.pickup.validate'), [
            'pickup_code' => $pickupCode,
            'seller_id' => $seller->id,
        ])->assertSessionHasErrors('pickup_code');
    }

    public function test_each_class_group_can_be_handed_over_independently_until_the_order_is_complete(): void
    {
        config([
            'marketplace.bank_name' => 'BCA',
            'marketplace.account_name' => 'FORTEXIV',
            'marketplace.account_number' => '1234567890',
        ]);
        Storage::fake('local');
        $buyer = User::factory()->create();
        $firstSeller = $this->seller('class-a@example.test');
        $secondSeller = $this->seller('class-b@example.test');
        $firstProduct = Product::create(['seller_id' => $firstSeller->id, 'name' => 'Class A tote', 'slug' => 'class-a-tote', 'description' => 'Tote bag', 'price' => 14000, 'stock' => 2, 'status' => 'published']);
        $secondProduct = Product::create(['seller_id' => $secondSeller->id, 'name' => 'Class B card', 'slug' => 'class-b-card', 'description' => 'Greeting card', 'price' => 5000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->createMany([
            ['product_id' => $firstProduct->id, 'quantity' => 1],
            ['product_id' => $secondProduct->id, 'quantity' => 1],
        ]);

        $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertRedirect();
        $order = Order::with('fulfillments')->firstOrFail();
        $this->assertCount(2, $order->fulfillments);
        $order->payment()->create(['proof_image' => 'payment-proofs/receipt.pdf', 'method' => 'Transfer', 'status' => 'pending']);
        $order->payment->update(['status' => 'verified']);
        $order->update(['status' => 'paid']);
        $order->ticket()->create(['ticket_code' => 'FXT-CLASSGROUP01', 'issued_at' => now()]);

        $firstPickupCode = $order->fulfillments->firstWhere('seller_id', $firstSeller->id)->pickup_code;
        $secondPickupCode = $order->fulfillments->firstWhere('seller_id', $secondSeller->id)->pickup_code;
        $this->actingAs($firstSeller->user)->post(route('seller.pickup'), ['pickup_code' => $firstPickupCode])
            ->assertRedirect();
        $this->assertSame('partially_picked_up', $order->fresh()->status);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $firstSeller->id,
            'status' => 'handed_over',
            'handed_over_by' => $firstSeller->user->id,
        ]);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $secondSeller->id,
            'status' => 'pending',
            'handed_over_by' => null,
        ]);

        $this->actingAs($firstSeller->user)->post(route('seller.pickup'), ['pickup_code' => $firstPickupCode])
            ->assertSessionHasErrors('pickup_code');
        $this->assertSame('partially_picked_up', $order->fresh()->status);

        $this->actingAs($secondSeller->user)->post(route('seller.pickup'), ['pickup_code' => $secondPickupCode])
            ->assertRedirect();
        $this->assertSame('picked_up', $order->fresh()->status);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $secondSeller->id,
            'status' => 'handed_over',
        ]);

        $this->actingAs($firstSeller->user)
            ->post(route('seller.pickup'), ['pickup_code' => $firstPickupCode])
            ->assertSessionHasErrors([
                'pickup_code' => 'Grup kelas ini sudah diserahkan.',
            ]);
        $this->assertSame('picked_up', $order->fresh()->status);
    }

    public function test_seller_validation_errors_use_indonesian_attribute_names(): void
    {
        $seller = $this->seller('validation-seller@example.test');

        $this->actingAs($seller->user)
            ->from(route('seller.products.create'))
            ->post(route('seller.products.store'), [])
            ->assertSessionHasErrors([
                'name' => 'Kolom nama produk wajib diisi.',
            ]);
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
        $this->get(route('login'))->assertOk()->assertSee('Masuk');
        $this->get(route('register'))->assertOk()->assertSee('Buat akun');
        $this->get(route('help'))->assertOk()->assertSee('pertanyaan umum')->assertSee('Cara memesan')->assertSee('Pengambilan barang');
        $this->get(route('terms'))->assertOk()->assertSee('Terms of Service');
        $this->actingAs($buyer)->get(route('account'))->assertOk()->assertSee('Account information');
        $this->actingAs($buyer)->get(route('cart.index'))->assertOk();
        $this->actingAs($buyer)->get(route('orders.index'))->assertOk();

        $seller = $this->seller('pages-seller@example.test');
        $this->actingAs($seller->user)->get(route('seller.dashboard'))->assertOk()->assertSee('DASHBOARD PENJUAL');
        $this->actingAs($seller->user)->get(route('seller.products.index'))->assertOk()->assertSee('Produk Anda');
        $this->actingAs($seller->user)->get(route('seller.products.create'))->assertOk()->assertSee('Tambah produk');
        $this->actingAs($seller->user)->get(route('seller.orders'))->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Daftar pesanan');
        $this->actingAs($admin)->get(route('admin.payments'))->assertOk()->assertSee('Verifikasi pembayaran');
        $this->actingAs($admin)->get(route('admin.users'))->assertOk()->assertSee('Nonaktifkan');
        $this->actingAs($admin)->get(route('admin.pickup'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports'))->assertOk()->assertSee('Laporan harian');
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

        $this->actingAs($buyer)->from(route('cart.index'))->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_checkout_saves_the_order_while_payment_details_are_unconfigured(): void
    {
        config([
            'marketplace.bank_name' => null,
            'marketplace.account_name' => null,
            'marketplace.account_number' => null,
            'services.midtrans.server_key' => null,
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'seed-kit', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Lanjut ke checkout');
        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('Pesanan tetap akan disimpan')
            ->assertSee('Buat pesanan');

        $response = $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ]);
        $response->assertRedirect(route('orders.show', Order::query()->firstOrFail()));
        $response->assertSessionHas('status', 'Pesanan berhasil disimpan di database dan menunggu informasi pembayaran.');

        $order = Order::query()->with('items')->firstOrFail();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'buyer_id' => $buyer->id,
            'status' => 'pending_payment',
            'total_price' => 7000,
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
        ]);
        $this->assertCount(1, $order->items);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_order' => 7000,
            'subtotal' => 7000,
        ]);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $seller->id,
        ]);
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $buyer->id, 'product_id' => $product->id]);
        $this->assertSame(1, $product->fresh()->stock);

        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Pesanan tersimpan dan menunggu informasi pembayaran.');
    }

    public function test_seller_cannot_handover_a_ticket_without_a_group_in_the_order(): void
    {
        $buyer = User::factory()->create();
        $order = Order::create([
            'order_code' => 'FX-SELLERTEST',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 1000,
            'pickup_date' => now()->addDay(),
        ]);
        $otherSeller = $this->seller('order-owner@example.test');
        $order->fulfillments()->create(['seller_id' => $otherSeller->id, 'pickup_code' => 'FTXG-SELLERTEST01']);
        $order->ticket()->create(['ticket_code' => 'FXT-SELLERTEST01', 'issued_at' => now()]);
        $unrelatedSeller = $this->seller('unrelated-seller@example.test');

        $this->actingAs($unrelatedSeller->user)->post(route('seller.pickup'), ['pickup_code' => 'FTXG-SELLERTEST01'])
            ->assertSessionHasErrors('pickup_code');

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $otherSeller->id,
            'status' => 'pending',
            'handed_over_by' => null,
        ]);
    }

    public function test_buyer_can_create_a_midtrans_snap_checkout(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.base_url' => 'https://app.sandbox.midtrans.com',
            'marketplace.po_start' => null,
            'marketplace.po_end' => null,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token',
            ], 201),
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'midtrans-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)
            ->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('value="midtrans"', false);

        $response = $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ]);

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token');
        $order = Order::with('payment')->firstOrFail();
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(Payment::GATEWAY_MIDTRANS, $order->payment->gateway);
        $this->assertSame('snap-token', $order->payment->snap_token);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token',
        ]);
        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['order_id'] === $order->order_code
            && $request['transaction_details']['gross_amount'] === 14000);
    }

    public function test_midtrans_checkout_is_unavailable_without_a_server_key(): void
    {
        config([
            'services.midtrans.server_key' => null,
            'marketplace.bank_name' => '',
            'marketplace.account_name' => '',
            'marketplace.account_number' => '',
            'marketplace.po_start' => null,
            'marketplace.po_end' => null,
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'unconfigured-midtrans-seed', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))->assertOk();

        $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertStatus(503);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_midtrans_api_failure_keeps_the_order_pending_and_shows_a_warning(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.base_url' => 'https://app.sandbox.midtrans.com',
            'marketplace.po_start' => null,
            'marketplace.po_end' => null,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['message' => 'Unavailable'], 500),
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'midtrans-api-failure-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))->assertOk();

        $response = $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ]);

        $order = Order::with('payment')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order))->assertSessionHas('error');
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('pending', $order->payment->status);
        $this->assertNull($order->payment->snap_redirect_url);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_midtrans_checkout_does_not_redirect_to_an_untrusted_payment_url(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.base_url' => 'https://app.sandbox.midtrans.com',
            'marketplace.po_start' => null,
            'marketplace.po_end' => null,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://attacker.example/snap/v2/vtweb/snap-token',
            ], 201),
        ]);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'unsafe-midtrans-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))->assertOk();

        $response = $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ]);

        $order = Order::with('payment')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order))->assertSessionHas('error');
        $this->assertSame('pending_payment', $order->status);
        $this->assertNull($order->payment->snap_redirect_url);
    }

    public function test_midtrans_settlement_marks_the_order_paid_and_issues_only_one_ticket(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);
        $notification = $this->midtransNotification($order, [
            'transaction_status' => 'settlement',
        ]);
        $this->fakeMidtransStatus($order, [
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'midtrans-transaction-1',
        ]);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk()->assertJson(['status' => 'ok']);
        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'verified',
            'method' => 'bank_transfer',
            'transaction_id' => 'midtrans-transaction-1',
        ]);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_midtrans_notification_returns_403_for_an_invalid_signature(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $order = $this->midtransOrder(User::factory()->create());
        $notification = $this->midtransNotification($order);
        $notification['signature_key'] = str_repeat('0', 128);

        $this->postJson(route('midtrans.notifications'), $notification)->assertForbidden();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_notification_rejects_an_amount_that_does_not_match_the_order(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $order = $this->midtransOrder(User::factory()->create());
        $notification = $this->midtransNotification($order, ['gross_amount' => '1.00']);
        $this->fakeMidtransStatus($order);

        $this->postJson(route('midtrans.notifications'), $notification)->assertUnprocessable();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_expiration_cancels_the_order_and_returns_reserved_stock_once(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'midtrans-seed-kit', 'description' => 'Seeds', 'price' => 14000, 'stock' => 0, 'status' => 'published']);
        $order = $this->midtransOrder($buyer);
        $order->items()->create(['product_id' => $product->id, 'seller_id' => $seller->id, 'product_name' => $product->name, 'quantity' => 1, 'price_at_order' => 14000, 'subtotal' => 14000]);
        $notification = $this->midtransNotification($order, ['transaction_status' => 'expire']);
        $this->fakeMidtransStatus($order, ['transaction_status' => 'expire']);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();
        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_capture_with_a_challenge_fraud_status_does_not_mark_the_order_paid(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $order = $this->midtransOrder(User::factory()->create());
        $notification = $this->midtransNotification($order, [
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ]);
        $this->fakeMidtransStatus($order, [
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ]);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_uses_the_verified_status_instead_of_the_callback_status(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $order = $this->midtransOrder(User::factory()->create());
        $notification = $this->midtransNotification($order, ['transaction_status' => 'settlement']);
        $this->fakeMidtransStatus($order, ['transaction_status' => 'pending']);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_buyer_cannot_upload_manual_proof_for_a_midtrans_order(): void
    {
        Storage::fake('local');
        config([
            'marketplace.bank_name' => 'BCA',
            'marketplace.account_name' => 'FORTEXIV',
            'marketplace.account_number' => '1234567890',
        ]);
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);

        $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk();

        $this->actingAs($buyer)->post(route('payments.store', $order), [
            '_token' => csrf_token(),
            'method' => 'Transfer BCA',
            'proof' => UploadedFile::fake()->createWithContent('receipt.pdf', "%PDF-1.4\ntransfer receipt\n%%EOF"),
        ])->assertUnprocessable();

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'pending',
        ]);
        Storage::disk('local')->assertDirectoryEmpty('payment-proofs');
    }

    private function midtransOrder(User $buyer): Order
    {
        $order = Order::create([
            'order_code' => 'FX-MIDTRANS01',
            'buyer_id' => $buyer->id,
            'status' => 'pending_payment',
            'total_price' => 14000,
            'pickup_date' => now()->addDay(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'pending',
        ]);

        return $order;
    }

    /**
     * @param  array<string, string>  $overrides
     */
    private function fakeMidtransStatus(Order $order, array $overrides = []): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.status_url' => 'https://api.sandbox.midtrans.com',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/'.$order->order_code.'/status' => Http::response(array_merge([
                'order_id' => $order->order_code,
                'gross_amount' => '14000.00',
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'transaction_id' => 'midtrans-transaction-1',
            ], $overrides)),
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function midtransNotification(Order $order, array $overrides = []): array
    {
        $notification = array_merge([
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '14000.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-transaction-1',
        ], $overrides);
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].config('services.midtrans.server_key'),
        );

        return $notification;
    }

    private function seller(string $email = 'seller@example.test'): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'email' => $email]);

        return Seller::create(['user_id' => $user->id, 'store_name' => 'Class Makers']);
    }
}
