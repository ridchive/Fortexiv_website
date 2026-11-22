<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use App\Services\CarbonImpactService;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarbonFootprintManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_only_published_approved_products_with_stock_in_live_list(): void
    {
        $seller = $this->seller();
        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Tas Live',
            'slug' => 'tas-live',
            'description' => 'Produk tersedia.',
            'price' => 50000,
            'stock' => 4,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Draft',
            'slug' => 'produk-draft',
            'description' => 'Produk belum terbit.',
            'price' => 50000,
            'stock' => 4,
            'review_status' => 'approved',
            'status' => 'draft',
        ]);
        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Habis',
            'slug' => 'produk-habis',
            'description' => 'Produk tidak punya stok.',
            'price' => 50000,
            'stock' => 0,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Belum Disetujui',
            'slug' => 'produk-belum-disetujui',
            'description' => 'Produk belum direview.',
            'price' => 50000,
            'stock' => 4,
            'review_status' => 'pending_review',
            'status' => 'published',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['status' => 'live']))
            ->assertOk()
            ->assertSee('Sedang dijual (1)')
            ->assertSee('Tas Live')
            ->assertSee('4')
            ->assertDontSee('Produk Draft')
            ->assertDontSee('Produk Habis')
            ->assertDontSee('Produk Belum Disetujui');
    }

    public function test_admin_can_manage_carbon_value_and_buyer_sees_it_below_description(): void
    {
        $seller = $this->seller();
        $project = Project::create([
            'class_name' => 'Kelas Hijau',
            'grade' => 10,
            'title' => 'Proyek Hijau',
            'introduction' => 'Ringkasan.',
            'story' => 'Cerita.',
            'outcome' => 'Hasil.',
        ]);
        $category = Category::create(['name' => 'Kerajinan', 'slug' => 'kerajinan']);
        $product = Product::create([
            'seller_id' => $seller->id,
            'project_id' => $project->id,
            'category_id' => $category->id,
            'name' => 'Tas Daur Ulang',
            'slug' => 'tas-daur-ulang',
            'description' => 'Deskripsi produk.',
            'story' => null,
            'price' => 50000,
            'stock' => 3,
            'review_status' => 'pending_review',
            'status' => 'draft',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.products.decide', $product), [
                'decision' => 'approved',
                'story' => 'Cerita produk yang dikelola admin.',
                'carbon_reduction_value' => '1.5',
            ])
            ->assertRedirect(route('admin.products.index', ['status' => 'pending_review']));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'carbon_reduction_value' => 1.5,
            'carbon_footprint_value' => 0,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 1.5,
            'carbon_baseline_source' => 'Nilai pengurangan karbon langsung per produk',
            'carbon_product_source' => 'Nilai pengurangan karbon langsung per produk',
            'carbon_functional_unit' => '1 produk',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('PENGURANGAN EMISI PRODUK')
            ->assertSee('1,500000 kg CO2e');
    }

    public function test_admin_dashboard_aggregates_carbon_by_class_seller_and_buyer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer', 'name' => 'Pembeli Dashboard']);
        $seller = $this->seller();
        $project = Project::create([
            'class_name' => 'Kelas Dashboard',
            'grade' => 10,
            'title' => 'Proyek Dashboard',
            'introduction' => 'Ringkasan.',
            'story' => 'Cerita.',
            'outcome' => 'Hasil.',
        ]);
        $seller->update(['project_id' => $project->id]);
        $order = Order::create([
            'order_code' => 'FX-CARBONDASH',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 25000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/dashboard.jpg',
        ]);
        $order->items()->create([
            'seller_id' => $seller->id,
            'product_name' => 'Produk Dashboard',
            'quantity' => 2,
            'price_at_order' => 12500,
            'subtotal' => 25000,
            'carbon_footprint_value_at_purchase' => 1.25,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_snapshot_source' => 'checkout',
            'carbon_contribution' => 2.5,
            'carbon_eligible_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('CARBON EMISSION AVOIDED DASHBOARD')
            ->assertSee('2,500000 kg CO2e')
            ->assertSee('Kelas Dashboard')
            ->assertSee('Produk Dashboard')
            ->assertSee('Kelas Penguji')
            ->assertSee('Pembeli Dashboard');
    }

    public function test_seller_dashboard_only_shows_its_eligible_carbon_contribution(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $otherSellerUser = User::factory()->create([
            'role' => 'seller',
            'email' => 'seller-carbon-other@example.test',
        ]);
        $otherSeller = Seller::create([
            'user_id' => $otherSellerUser->id,
            'store_name' => 'Kelas Rahasia',
        ]);

        foreach ([
            [$seller, 'Produk Kelas Sendiri', 'FX-SELLERCARBON1'],
            [$otherSeller, 'Produk Kelas Lain', 'FX-SELLERCARBON2'],
        ] as [$orderSeller, $productName, $orderCode]) {
            $order = Order::create([
                'order_code' => $orderCode,
                'buyer_id' => $buyer->id,
                'status' => 'paid',
                'total_price' => 10000,
                'pickup_date' => now()->toDateString(),
            ]);
            $order->payment()->create([
                'gateway' => Payment::GATEWAY_MANUAL,
                'status' => 'verified',
                'proof_image' => 'proof/'.$orderCode.'.jpg',
            ]);
            $order->items()->create([
                'seller_id' => $orderSeller->id,
                'product_name' => $productName,
                'quantity' => 1,
                'price_at_order' => 10000,
                'subtotal' => 10000,
                'carbon_footprint_value_at_purchase' => 1.0,
                'carbon_footprint_unit_at_purchase' => 'kg CO2e',
                'carbon_contribution' => $orderSeller->id === $seller->id ? 1.0 : 9.0,
                'carbon_eligible_at' => now(),
            ]);
        }

        $this->actingAs($seller->user)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('1,000000 kg CO2e')
            ->assertSee('Produk Kelas Sendiri')
            ->assertDontSee('Produk Kelas Lain')
            ->assertDontSee('9,0000 kg CO2e');
    }

    public function test_buyer_dashboard_shows_only_its_contributions_grouped_by_order_and_class(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $otherSellerUser = User::factory()->create([
            'role' => 'seller',
            'email' => 'seller-carbon-buyer-two@example.test',
        ]);
        $otherSeller = Seller::create([
            'user_id' => $otherSellerUser->id,
            'store_name' => 'Kelas Dua',
        ]);
        $order = Order::create([
            'order_code' => 'FX-BUYERCARBON',
            'buyer_id' => $buyer->id,
            'status' => 'partially_picked_up',
            'total_price' => 30000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/buyer-carbon.jpg',
        ]);

        foreach ([[$seller, 'Kelas Satu', 1.5], [$otherSeller, 'Kelas Dua', 2.5]] as [$orderSeller, $className, $contribution]) {
            $fulfillment = $order->fulfillments()->create([
                'seller_id' => $orderSeller->id,
                'class_key' => 'project-'.$className,
                'class_name' => $className,
                'pickup_code' => 'PICKUP-'.str_replace(' ', '', $className),
            ]);
            $order->items()->create([
                'seller_id' => $orderSeller->id,
                'order_fulfillment_id' => $fulfillment->id,
                'product_name' => 'Produk '.$className,
                'quantity' => 1,
                'price_at_order' => 15000,
                'subtotal' => 15000,
                'carbon_footprint_value_at_purchase' => $contribution,
                'carbon_footprint_unit_at_purchase' => 'kg CO2e',
                'carbon_contribution' => $contribution,
                'carbon_eligible_at' => now(),
            ]);
        }

        $this->actingAs($buyer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('4,000000 kg CO2e')
            ->assertSee('Kelas Satu')
            ->assertSee('Kelas Dua')
            ->assertSee('FX-BUYERCARBON');

        $this->actingAs($otherBuyer)
            ->get(route('orders.show', $order))
            ->assertNotFound();
    }

    public function test_public_impact_uses_the_same_eligible_snapshot_contribution_as_the_total(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Tracker',
            'slug' => 'produk-tracker',
            'description' => 'Produk untuk tracker.',
            'price' => 10000,
            'stock' => 0,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 1.5,
            'carbon_footprint_unit' => 'kg CO2e',
        ]);
        $order = Order::create([
            'order_code' => 'FX-PUBLICTRACKER',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/public-carbon.jpg',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price_at_order' => 10000,
            'subtotal' => 20000,
            'carbon_footprint_value_at_purchase' => 2.0,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_quantity_at_purchase' => 2,
            'carbon_contribution' => 4.0,
            'carbon_eligible_at' => now(),
        ]);

        $this->get(route('impact.index'))
            ->assertOk()
            ->assertSee('4,00')
            ->assertSee('kg CO2e')
            ->assertSee('2 unit')
            ->assertSee('4,000000 kg CO2e')
            ->assertDontSee('20,00 kg CO2e');
    }

    public function test_unverified_legacy_factor_is_not_promoted_and_missing_order_snapshot_is_flagged(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Legacy',
            'slug' => 'produk-legacy',
            'description' => 'Produk legacy.',
            'price' => 10000,
            'stock' => 0,
            'review_status' => 'approved',
            'status' => 'published',
            'co2_reduction' => 1.75,
        ]);
        $order = Order::create([
            'order_code' => 'FX-LEGACYCARBON',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/legacy-carbon.jpg',
            'verified_at' => now()->subDay(),
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price_at_order' => 10000,
            'subtotal' => 10000,
        ]);

        $migration = require database_path('migrations/2026_11_20_101119_normalize_and_backfill_carbon_footprints.php');
        $migration->up();

        $this->assertNull($product->fresh()->carbon_footprint_value);
        $this->assertSame('legacy_unverified', $item->fresh()->carbon_snapshot_source);
        $this->assertNull($item->fresh()->carbon_contribution);
        $this->assertSame(0.0, app(CarbonImpactService::class)->aggregateEligibleContribution());
        $summary = app(CarbonImpactService::class)->adminDashboardSummary();
        $this->assertSame(1, $summary['missing_snapshot_count']);
        $this->assertSame(1, $summary['legacy_snapshot_count']);
        $this->assertSame($item->id, $summary['incomplete_items']->first()->id);
    }

    public function test_carbon_dashboard_routes_reject_users_without_their_role(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($buyer)->get(route('seller.dashboard'))->assertForbidden();
        $this->actingAs($seller->user)->get(route('orders.index'))->assertForbidden();
        $this->actingAs($seller->user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('orders.index'))->assertForbidden();
    }

    public function test_seller_cannot_change_official_carbon_value(): void
    {
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Seller',
            'slug' => 'produk-seller',
            'description' => 'Deskripsi awal.',
            'price' => 10000,
            'stock' => 2,
            'review_status' => 'draft',
            'status' => 'draft',
            'carbon_footprint_value' => 2.5,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 5.0,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 pot',
        ]);

        $this->actingAs($seller->user)
            ->put(route('seller.products.update', $product), [
                'name' => 'Produk Seller Baru',
                'description' => 'Deskripsi baru.',
                'price' => 12000,
                'stock' => 2,
                'carbon_footprint_value' => 99,
                'carbon_footprint_unit' => 'kg CO2e',
            ])
            ->assertRedirect(route('seller.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'carbon_footprint_value' => 2.5,
            'carbon_footprint_unit' => 'kg CO2e',
        ]);
    }

    public function test_verified_payment_is_snapshot_based_and_repeated_processing_is_idempotent(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Pot Tembikar',
            'slug' => 'pot-tembikar',
            'description' => 'Pot tanah liat.',
            'price' => 20000,
            'stock' => 5,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 2.5,
            'carbon_footprint_unit' => 'kg CO2e',
        ]);
        $order = Order::create([
            'order_code' => 'FX-00000001',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 60000,
            'pickup_date' => now()->toDateString(),
        ]);
        Payment::create([
            'order_id' => $order->id,
            'gateway' => Payment::GATEWAY_MANUAL,
            'proof_image' => 'proof/test.jpg',
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $buyer->id,
        ]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'price_at_order' => 20000,
            'subtotal' => 60000,
            'carbon_footprint_value_at_purchase' => 2.5,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_baseline_emission_factor_at_purchase' => 5.0,
            'carbon_baseline_source_at_purchase' => 'Baseline source',
            'carbon_product_source_at_purchase' => 'Product source',
            'carbon_functional_unit_at_purchase' => '1 pot',
            'carbon_quantity_at_purchase' => 3,
            'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
            'carbon_snapshot_source' => 'checkout',
        ]);

        $impact = app(CarbonImpactService::class);
        $impact->recordEligiblePurchase($order);
        $impact->recordEligiblePurchase($order);

        $this->assertSame(7.5, (float) $item->fresh()->carbon_contribution);

        $product->update([
            'carbon_footprint_value' => 3.0,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 99.0,
        ]);
        $impact->recordEligiblePurchase($order);

        $this->assertSame(7.5, (float) $item->fresh()->carbon_contribution);
        $this->assertSame(7.5, $impact->aggregateEligibleContribution());
    }

    public function test_adjustment_is_audited_and_affects_only_the_selected_item(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Koreksi',
            'slug' => 'produk-koreksi',
            'description' => 'Deskripsi.',
            'price' => 10000,
            'stock' => 4,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 1.5,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 3.0,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 product',
        ]);
        $order = Order::create([
            'order_code' => 'FX-00000002',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        Payment::create([
            'order_id' => $order->id,
            'gateway' => Payment::GATEWAY_MANUAL,
            'proof_image' => 'proof/test.jpg',
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price_at_order' => 10000,
            'subtotal' => 20000,
            'carbon_footprint_value_at_purchase' => 1.5,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_baseline_emission_factor_at_purchase' => 3.0,
            'carbon_baseline_source_at_purchase' => 'Baseline source',
            'carbon_product_source_at_purchase' => 'Product source',
            'carbon_functional_unit_at_purchase' => '1 product',
            'carbon_quantity_at_purchase' => 2,
            'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
            'carbon_snapshot_source' => 'checkout',
        ]);
        app(CarbonImpactService::class)->recordEligiblePurchase($order);

        app(CarbonImpactService::class)->adjustOrderItem(
            $item,
            0,
            'Refund penuh kelas terkait.',
            $admin,
        );

        $this->assertSame(3.0, (float) $item->fresh()->carbon_contribution);
        $this->assertDatabaseHas('carbon_impact_adjustments', [
            'order_item_id' => $item->id,
            'performed_by' => $admin->id,
            'previous_contribution' => 3.0,
            'corrected_contribution' => 0.0,
            'reason' => 'Refund penuh kelas terkait.',
        ]);
        $this->assertSame(0.0, app(CarbonImpactService::class)->aggregateEligibleContribution());
        $this->assertDatabaseHas('carbon_impact_adjustments', [
            'order_item_id' => $item->id,
            'adjustment' => -3.0,
        ]);
    }

    public function test_midtrans_refund_without_a_performer_is_audited_once(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Refund',
            'slug' => 'produk-refund',
            'description' => 'Deskripsi.',
            'price' => 20000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 2.5,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 4.0,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 product',
        ]);
        $order = Order::create([
            'order_code' => 'FX-CARBONREFUND',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'verified',
            'midtrans_order_id' => $order->order_code,
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price_at_order' => 20000,
            'subtotal' => 20000,
            'carbon_footprint_value_at_purchase' => 2.5,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_baseline_emission_factor_at_purchase' => 4.0,
            'carbon_baseline_source_at_purchase' => 'Baseline source',
            'carbon_product_source_at_purchase' => 'Product source',
            'carbon_functional_unit_at_purchase' => '1 product',
            'carbon_quantity_at_purchase' => 1,
            'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
            'carbon_snapshot_source' => 'checkout',
        ]);
        app(CarbonImpactService::class)->recordEligiblePurchase($order);

        $notification = [
            'order_id' => $order->order_code,
            'gross_amount' => '20000.00',
        ];
        $transaction = [
            'gross_amount' => '20000.00',
            'transaction_status' => 'refund',
        ];
        $midtrans = app(MidtransService::class);

        $midtrans->handleNotification($notification, $transaction);
        $midtrans->handleNotification($notification, $transaction);

        $this->assertSame(1.5, (float) $item->fresh()->carbon_contribution);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertDatabaseCount('carbon_impact_adjustments', 1);
        $this->assertDatabaseHas('carbon_impact_adjustments', [
            'order_item_id' => $item->id,
            'performed_by' => null,
            'source' => 'midtrans',
        ]);
    }

    public function test_partial_midtrans_refund_is_flagged_for_item_level_admin_adjustment(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $order = Order::create([
            'order_code' => 'FX-CEAPARTIALREFUND',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        $payment = $order->payment()->create([
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'verified',
            'midtrans_order_id' => $order->order_code,
        ]);
        $item = $order->items()->create(array_merge([
            'seller_id' => $seller->id,
            'product_name' => 'Partially refunded item',
            'quantity' => 2,
            'price_at_order' => 10000,
            'subtotal' => 20000,
        ], $this->carbonSnapshot(4.0, 1.0, 2)));
        app(CarbonImpactService::class)->recordEligiblePurchase($order);

        app(MidtransService::class)->handleNotification(
            ['order_id' => $order->order_code, 'gross_amount' => '20000.00', 'refund_amount' => '5000.00'],
            ['gross_amount' => '20000.00', 'transaction_status' => 'refund', 'refund_amount' => '5000.00'],
        );

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(6.0, (float) $item->fresh()->carbon_contribution);
        $this->assertSame(0, $item->carbonImpactAdjustments()->count());
        $this->assertSame(
            'Midtrans melaporkan refund parsial. Admin perlu mengoreksi dampak item terkait.',
            $payment->fresh()->notes,
        );
        $this->assertSame('partially_refunded', $payment->fresh()->status);
    }

    public function test_repeated_midtrans_payment_callback_creates_one_contribution(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk Callback',
            'slug' => 'produk-callback',
            'description' => 'Deskripsi.',
            'price' => 10000,
            'stock' => 2,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 1.25,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 2.5,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 product',
        ]);
        $order = Order::create([
            'order_code' => 'FX-CARBONCALLBACK',
            'buyer_id' => $buyer->id,
            'status' => 'pending_payment',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'pending',
            'midtrans_order_id' => $order->order_code,
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price_at_order' => 10000,
            'subtotal' => 10000,
            'carbon_footprint_value_at_purchase' => 1.25,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_baseline_emission_factor_at_purchase' => 2.5,
            'carbon_baseline_source_at_purchase' => 'Baseline source',
            'carbon_product_source_at_purchase' => 'Product source',
            'carbon_functional_unit_at_purchase' => '1 product',
            'carbon_quantity_at_purchase' => 2,
            'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
            'carbon_snapshot_source' => 'checkout',
        ]);

        $notification = ['order_id' => $order->order_code, 'gross_amount' => '10000.00'];
        $transaction = [
            'gross_amount' => '10000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ];
        $midtrans = app(MidtransService::class);

        $midtrans->handleNotification($notification, $transaction);
        $midtrans->handleNotification($notification, $transaction);

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(2.5, (float) $item->fresh()->carbon_contribution);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_cancelled_order_cannot_become_paid_by_a_later_settlement_callback(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'order_code' => 'FX-CARBONCANCEL',
            'buyer_id' => $buyer->id,
            'status' => 'cancelled',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MIDTRANS,
            'status' => 'failed',
            'midtrans_order_id' => $order->order_code,
        ]);

        app(MidtransService::class)->handleNotification(
            ['order_id' => $order->order_code, 'gross_amount' => '10000.00'],
            ['gross_amount' => '10000.00', 'transaction_status' => 'settlement'],
        );

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('failed', $order->payment->fresh()->status);
    }

    public function test_independent_fulfillment_adjustment_changes_only_the_selected_class_group(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerOne = $this->seller();
        $sellerTwoUser = User::factory()->create([
            'role' => 'seller',
            'email' => 'seller-carbon-two@example.test',
        ]);
        $sellerTwo = Seller::create([
            'user_id' => $sellerTwoUser->id,
            'store_name' => 'Kelas Lain',
        ]);
        $order = Order::create([
            'order_code' => 'FX-CARBONGROUP',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 30000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/test.jpg',
        ]);
        $fulfillmentOne = $order->fulfillments()->create([
            'seller_id' => $sellerOne->id,
            'class_key' => 'project-1',
            'class_name' => 'Kelas Satu',
            'pickup_code' => 'GROUP-ONE',
        ]);
        $fulfillmentTwo = $order->fulfillments()->create([
            'seller_id' => $sellerTwo->id,
            'class_key' => 'project-2',
            'class_name' => 'Kelas Dua',
            'pickup_code' => 'GROUP-TWO',
        ]);
        $first = $order->items()->create([
            'seller_id' => $sellerOne->id,
            'order_fulfillment_id' => $fulfillmentOne->id,
            'product_name' => 'Produk Satu',
            'quantity' => 1,
            'price_at_order' => 10000,
            'subtotal' => 10000,
            'carbon_footprint_value_at_purchase' => 2.0,
            'carbon_contribution' => 2.0,
            'carbon_eligible_at' => now(),
        ]);
        $second = $order->items()->create([
            'seller_id' => $sellerTwo->id,
            'order_fulfillment_id' => $fulfillmentTwo->id,
            'product_name' => 'Produk Dua',
            'quantity' => 1,
            'price_at_order' => 20000,
            'subtotal' => 20000,
            'carbon_footprint_value_at_purchase' => 3.0,
            'carbon_contribution' => 3.0,
            'carbon_eligible_at' => now(),
        ]);

        app(CarbonImpactService::class)->revokeFulfillment(
            $fulfillmentOne,
            'Resolusi independen kelas satu.',
            User::factory()->create(['role' => 'admin']),
        );

        $this->assertSame(2.0, (float) $first->fresh()->carbon_contribution);
        $this->assertSame(3.0, (float) $second->fresh()->carbon_contribution);
        $this->assertSame(3.0, app(CarbonImpactService::class)->aggregateEligibleContribution());
    }

    public function test_missing_zero_and_verified_zero_carbon_values_are_distinct(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $missing = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Belum Ada Faktor',
            'slug' => 'belum-ada-faktor',
            'description' => 'Deskripsi.',
            'price' => 10000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $zero = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Faktor Nol',
            'slug' => 'faktor-nol',
            'description' => 'Deskripsi.',
            'price' => 10000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 0,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 0,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 product',
        ]);
        $order = Order::create([
            'order_code' => 'FX-CARBONZERO',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/test.jpg',
        ]);
        $missingItem = $order->items()->create([
            'product_id' => $missing->id,
            'seller_id' => $seller->id,
            'product_name' => $missing->name,
            'quantity' => 1,
            'price_at_order' => 10000,
            'subtotal' => 10000,
        ]);
        $zeroItem = $order->items()->create([
            'product_id' => $zero->id,
            'seller_id' => $seller->id,
            'product_name' => $zero->name,
            'quantity' => 1,
            'price_at_order' => 10000,
            'subtotal' => 10000,
            'carbon_footprint_value_at_purchase' => 0,
            'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            'carbon_baseline_emission_factor_at_purchase' => 0,
            'carbon_baseline_source_at_purchase' => 'Baseline source',
            'carbon_product_source_at_purchase' => 'Product source',
            'carbon_functional_unit_at_purchase' => '1 product',
            'carbon_quantity_at_purchase' => 1,
            'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
            'carbon_snapshot_source' => 'checkout',
        ]);

        app(CarbonImpactService::class)->recordEligiblePurchase($order);
        $summary = app(CarbonImpactService::class)->impactSummary();

        $this->assertNull($missingItem->fresh()->carbon_contribution);
        $this->assertSame(0.0, (float) $zeroItem->fresh()->carbon_contribution);
        $this->assertTrue($summary['has_verified_data']);
        $this->assertTrue($summary['has_actual_zero']);
    }

    public function test_seller_and_buyer_cannot_adjust_carbon_or_open_admin_adjustment_route(): void
    {
        $seller = $this->seller();
        $buyer = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'order_code' => 'FX-CARBONAUTH',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/test.jpg',
        ]);
        $item = $order->items()->create([
            'seller_id' => $seller->id,
            'product_name' => 'Produk Terbatas',
            'quantity' => 1,
            'price_at_order' => 10000,
            'subtotal' => 10000,
            'carbon_footprint_value_at_purchase' => 1.0,
            'carbon_contribution' => 1.0,
        ]);

        $this->actingAs($seller->user)
            ->post(route('admin.carbon-impact.adjust', $item), [
                'corrected_contribution' => 0,
                'reason' => 'Percobaan akses seller',
            ])
            ->assertForbidden();

        $this->actingAs($buyer)
            ->post(route('admin.carbon-impact.adjust', $item), [
                'corrected_contribution' => 0,
                'reason' => 'Percobaan akses buyer',
            ])
            ->assertForbidden();

        $this->assertSame(1.0, (float) $item->fresh()->carbon_contribution);
    }

    public function test_checkout_snapshot_does_not_change_when_product_carbon_value_is_edited(): void
    {
        $seller = $this->seller();
        $project = Project::create([
            'class_name' => 'Kelas Snapshot',
            'grade' => 10,
            'title' => 'Proyek Snapshot',
            'introduction' => 'Ringkasan.',
            'story' => 'Cerita.',
            'outcome' => 'Hasil.',
        ]);
        $category = Category::create(['name' => 'Snapshot', 'slug' => 'snapshot']);
        $product = Product::create([
            'seller_id' => $seller->id,
            'project_id' => $project->id,
            'category_id' => $category->id,
            'name' => 'Produk Snapshot',
            'slug' => 'produk-snapshot',
            'description' => 'Deskripsi.',
            'price' => 10000,
            'stock' => 2,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 2.25,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 5.25,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 produk',
        ]);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertRedirect();

        $item = OrderItem::query()->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(2.25, (float) $item->carbon_footprint_value_at_purchase);
        $this->assertSame('kg CO2e', $item->carbon_footprint_unit_at_purchase);
        $this->assertSame(5.25, (float) $item->carbon_baseline_emission_factor_at_purchase);
        $this->assertSame('Baseline source', $item->carbon_baseline_source_at_purchase);
        $this->assertSame('Product source', $item->carbon_product_source_at_purchase);
        $this->assertSame('1 produk', $item->carbon_functional_unit_at_purchase);
        $this->assertSame(1, $item->carbon_quantity_at_purchase);
        $this->assertSame(CarbonImpactService::CALCULATION_VERSION, $item->carbon_calculation_version_at_purchase);
        $this->assertSame('checkout', $item->carbon_snapshot_source);

        $product->update([
            'carbon_footprint_value' => 9.75,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 99.0,
            'carbon_functional_unit' => 'Updated functional unit',
        ]);

        $this->assertSame(2.25, (float) $item->fresh()->carbon_footprint_value_at_purchase);
        $this->assertSame(5.25, (float) $item->fresh()->carbon_baseline_emission_factor_at_purchase);
        $this->assertSame('1 produk', $item->fresh()->carbon_functional_unit_at_purchase);
    }

    public function test_item_calculations_separate_avoided_additional_and_net_emissions(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $order = Order::create([
            'order_code' => 'FX-CEAMETRICS',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 110000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/cea-metrics.jpg',
        ]);
        $items = [];

        foreach ([
            ['avoided', 5.0, 2.0, 2],
            ['additional', 2.0, 3.0, 2],
            ['zero', 1.0, 1.0, 4],
            ['precision', 1.234567, 1.000001, 3],
        ] as [$slug, $baseline, $productFactor, $quantity]) {
            $product = Product::create([
                'seller_id' => $seller->id,
                'name' => 'Product '.$slug,
                'slug' => 'product-'.$slug,
                'description' => 'Comparable product.',
                'price' => 10000,
                'stock' => 0,
                'review_status' => 'approved',
                'status' => 'published',
            ]);
            $items[$slug] = $order->items()->create(array_merge([
                'product_id' => $product->id,
                'seller_id' => $seller->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price_at_order' => 10000,
                'subtotal' => 10000 * $quantity,
            ], $this->carbonSnapshot($baseline, $productFactor, $quantity)));
        }

        $impact = app(CarbonImpactService::class);
        $impact->recordEligiblePurchase($order);
        $summary = $impact->adminDashboardSummary();

        $this->assertSame(6.0, (float) $items['avoided']->fresh()->carbon_contribution);
        $this->assertSame(-2.0, (float) $items['additional']->fresh()->carbon_contribution);
        $this->assertSame(0.0, (float) $items['zero']->fresh()->carbon_contribution);
        $this->assertSame(0.703698, (float) $items['precision']->fresh()->carbon_contribution);
        $this->assertSame(6.703698, $summary['total_avoided_emissions']);
        $this->assertSame(2.0, $summary['total_additional_emissions']);
        $this->assertSame(4.703698, $summary['net_avoided_emissions']);
    }

    public function test_unverified_payment_is_excluded_until_verification(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $order = Order::create([
            'order_code' => 'FX-CEAUNVERIFIED',
            'buyer_id' => $buyer->id,
            'status' => 'waiting_verification',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
        ]);
        $payment = $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'pending',
            'proof_image' => 'proof/cea-pending.jpg',
        ]);
        $item = $order->items()->create(array_merge([
            'seller_id' => $seller->id,
            'product_name' => 'Pending CEA item',
            'quantity' => 2,
            'price_at_order' => 5000,
            'subtotal' => 10000,
        ], $this->carbonSnapshot(4.0, 1.0, 2)));
        $impact = app(CarbonImpactService::class);

        $impact->recordEligiblePurchase($order);

        $this->assertNull($item->fresh()->carbon_contribution);
        $this->assertSame(0.0, $impact->aggregateEligibleContribution());

        $payment->update(['status' => 'verified']);
        $order->update(['status' => 'paid']);
        $impact->recordEligiblePurchase($order);

        $this->assertSame(6.0, (float) $item->fresh()->carbon_contribution);
        $this->assertSame(6.0, $impact->aggregateEligibleContribution());
    }

    public function test_admin_payment_verification_records_cea_with_the_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $order = Order::create([
            'order_code' => 'FX-CEAMANUALVERIFY',
            'buyer_id' => $buyer->id,
            'status' => 'waiting_verification',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
        ]);
        $payment = $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'pending',
            'proof_image' => 'proof/cea-manual-verify.jpg',
        ]);
        $item = $order->items()->create(array_merge([
            'seller_id' => $seller->id,
            'product_name' => 'Manually verified item',
            'quantity' => 2,
            'price_at_order' => 5000,
            'subtotal' => 10000,
        ], $this->carbonSnapshot(4.0, 1.0, 2)));

        $this->actingAs($admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertRedirect();

        $this->assertSame('verified', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(6.0, (float) $item->fresh()->carbon_contribution);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_deleted_product_keeps_its_historical_cea_record(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Deleted Product',
            'slug' => 'deleted-product',
            'description' => 'Product retained on an order.',
            'price' => 10000,
            'stock' => 0,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $order = Order::create([
            'order_code' => 'FX-CEADELETED',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/cea-deleted.jpg',
        ]);
        $item = $order->items()->create(array_merge([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price_at_order' => 10000,
            'subtotal' => 20000,
        ], $this->carbonSnapshot(3.0, 1.0, 2)));

        $product->delete();
        app(CarbonImpactService::class)->recordEligiblePurchase($order);
        $summary = app(CarbonImpactService::class)->publicImpactSummary();

        $this->assertNull($item->fresh()->product_id);
        $this->assertSame(4.0, (float) $item->fresh()->carbon_contribution);
        $this->assertSame(4.0, $summary['unlinked_products']->first()->net_avoided_emissions);
    }

    public function test_all_dashboards_show_the_same_verified_item_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Consistent Dashboard Product',
            'slug' => 'consistent-dashboard-product',
            'description' => 'Product for dashboard metric consistency.',
            'price' => 10000,
            'stock' => 0,
            'review_status' => 'approved',
            'status' => 'published',
            'carbon_footprint_value' => 1.0,
            'carbon_footprint_unit' => 'kg CO2e',
            'carbon_baseline_emission_factor' => 4.0,
            'carbon_baseline_source' => 'Baseline source',
            'carbon_product_source' => 'Product source',
            'carbon_functional_unit' => '1 unit',
        ]);
        $order = Order::create([
            'order_code' => 'FX-CEADASHBOARD',
            'buyer_id' => $buyer->id,
            'status' => 'paid',
            'total_price' => 20000,
            'pickup_date' => now()->toDateString(),
        ]);
        $order->payment()->create([
            'gateway' => Payment::GATEWAY_MANUAL,
            'status' => 'verified',
            'proof_image' => 'proof/cea-dashboard.jpg',
        ]);
        $order->items()->create(array_merge([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price_at_order' => 10000,
            'subtotal' => 20000,
        ], $this->carbonSnapshot(4.0, 1.0, 2)));
        app(CarbonImpactService::class)->recordEligiblePurchase($order);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('6,000000 kg CO2e');
        $this->actingAs($seller->user)->get(route('seller.dashboard'))->assertOk()->assertSee('6,000000 kg CO2e');
        $this->actingAs($buyer)->get(route('orders.index'))->assertOk()->assertSee('6,000000 kg CO2e');
        $this->get(route('impact.index'))->assertOk()->assertSee('6,000000 kg CO2e');
        $this->get(route('home'))->assertOk()->assertSee('6,00');
    }

    public function test_admin_cannot_save_negative_factors_or_factor_values_without_references(): void
    {
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Validated Product',
            'slug' => 'validated-product',
            'description' => 'Product awaiting review.',
            'price' => 10000,
            'stock' => 1,
            'review_status' => 'pending_review',
            'status' => 'draft',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.products.decide', $product), [
                'decision' => 'revision_requested',
                'review_notes' => 'Please provide valid factors.',
                'carbon_baseline_emission_factor' => -1,
                'carbon_footprint_value' => 1,
                'carbon_baseline_source' => 'Baseline',
                'carbon_product_source' => 'Product',
                'carbon_functional_unit' => '1 unit',
            ])
            ->assertSessionHasErrors('carbon_baseline_emission_factor');

        $this->actingAs($admin)
            ->put(route('admin.products.decide', $product), [
                'decision' => 'revision_requested',
                'review_notes' => 'Please provide source references.',
                'carbon_baseline_emission_factor' => 2,
                'carbon_footprint_value' => 1,
                'carbon_product_source' => 'Product',
                'carbon_functional_unit' => '1 unit',
            ])
            ->assertSessionHasErrors('carbon_baseline_source');

        $this->assertNull($product->fresh()->carbon_baseline_emission_factor);
        $this->assertNull($product->fresh()->carbon_footprint_value);
    }

    /**
     * @return array<string, int|float|string>
     */
    private function carbonSnapshot(float $baselineFactor, float $productFactor, int $quantity): array
    {
        return [
            'carbon_baseline_emission_factor_at_purchase' => $baselineFactor,
            'carbon_baseline_source_at_purchase' => 'Baseline methodology',
            'carbon_footprint_value_at_purchase' => $productFactor,
            'carbon_footprint_unit_at_purchase' => CarbonImpactService::UNIT,
            'carbon_product_source_at_purchase' => 'Product methodology',
            'carbon_functional_unit_at_purchase' => '1 comparable unit',
            'carbon_quantity_at_purchase' => $quantity,
            'carbon_calculation_version_at_purchase' => CarbonImpactService::CALCULATION_VERSION,
            'carbon_snapshot_source' => 'checkout',
        ];
    }

    private function seller(): Seller
    {
        $user = User::factory()->create([
            'role' => 'seller',
            'email' => 'seller-carbon@example.test',
        ]);

        return Seller::create([
            'user_id' => $user->id,
            'store_name' => 'Kelas Penguji',
        ]);
    }
}
