<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_the_replacement_classes_in_the_requested_order(): void
    {
        $legacyProject = Project::create([
            'class_name' => 'X Desain Grafis',
            'grade' => 10,
            'title' => 'Proyek lama',
            'introduction' => 'Proyek lama.',
            'story' => 'Cerita lama.',
            'outcome' => 'Hasil lama.',
        ]);
        $legacySeller = $this->seller('legacy-class@example.test');
        $legacySeller->update(['project_id' => $legacyProject->id]);
        Product::create([
            'seller_id' => $legacySeller->id,
            'project_id' => $legacyProject->id,
            'name' => 'Produk kelas lama',
            'slug' => 'produk-kelas-lama',
            'description' => 'Produk dari kelas lama.',
            'price' => 10000,
            'stock' => 10,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        foreach ([6, 9, 10, 11, 12] as $grade) {
            Project::create([
                'class_name' => "kelas {$grade}",
                'grade' => $grade,
                'title' => "Proyek kelas {$grade}",
                'introduction' => 'Proyek kelas lama.',
                'story' => 'Cerita kelas lama.',
                'outcome' => 'Hasil kelas lama.',
            ]);
        }

        $this->seed(MarketplaceSeeder::class);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'kelas 1',
                'kelas 2',
                'kelas 3',
                'kelas 4',
                'kelas 5',
                'kelas 7',
                'kelas 8',
                'ISTC basic &amp; Intermediate',
                'ISTC Advance &amp; Master',
            ], false)
            ->assertDontSee('X Desain Grafis')
            ->assertDontSee('Produk kelas lama')
            ->assertSee('Kartu Ucapan Daur Ulang')
            ->assertSee('Pot Tanah Liat Mini')
            ->assertSee('Rp 1');

        $this->assertDatabaseHas('products', [
            'slug' => 'produk-kelas-lama',
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('products', [
            'slug' => 'pot-tanah-liat-mini',
            'price' => 1,
            'status' => 'published',
            'review_status' => 'approved',
        ]);
        $this->assertDatabaseCount('products', 11);
        foreach ([6, 9, 10, 11, 12] as $grade) {
            $this->assertDatabaseHas('projects', [
                'class_name' => "kelas {$grade}",
                'is_published' => false,
            ]);
        }
    }

    public function test_project_articles_show_reference_photos_and_admin_can_preview_a_cover(): void
    {
        $project = Project::create([
            'class_name' => 'XI Agribisnis 1',
            'grade' => 11,
            'title' => 'Kebun Tempe Tanpa Plastik',
            'introduction' => 'Kelas mengolah tempe dengan daun pisang dari kebun sendiri.',
            'story' => 'Cerita kebun kelas.',
            'outcome' => 'Kebun kelas menghasilkan bahan pangan.',
            'article_body' => "## Latar belakang\n\n<script>alert('unsafe')</script>",
            'is_published' => true,
        ]);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('images.unsplash.com')
            ->assertSee('Foto referensi proyek Kebun Tempe Tanpa Plastik');

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('images.unsplash.com')
            ->assertDontSee("<script>alert('unsafe')</script>", false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.articles.create'))
            ->assertOk()
            ->assertSee('project-image-preview', false)
            ->assertSee('data-project-image-input', false)
            ->assertSee('Opsi Unggah File Markdown (.md)')
            ->assertSee('Pilih File .md')
            ->assertSee('for="article-md-file-input"', false)
            ->assertSee('images.unsplash.com');
    }

    public function test_admin_article_editor_combines_legacy_sections_into_markdown(): void
    {
        $project = Project::create([
            'class_name' => 'XI Agribisnis 1',
            'grade' => 11,
            'title' => 'Kebun Tempe Tanpa Plastik',
            'introduction' => 'Ringkasan proyek.',
            'story' => 'Latar belakang lama.',
            'goal' => 'Tujuan lama.',
            'process' => ['Langkah pertama.', 'Langkah kedua.'],
            'outcome' => 'Hasil lama.',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.articles.edit', $project))
            ->assertOk()
            ->assertSee('name="article_body"', false)
            ->assertSeeInOrder([
                '## Cerita / latar belakang',
                'Latar belakang lama.',
                '## Tujuan proyek',
                'Tujuan lama.',
                '## Proses',
                '- Langkah pertama.',
                '- Langkah kedua.',
                '## Hasil dan target',
                'Hasil lama.',
            ], false)
            ->assertDontSee('name="story"', false)
            ->assertDontSee('name="goal"', false)
            ->assertDontSee('name="process_steps"', false)
            ->assertDontSee('name="outcome"', false);
    }

    public function test_admin_can_update_project_article_markdown(): void
    {
        $project = Project::create([
            'class_name' => 'XI Agribisnis 1',
            'grade' => 11,
            'title' => 'Artikel Lama',
            'introduction' => 'Ringkasan lama.',
            'story' => 'Cerita lama.',
            'outcome' => 'Hasil lama.',
            'is_published' => false,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $markdown = "## Artikel diperbarui\n\nKonten **baru**.";

        $this->actingAs($admin)
            ->put(route('admin.articles.update', $project), [
                'class_name' => 'XI Agribisnis 1',
                'grade' => 11,
                'title' => 'Artikel Lama',
                'introduction' => 'Ringkasan lama.',
                'article_body' => $markdown,
                'is_published' => '0',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'article_body' => $markdown,
        ]);
    }

    public function test_admin_can_create_and_render_a_project_article_from_markdown_file(): void
    {
        $seller = $this->seller('markdown-article@example.test');
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Pot Kebun Mini',
            'slug' => 'pot-kebun-mini-markdown',
            'description' => 'Pot mini untuk tanaman.',
            'price' => 12000,
            'stock' => 5,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $markdown = "## Latar belakang\n\nArtikel **Markdown**.";

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'class_name' => 'XI Agribisnis 1',
                'grade' => 11,
                'title' => 'Artikel Kebun Markdown',
                'introduction' => 'Ringkasan artikel.',
                'article_body' => '',
                'md_file' => UploadedFile::fake()->createWithContent('artikel.md', $markdown),
                'product_ids' => [$product->id],
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $project = Project::query()->where('title', 'Artikel Kebun Markdown')->firstOrFail();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'article_body' => $markdown,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'project_id' => $project->id,
        ]);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('<h2>Latar belakang</h2>', false)
            ->assertSee('<strong>Markdown</strong>', false);
    }

    public function test_admin_article_creation_rejects_non_markdown_uploads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'class_name' => 'XI Agribisnis 1',
                'grade' => 11,
                'title' => 'Artikel dengan file invalid',
                'introduction' => 'Ringkasan artikel.',
                'article_body' => '',
                'md_file' => UploadedFile::fake()->createWithContent('artikel.pdf', "%PDF-1.4\nbukan markdown\n%%EOF"),
                'is_published' => '0',
            ])
            ->assertSessionHasErrors('md_file');

        $this->assertDatabaseMissing('projects', ['title' => 'Artikel dengan file invalid']);
    }

    public function test_admin_article_creation_requires_markdown_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'class_name' => 'XI Agribisnis 1',
                'grade' => 11,
                'title' => 'Artikel tanpa isi',
                'introduction' => 'Ringkasan artikel.',
                'is_published' => '0',
            ])
            ->assertSessionHasErrors('article_body');

        $this->assertDatabaseMissing('projects', ['title' => 'Artikel tanpa isi']);
    }

    public function test_admin_article_creation_rejects_markdown_files_over_the_content_limit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'class_name' => 'XI Agribisnis 1',
                'grade' => 11,
                'title' => 'Artikel terlalu panjang',
                'introduction' => 'Ringkasan artikel.',
                'article_body' => '',
                'md_file' => UploadedFile::fake()->createWithContent('artikel.md', str_repeat('a', 50001)),
                'is_published' => '0',
            ])
            ->assertSessionHasErrors('article_body');

        $this->assertDatabaseMissing('projects', ['title' => 'Artikel terlalu panjang']);
    }

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
        Product::create(['seller_id' => $seller->id, 'name' => 'Published tote', 'slug' => 'published-tote', 'description' => 'Handmade tote', 'price' => 14000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        Product::create(['seller_id' => $seller->id, 'name' => 'Draft mug', 'slug' => 'draft-mug', 'description' => 'Handmade mug', 'price' => 9000, 'stock' => 2, 'status' => 'draft']);
        Product::create(['seller_id' => $seller->id, 'name' => 'Sold out card', 'slug' => 'sold-out-card', 'description' => 'Handmade card', 'price' => 9000, 'stock' => 0, 'review_status' => 'approved', 'status' => 'published']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Konsumsi Bijak, Bumi Lestari.')
            ->assertSee('Catatan dari tim kelas')
            ->assertSee('Komplek Sekolah Alam Indonesia');

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

    public function test_public_registration_always_creates_buyer_accounts(): void
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

        $buyer = User::where('email', 'seller@example.test')->firstOrFail();
        $this->assertSame('buyer', $buyer->role);
        $this->assertTrue(password_verify('secure-password', $buyer->password));
        $this->assertNull($buyer->email_verified_at);
        $this->assertDatabaseMissing('sellers', ['user_id' => $buyer->id]);
        Notification::assertSentTo($buyer, VerifyEmail::class);

        $this->post(route('logout'));
        $this->post(route('register.store'), [
            'name' => 'Injected Admin',
            'email' => 'admin@example.test',
            'role' => 'admin',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('verification.notice'));

        $secondBuyer = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertSame('buyer', $secondBuyer->role);
        $this->assertDatabaseMissing('sellers', ['user_id' => $secondBuyer->id]);
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

    public function test_default_purchase_window_is_open_and_allows_orders_until_november_28(): void
    {
        $this->travelTo('2026-11-13 12:00:00');
        $buyer = User::factory()->create();
        $seller = $this->seller();
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk PO aktif',
            'slug' => 'produk-po-aktif',
            'description' => 'Produk tersedia selama PO.',
            'price' => 12000,
            'stock' => 3,
            'review_status' => 'approved',
            'status' => 'published',
        ]);

        $this->actingAs($buyer)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('PO aktif')
            ->assertDontSee('Jadwal PO belum tersedia')
            ->assertSee('+ Keranjang');

        $this->actingAs($buyer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Pemesanan belum tersedia')
            ->assertSee('Beli Sekarang');
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
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $otherProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Buku Catatan',
            'slug' => 'buku-catatan',
            'description' => 'Kertas daur ulang.',
            'price' => 12000,
            'stock' => 2,
            'review_status' => 'approved',
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
            'review_status' => 'approved',
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
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $buyNowProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Produk beli sekarang',
            'slug' => 'produk-beli-sekarang',
            'description' => 'Produk yang dipilih langsung.',
            'price' => 14000,
            'stock' => 3,
            'review_status' => 'approved',
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
            'review_status' => 'approved',
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
            'review_status' => 'approved',
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
            'review_status' => 'approved',
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'recycled-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 3, 'review_status' => 'approved', 'status' => 'published']);

        $this->actingAs($buyer)->get(route('products.show', $product))->assertOk()->assertSee('Tambah ke Keranjang');
        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])->assertRedirect(route('cart.index'));
        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('Kontak pengambil')
            ->assertSee('<input type="hidden" name="payment_method" value="manual">', false)
            ->assertDontSee('QRIS (Midtrans)')
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
        $productQrToken = $order->fresh('items.units')->items->first()->units->first()->qr_token;

        $this->actingAs($buyer)->post(route('payments.store', $order), [
            'method' => 'Transfer BCA',
            'proof' => UploadedFile::fake()->createWithContent('receipt.pdf', "%PDF-1.4\ntransfer receipt\n%%EOF"),
        ])->assertRedirect();
        $this->assertSame('waiting_verification', $order->fresh()->status);
        $this->assertDatabaseCount('tickets', 0);
        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi')
            ->assertSee('Pembayaran sedang diperiksa oleh admin')
            ->assertSee('QR pickup akan aktif setelah pembayaran diverifikasi')
            ->assertDontSee('Pembayaran berhasil')
            ->assertDontSee('Buka Tiket & Tampilkan QR');

        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $order->fresh()->payment;
        $this->actingAs($admin)->get(route('admin.payments'))
            ->assertOk()
            ->assertSee('Nominal Masuk Sesuai Mutasi')
            ->assertSee('Catatan Admin')
            ->assertSee('Alasan Penolakan');
        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), [
            'notes' => 'Bukti transfer tidak terbaca',
        ])->assertRedirect();
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'rejected',
            'notes' => 'Bukti transfer tidak terbaca',
        ]);
        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Pembayaran ditolak')
            ->assertSee('Bukti transfer tidak terbaca')
            ->assertSee('Unggah Ulang Bukti Pembayaran');

        $this->actingAs($buyer)->post(route('payments.store', $order), [
            'method' => 'Transfer BCA',
            'proof' => UploadedFile::fake()->createWithContent('receipt-new.pdf', "%PDF-1.4\nnew transfer receipt\n%%EOF"),
        ])->assertRedirect();
        $this->assertSame('waiting_verification', $order->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
            'notes' => null,
        ]);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('tickets', 0);

        $this->actingAs($admin)->post(route('admin.payments.verify', $payment), [
            'received_amount' => '28000',
            'notes' => 'Mutasi sesuai',
        ])->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'verified',
            'received_amount' => 28000,
            'notes' => 'Mutasi sesuai',
        ]);
        $this->assertNotNull($order->fresh()->ticket);
        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Pembayaran berhasil')
            ->assertSee('Aktif untuk pickup')
            ->assertSee('Buka Tiket & Tampilkan QR', false);

        $pickupCode = $order->fresh()->fulfillments->first()->pickup_code;
        $this->actingAs($buyer)->get(route('tickets.show', $order))
            ->assertOk()
            ->assertSee($pickupCode)
            ->assertSee('QR KELAS')
            ->assertSee('QR produk')
            ->assertSee($productQrToken)
            ->assertSee('data-qr-value="'.$pickupCode.'"', false);

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

    public function test_pending_manual_order_displays_the_configured_qris_and_copyable_amount(): void
    {
        $buyer = User::factory()->create();
        $order = Order::create([
            'order_code' => 'FX-QRIS-TEST',
            'buyer_id' => $buyer->id,
            'status' => 'pending_payment',
            'total_price' => 14000,
            'pickup_date' => now()->addDay(),
        ]);
        $qrisImage = 'images/qris-test-'.bin2hex(random_bytes(8)).'.png';
        $qrisImagePath = public_path($qrisImage);
        if (! is_dir(dirname($qrisImagePath))) {
            mkdir(dirname($qrisImagePath), 0755, true);
        }
        file_put_contents($qrisImagePath, 'test image');
        config(['marketplace.qris_image' => $qrisImage]);

        try {
            $this->actingAs($buyer)->get(route('orders.show', $order))
                ->assertOk()
                ->assertSee('src="'.asset($qrisImage).'"', false)
                ->assertSee('alt="QRIS statis resmi FORTEXIV"', false)
                ->assertSee('data-copy-payment-amount="14000"', false)
                ->assertSee('Salin Nominal')
                ->assertSee('Scan QRIS di atas, masukkan nominal yang sesuai, lalu unggah screenshot bukti pembayaran di bawah ini.');
        } finally {
            unlink($qrisImagePath);
        }
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
        $firstProduct = Product::create(['seller_id' => $firstSeller->id, 'name' => 'Class A tote', 'slug' => 'class-a-tote', 'description' => 'Tote bag', 'price' => 14000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $secondProduct = Product::create(['seller_id' => $secondSeller->id, 'name' => 'Class B card', 'slug' => 'class-b-card', 'description' => 'Greeting card', 'price' => 5000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
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
            'status' => 'delivered',
            'handed_over_by' => $firstSeller->user->id,
        ]);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $secondSeller->id,
            'status' => 'pending',
            'handed_over_by' => null,
        ]);
        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sebagian selesai')
            ->assertSee('Class A tote')
            ->assertSee('Class B card')
            ->assertSee('Diserahkan')
            ->assertSee('Menunggu pemenuhan')
            ->assertSee('Buka Tiket & Tampilkan QR', false);
        $this->actingAs($buyer)->get(route('tickets.show', $order))
            ->assertOk()
            ->assertSee($firstPickupCode)
            ->assertSee($secondPickupCode)
            ->assertSee('Sebagian diserahkan')
            ->assertSee('Menunggu pengambilan')
            ->assertSee('data-qr-download disabled', false)
            ->assertSee('data-token-download="'.$firstPickupCode.'"', false)
            ->assertSee('data-token-download="'.$secondPickupCode.'"', false);

        $this->actingAs($firstSeller->user)->post(route('seller.pickup'), ['pickup_code' => $firstPickupCode])
            ->assertSessionHasErrors('pickup_code');
        $this->assertSame('partially_picked_up', $order->fresh()->status);

        $this->actingAs($secondSeller->user)->post(route('seller.pickup'), ['pickup_code' => $secondPickupCode])
            ->assertRedirect();
        $this->assertSame('picked_up', $order->fresh()->status);
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $order->id,
            'seller_id' => $secondSeller->id,
            'status' => 'delivered',
        ]);
        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Selesai');

        $this->actingAs($firstSeller->user)
            ->post(route('seller.pickup'), ['pickup_code' => $firstPickupCode])
            ->assertSessionHasErrors([
                'pickup_code' => 'QR kelas ini sudah pernah digunakan.',
            ]);
        $this->assertSame('picked_up', $order->fresh()->status);
    }

    public function test_product_qr_delivers_one_unit_and_class_qr_delivers_all_remaining_units(): void
    {
        $buyer = User::factory()->create();
        $seller = $this->seller('qr-seller@example.test');
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'QR Seed Kit',
            'slug' => 'qr-seed-kit',
            'description' => 'Seed kit for QR verification.',
            'price' => 7000,
            'stock' => 2,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $secondProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'QR Clay Pot',
            'slug' => 'qr-clay-pot',
            'description' => 'Clay pot for QR verification.',
            'price' => 9000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);
        $buyer->cartItems()->create(['product_id' => $secondProduct->id, 'quantity' => 1]);

        $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertRedirect();

        $order = Order::with(['items.units', 'fulfillments'])->firstOrFail();
        $fulfillment = $order->fulfillments->firstOrFail();
        $units = $order->items->firstWhere('product_id', $product->id)->units;
        $secondProductUnit = $order->items->firstWhere('product_id', $secondProduct->id)->units->firstOrFail();
        $this->assertCount(2, $units);
        $this->assertNotSame($units[0]->qr_token, $units[1]->qr_token);
        $this->assertCount(1, $order->fulfillments);
        $order->update(['status' => 'paid']);
        $order->ticket()->create(['ticket_code' => 'FXT-QRORDER01', 'issued_at' => now()]);

        $this->actingAs($seller->user)->get(route('seller.pickup.scan'))
            ->assertOk()
            ->assertSee('Scan Class QR')
            ->assertSee('Scan Product QR')
            ->assertSee('DETAIL SEBELUM KONFIRMASI');

        $this->actingAs($seller->user)->postJson(route('seller.pickup.qr.preview'), [
            'type' => 'product',
            'token' => $units[0]->qr_token,
        ])->assertOk()
            ->assertJsonPath('data.products.0.name', $product->name)
            ->assertJsonPath('data.pending_count', 1);

        $this->actingAs($seller->user)->postJson(route('seller.pickup.qr.confirm'), [
            'type' => 'product',
            'token' => $units[0]->qr_token,
        ])->assertOk()
            ->assertJsonPath('status', 'delivered');

        $this->assertDatabaseHas('order_item_units', ['id' => $units[0]->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('order_item_units', ['id' => $units[1]->id, 'status' => 'pending']);
        $this->assertSame('partially_delivered', $fulfillment->fresh()->status);
        $this->assertSame('partially_picked_up', $order->fresh()->status);
        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sebagian diserahkan');

        $this->actingAs($seller->user)->postJson(route('seller.pickup.qr.preview'), [
            'type' => 'class',
            'token' => $fulfillment->pickup_code,
        ])->assertOk()
            ->assertJsonPath('data.pending_count', 2)
            ->assertJsonPath('data.products.0.delivered_count', 1)
            ->assertJsonCount(2, 'data.products')
            ->assertJsonFragment([
                'name' => $secondProduct->name,
                'quantity' => 1,
                'delivered_count' => 0,
                'pending_count' => 1,
            ]);

        $this->actingAs($seller->user)->postJson(route('seller.pickup.qr.confirm'), [
            'type' => 'class',
            'token' => $fulfillment->pickup_code,
        ])->assertOk()
            ->assertJsonPath('status', 'delivered');

        $this->assertSame('picked_up', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->ticket->picked_up_at);
        $this->assertDatabaseHas('order_item_units', ['id' => $units[1]->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('order_item_units', ['id' => $secondProductUnit->id, 'status' => 'delivered']);

        $this->actingAs($seller->user)->postJson(route('seller.pickup.qr.preview'), [
            'type' => 'product',
            'token' => $units[0]->qr_token,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['token' => 'QR produk ini sudah pernah digunakan.']);
    }

    public function test_seller_cannot_preview_or_confirm_another_sellers_product_qr(): void
    {
        $buyer = User::factory()->create();
        $seller = $this->seller('qr-owner@example.test');
        $otherSeller = $this->seller('qr-other@example.test');
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Owned Seed Kit',
            'slug' => 'owned-seed-kit',
            'description' => 'Seller-owned seed kit.',
            'price' => 7000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertRedirect();

        $order = Order::with('items.units')->firstOrFail();
        $unit = $order->items->firstOrFail()->units->firstOrFail();
        $order->update(['status' => 'paid']);
        $order->ticket()->create(['ticket_code' => 'FXT-QRORDER02', 'issued_at' => now()]);

        $this->actingAs($otherSeller->user)->postJson(route('seller.pickup.qr.preview'), [
            'type' => 'product',
            'token' => $unit->qr_token,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['token' => 'QR tidak valid atau bukan tanggung jawab seller ini.']);

        $this->actingAs($otherSeller->user)->postJson(route('seller.pickup.qr.confirm'), [
            'type' => 'product',
            'token' => $unit->qr_token,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['token' => 'QR tidak valid atau bukan tanggung jawab seller ini.']);

        $this->assertDatabaseHas('order_item_units', ['id' => $unit->id, 'status' => 'pending']);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_checkout_rejects_multiple_sellers_for_the_same_class(): void
    {
        $buyer = User::factory()->create();
        $project = Project::create([
            'class_name' => 'Kelas QR',
            'grade' => 8,
            'title' => 'Proyek QR',
            'introduction' => 'Proyek kelas untuk verifikasi QR.',
            'story' => 'Cerita kelas.',
            'outcome' => 'Produk kelas.',
        ]);
        $firstSeller = $this->seller('qr-class-a@example.test');
        $secondSeller = $this->seller('qr-class-b@example.test');
        $firstSeller->update(['project_id' => $project->id]);
        $secondSeller->update(['project_id' => $project->id]);
        $firstProduct = Product::create([
            'seller_id' => $firstSeller->id,
            'project_id' => $project->id,
            'name' => 'Class QR Product A',
            'slug' => 'class-qr-product-a',
            'description' => 'First class product.',
            'price' => 7000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $secondProduct = Product::create([
            'seller_id' => $secondSeller->id,
            'project_id' => $project->id,
            'name' => 'Class QR Product B',
            'slug' => 'class-qr-product-b',
            'description' => 'Second class product.',
            'price' => 7000,
            'stock' => 1,
            'review_status' => 'approved',
            'status' => 'published',
        ]);
        $buyer->cartItems()->createMany([
            ['product_id' => $firstProduct->id, 'quantity' => 1],
            ['product_id' => $secondProduct->id, 'quantity' => 1],
        ]);

        $this->actingAs($buyer)->from(route('checkout.show'))->post(route('checkout'), [
            'mode' => 'cart',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors([
            'cart' => 'Setiap kelas hanya dapat berisi produk dari satu seller. Pisahkan produk kelas ini pada pesanan berbeda.',
        ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $firstProduct->fresh()->stock);
        $this->assertSame(1, $secondProduct->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 2);
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

    public function test_sellers_see_product_image_previews_in_management_and_editor(): void
    {
        Storage::fake('public');
        $seller = $this->seller('preview-seller@example.test');
        $uploadedProduct = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Tas Daur Ulang',
            'slug' => 'tas-daur-ulang',
            'description' => 'Tas dari kain daur ulang.',
            'image' => 'products/tas-daur-ulang.jpg',
            'price' => 14000,
            'stock' => 2,
            'status' => 'draft',
        ]);
        Product::create([
            'seller_id' => $seller->id,
            'name' => 'Pot Tanaman',
            'slug' => 'pot-tanaman',
            'description' => 'Pot dari bahan bekas.',
            'price' => 9000,
            'stock' => 1,
            'status' => 'draft',
        ]);

        $this->actingAs($seller->user)
            ->get(route('seller.products.index'))
            ->assertOk()
            ->assertSee('seller-product-thumb', false)
            ->assertSee(Storage::disk('public')->url('products/tas-daur-ulang.jpg'))
            ->assertSee('images.unsplash.com')
            ->assertSee('Ilustrasi produk kelas');

        $this->get(route('seller.products.create'))
            ->assertOk()
            ->assertSee('seller-image-preview', false)
            ->assertSee('data-product-image-input', false)
            ->assertSee('images.unsplash.com')
            ->assertSee('data-product-generated-preview', false);

        $this->get(route('seller.products.edit', $uploadedProduct))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url('products/tas-daur-ulang.jpg'));
    }

    public function test_rejecting_a_manual_payment_allows_resubmission_without_releasing_reserved_stock(): void
    {
        $seller = $this->seller();
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'seed-kit', 'description' => 'Seeds', 'price' => 7000, 'stock' => 0, 'review_status' => 'approved', 'status' => 'published']);
        $buyer = User::factory()->create();
        $order = Order::create(['order_code' => 'FX-TEST0001', 'buyer_id' => $buyer->id, 'status' => 'waiting_verification', 'total_price' => 7000, 'pickup_date' => now()->addDay()]);
        $order->items()->create(['product_id' => $product->id, 'seller_id' => $seller->id, 'product_name' => $product->name, 'quantity' => 1, 'price_at_order' => 7000, 'subtotal' => 7000]);
        $payment = $order->payment()->create(['proof_image' => 'payment-proofs/receipt.pdf', 'method' => 'Transfer', 'status' => 'pending']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.payments.reject', $payment))
            ->assertSessionHasErrors('notes');
        $this->assertSame('waiting_verification', $order->fresh()->status);
        $this->assertSame(0, $product->fresh()->stock);

        $this->post(route('admin.payments.reject', $payment), ['notes' => 'Amount does not match'])->assertRedirect();
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertSame(0, $product->fresh()->stock);
        $this->actingAs($admin)->post(route('admin.payments.reject', $payment), ['notes' => 'Duplicate'])->assertUnprocessable();
        $this->assertSame(0, $product->fresh()->stock);
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
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun')
            ->assertDontSee('Saya ingin')
            ->assertDontSee('Menjual sebagai proyek siswa')
            ->assertDontSee('Nama toko atau kelas');
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Low stock item', 'slug' => 'low-stock-item', 'description' => 'Limited stock', 'price' => 14000, 'stock' => 1, 'review_status' => 'approved', 'status' => 'published']);
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'seed-kit', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Subtotal produk')
            ->assertSee('Total pembayaran')
            ->assertSee('Pengambilan di acara')
            ->assertSee('Komplek Sekolah Alam Indonesia, Mampang, Pancoran Mas, Depok City, West Java 16433')
            ->assertDontSee('Tanpa biaya pengiriman. Setiap grup kelas diserahkan secara terpisah.')
            ->assertSee('Lanjut ke Checkout');
        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('Komplek Sekolah Alam Indonesia, Mampang, Pancoran Mas, Depok City, West Java 16433')
            ->assertSee('Pesanan tetap akan disimpan')
            ->assertSee('Buat Pesanan & Lanjutkan Pembayaran');

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
            ->assertSee('Komplek Sekolah Alam Indonesia, Mampang, Pancoran Mas, Depok City, West Java 16433')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Poster QRIS statis FORTEXIV atau informasi transfer belum dikonfigurasi.')
            ->assertDontSee('Pembayaran berhasil')
            ->assertDontSee('Buka Tiket & Tampilkan QR');
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

    public function test_buyer_cannot_create_a_new_midtrans_checkout(): void
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'midtrans-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)
            ->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('<input type="hidden" name="payment_method" value="manual">', false)
            ->assertDontSee('QRIS (Midtrans)');

        $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        Http::assertNothingSent();
    }

    public function test_midtrans_checkout_remains_disabled_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        // CSRF verification is only skipped automatically while the environment is "testing".
        $this->withoutMiddleware(PreventRequestForgery::class);
        config([
            'services.midtrans.server_key' => 'production-server-key',
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
        $product = Product::create(['seller_id' => $this->seller()->id, 'name' => 'Recycled Tote Bag', 'slug' => 'production-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        Http::assertNothingSent();
    }

    public function test_midtrans_notification_finds_the_order_by_its_suffixed_midtrans_order_id(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $order = $this->midtransOrder(User::factory()->create());
        $order->payment()->update(['midtrans_order_id' => 'FX-MIDTRANS01-AB12CD']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/FX-MIDTRANS01-AB12CD/status' => Http::response([
                'order_id' => 'FX-MIDTRANS01-AB12CD',
                'gross_amount' => '14000.00',
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
                'transaction_id' => 'midtrans-transaction-suffixed',
            ]),
        ]);
        $notification = $this->midtransNotification($order, ['order_id' => 'FX-MIDTRANS01-AB12CD']);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_checkout_defaults_to_manual_payment_when_midtrans_is_the_only_configured_gateway(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'marketplace.bank_name' => '',
            'marketplace.account_name' => '',
            'marketplace.account_number' => '',
            'marketplace.po_start' => null,
            'marketplace.po_end' => null,
        ]);
        $buyer = User::factory()->create();
        $product = Product::create(['seller_id' => $this->seller()->id, 'name' => 'Seed kit', 'slug' => 'qris-only-seed', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)
            ->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('<input type="hidden" name="payment_method" value="manual">', false)
            ->assertSee('Manual (QRIS / Transfer Bank)')
            ->assertDontSee('QRIS (Midtrans)')
            ->assertDontSee('type="radio" name="payment_method"', false);
    }

    public function test_checkout_keeps_manual_payment_as_default_when_midtrans_is_configured(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'marketplace.bank_name' => 'Bank Test',
            'marketplace.account_name' => 'Koperasi',
            'marketplace.account_number' => '123',
            'marketplace.po_start' => null,
            'marketplace.po_end' => null,
        ]);
        $buyer = User::factory()->create();
        $product = Product::create(['seller_id' => $this->seller()->id, 'name' => 'Seed kit', 'slug' => 'two-methods-seed', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)
            ->get(route('checkout.show', ['mode' => 'cart']))
            ->assertOk()
            ->assertSee('<input type="hidden" name="payment_method" value="manual">', false)
            ->assertSee('Manual (QRIS / Transfer Bank)')
            ->assertDontSee('value="midtrans"', false)
            ->assertDontSee('type="radio" name="payment_method"', false);
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'unconfigured-midtrans-seed', 'description' => 'Seeds', 'price' => 7000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))->assertOk();

        $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_midtrans_api_failure_cannot_create_a_new_checkout(): void
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'midtrans-api-failure-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))->assertOk();

        $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        Http::assertNothingSent();
    }

    public function test_buyer_can_retry_midtrans_for_an_order_without_a_payment_url(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.base_url' => 'https://app.sandbox.midtrans.com',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'retry-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/retry-snap-token',
            ], 201),
        ]);
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);

        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Coba siapkan halaman pembayaran lagi');

        $this->actingAs($buyer)->post(route('payments.midtrans.retry', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/retry-snap-token');
        $this->actingAs($buyer)->post(route('payments.midtrans.retry', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/retry-snap-token');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'snap_token' => 'retry-snap-token',
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/retry-snap-token',
        ]);
        $midtransOrderId = $order->payment()->value('midtrans_order_id');
        $this->assertMatchesRegularExpression('/^FX-MIDTRANS01-[A-Z0-9]{6}$/', $midtransOrderId);
        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['order_id'] === $midtransOrderId);
        Http::assertSentCount(1);
    }

    public function test_buyer_cannot_retry_midtrans_for_another_buyers_order(): void
    {
        Http::preventStrayRequests();
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);

        $this->actingAs($otherBuyer)->post(route('payments.midtrans.retry', $order))
            ->assertNotFound();

        $this->assertNull($order->payment->fresh()->snap_redirect_url);
    }

    public function test_failed_midtrans_retry_keeps_order_available_for_another_attempt(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.base_url' => 'https://app.sandbox.midtrans.com',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['message' => 'Unavailable'], 500),
        ]);
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);

        $this->actingAs($buyer)->post(route('payments.midtrans.retry', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertNull($order->payment->fresh()->snap_redirect_url);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_midtrans_checkout_cannot_redirect_to_an_untrusted_payment_url(): void
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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Recycled Tote Bag', 'slug' => 'unsafe-midtrans-tote', 'description' => 'Made from upcycled fabric', 'price' => 14000, 'stock' => 2, 'review_status' => 'approved', 'status' => 'published']);
        $buyer->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)->get(route('checkout.show', ['mode' => 'cart']))->assertOk();

        $this->actingAs($buyer)->post(route('checkout'), [
            '_token' => csrf_token(),
            'mode' => 'cart',
            'payment_method' => 'midtrans',
            'pickup_contact_name' => $buyer->name,
            'pickup_contact_phone' => '081234567890',
            'accepted_terms' => '1',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        Http::assertNothingSent();
    }

    public function test_midtrans_settlement_marks_the_order_paid_and_issues_only_one_ticket(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        Log::spy();
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

        Log::shouldHaveReceived('debug')
            ->with('Midtrans notification signature validation completed.', [
                'order_id' => $order->order_code,
                'status_code' => '200',
                'gross_amount' => '14000.00',
                'server_key_configured' => true,
                'received_signature_length' => 128,
                'received_signature_is_sha512_hex' => true,
                'algorithm' => 'SHA-512(order_id + status_code + gross_amount + server_key)',
                'result' => 'valid',
            ])
            ->twice();
        Log::shouldHaveReceived('info')
            ->with('Midtrans webhook HTTP request received.', [
                'method' => 'POST',
                'content_type' => 'application/json',
            ])
            ->twice();
        Log::shouldHaveReceived('info')
            ->with('Midtrans notification received.', [
                'order_id' => $order->order_code,
                'transaction_id' => 'midtrans-transaction-1',
                'status_code' => '200',
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
            ])
            ->twice();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'verified',
            'method' => 'bank_transfer',
            'transaction_id' => 'midtrans-transaction-1',
        ]);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_midtrans_notification_with_an_invalid_signature_returns_401_without_checking_the_status_api(): void
    {
        config([
            'app.env' => 'local',
            'services.midtrans.server_key' => 'sandbox-server-key',
        ]);
        $order = $this->midtransOrder(User::factory()->create());
        $this->fakeMidtransStatus($order);
        $notification = $this->midtransNotification($order);
        $notification['signature_key'] = str_repeat('0', 128);

        $this->postJson(route('midtrans.notifications'), $notification)
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid Midtrans notification signature.']);

        Http::assertNothingSent();
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_notification_with_an_invalid_signature_does_not_settle_a_pending_transaction(): void
    {
        $order = $this->midtransOrder(User::factory()->create());
        $this->fakeMidtransStatus($order, ['transaction_status' => 'pending']);
        $notification = $this->midtransNotification($order);
        $notification['signature_key'] = str_repeat('0', 128);

        $this->postJson(route('midtrans.notifications'), $notification)->assertUnauthorized();

        Http::assertNothingSent();
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_buyer_payment_status_endpoint_reports_current_payment_and_ticket_eligibility(): void
    {
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);

        $this->actingAs($buyer)
            ->getJson(route('payments.midtrans.status', $order))
            ->assertOk()
            ->assertJson([
                'payment_status' => 'pending',
                'order_status' => 'pending_payment',
                'ticket_available' => false,
                'ticket_url' => null,
                'is_pending' => true,
            ]);

        $order->update(['status' => 'paid']);
        $order->payment()->update(['status' => 'verified']);
        $order->ticket()->create([
            'ticket_code' => 'FXT-MIDTRANS01',
            'issued_at' => now(),
        ]);

        $this->actingAs($buyer)
            ->getJson(route('payments.midtrans.status', $order))
            ->assertOk()
            ->assertJson([
                'payment_status' => 'verified',
                'order_status' => 'paid',
                'ticket_available' => true,
                'ticket_url' => route('tickets.show', $order),
                'is_pending' => false,
            ]);
    }

    public function test_buyer_cannot_read_midtrans_status_for_another_buyers_order(): void
    {
        $order = $this->midtransOrder(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->getJson(route('payments.midtrans.status', $order))
            ->assertNotFound();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_unauthenticated_buyer_cannot_read_midtrans_status(): void
    {
        $order = $this->midtransOrder(User::factory()->create());

        $this->getJson(route('payments.midtrans.status', $order))
            ->assertUnauthorized();

        $this->assertSame('pending_payment', $order->fresh()->status);
    }

    public function test_buyer_can_refresh_midtrans_status_from_the_authenticated_status_api(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);
        $this->fakeMidtransStatus($order, [
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-transaction-refresh',
        ]);

        $this->actingAs($buyer)
            ->post(route('payments.midtrans.refresh', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status', 'Status Midtrans berhasil diperiksa: settlement.');

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'verified',
            'transaction_id' => 'midtrans-transaction-refresh',
        ]);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_another_buyer_cannot_refresh_midtrans_status_for_an_order(): void
    {
        $order = $this->midtransOrder(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->post(route('payments.midtrans.refresh', $order))
            ->assertNotFound();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_status_refresh_does_not_update_an_order_when_the_api_amount_does_not_match(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $buyer = User::factory()->create();
        $order = $this->midtransOrder($buyer);
        $this->fakeMidtransStatus($order, ['gross_amount' => '1.00']);

        $this->actingAs($buyer)
            ->post(route('payments.midtrans.refresh', $order))
            ->assertUnprocessable();

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
        $product = Product::create(['seller_id' => $seller->id, 'name' => 'Seed kit', 'slug' => 'midtrans-seed-kit', 'description' => 'Seeds', 'price' => 14000, 'stock' => 0, 'review_status' => 'approved', 'status' => 'published']);
        $order = $this->midtransOrder($buyer);
        $order->items()->create(['product_id' => $product->id, 'seller_id' => $seller->id, 'product_name' => $product->name, 'quantity' => 1, 'price_at_order' => 14000, 'subtotal' => 14000]);
        $notification = $this->midtransNotification($order, ['transaction_status' => 'expire']);
        $this->fakeMidtransStatus($order, ['transaction_status' => 'expire']);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();
        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'expired']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_midtrans_capture_with_an_accepted_fraud_status_marks_the_order_paid(): void
    {
        config(['services.midtrans.server_key' => 'sandbox-server-key']);
        $order = $this->midtransOrder(User::factory()->create());
        $notification = $this->midtransNotification($order, [
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
        ]);
        $this->fakeMidtransStatus($order, [
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
        ]);

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'verified']);
        $this->assertDatabaseCount('tickets', 1);
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

        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Memverifikasi pembayaran dengan Midtrans.')
            ->assertSee('data-midtrans-payment-monitor', false)
            ->assertDontSee('Periksa status pembayaran');

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
            'midtrans_order_id' => $order->order_code,
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
