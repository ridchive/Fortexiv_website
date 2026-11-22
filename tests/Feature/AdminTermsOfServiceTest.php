<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminTermsOfServiceTest extends TestCase
{
    use RefreshDatabase;

    public static function nonAdminRoles(): array
    {
        return [
            'buyer' => ['buyer'],
            'seller' => ['seller'],
            'finance' => ['finance'],
            'pickup staff' => ['pickup_staff'],
        ];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_roles_cannot_manage_terms(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);
        if ($role === 'seller') {
            $user->seller()->create(['store_name' => 'Toko Uji']);
        }

        $this->actingAs($user)
            ->post(route('admin.terms.publish'), ['sections' => []])
            ->assertForbidden();

        $this->assertDatabaseCount('terms_versions', 1);
    }

    public function test_guest_is_redirected_from_terms_editor(): void
    {
        $this->get(route('admin.terms.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_open_terms_management_from_the_static_content_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.terms.edit'))
            ->assertOk()
            ->assertSee('Terms of Service')
            ->assertSee('Konten Statis')
            ->assertSee('Simpan Draft')
            ->assertSee('Publikasikan')
            ->assertSee('Riwayat perubahan')
            ->assertSee(route('admin.terms.edit'), false);
    }

    public function test_public_terms_render_the_initial_content_from_database(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Pre-order dan pembayaran')
            ->assertSee('Pengambilan pesanan')
            ->assertSee('Pesanan hanya dapat dibuat selama jendela PO aktif.')
            ->assertSee('Versi prototype-1.0');
    }

    public function test_admin_can_save_a_draft_without_changing_published_terms(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Konten']);

        $this->actingAs($admin)
            ->post(route('admin.terms.draft'), [
                'sections' => [
                    ['key' => 'new-section', 'title' => 'Bagian draft', 'content' => '<p>Isi yang belum diterbitkan.</p>', 'position' => 0],
                ],
            ])
            ->assertRedirect(route('admin.terms.edit'))
            ->assertSessionHas('status');

        $draft = TermsVersion::query()->where('status', 'draft')->firstOrFail();
        $this->assertSame($admin->id, $draft->created_by);
        $this->assertSame('Bagian draft', $draft->sections[0]['title']);
        $this->assertStringContainsString('Ditambahkan', $draft->summary);
        $this->assertDatabaseCount('terms_versions', 2);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Pre-order dan pembayaran')
            ->assertDontSee('Bagian draft')
            ->assertDontSee('Isi yang belum diterbitkan.');
    }

    public function test_preview_sanitizes_unsafe_markup_without_creating_a_version(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.terms.preview'), [
                'sections' => [
                    [
                        'key' => 'preview',
                        'title' => 'Bagian pratinjau',
                        'content' => '<p onclick="alert(1)">Teks <strong>penting</strong></p><script>alert("unsafe")</script><a href="javascript:alert(1)">tautan berbahaya</a><a href="https://example.test/terms">tautan aman</a>',
                        'position' => 0,
                    ],
                ],
            ])
            ->assertOk()
            ->assertSee('Pratinjau')
            ->assertSee('<strong>penting</strong>', false)
            ->assertSee('href="https://example.test/terms"', false)
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('alert("unsafe")', false);

        $this->assertDatabaseCount('terms_versions', 1);
    }

    public function test_admin_can_publish_ordered_sections_and_old_order_approval_stays_unchanged(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Publikasi']);
        $originalVersion = TermsVersion::currentPublished();
        $buyer = User::factory()->create(['role' => 'buyer']);
        $acceptedAt = now()->subMinute();
        $order = Order::query()->create([
            'order_code' => 'FX-00000001',
            'buyer_id' => $buyer->id,
            'status' => 'pending_payment',
            'total_price' => 10000,
            'pickup_date' => now()->toDateString(),
            'terms_accepted_at' => $acceptedAt,
            'terms_version_id' => $originalVersion->id,
            'terms_version' => $originalVersion->version_label,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.terms.publish'), [
                'sections' => [
                    ['key' => 'second', 'title' => 'Bagian Kedua', 'content' => '<p>Isi kedua.</p>', 'position' => 1],
                    ['key' => 'first', 'title' => 'Bagian Pertama', 'content' => '<p>Isi pertama.</p>', 'position' => 0],
                ],
            ])
            ->assertRedirect(route('admin.terms.edit'))
            ->assertSessionHas('status');

        $newVersion = TermsVersion::currentPublished();
        $this->assertSame('published', $newVersion->status);
        $this->assertSame('tos-'.$newVersion->id, $newVersion->version_label);
        $this->assertSame($admin->id, $newVersion->published_by);
        $this->assertSame('archived', $originalVersion->fresh()->status);
        $this->assertSame('Bagian Pertama', $newVersion->sections[0]['title']);
        $this->assertSame('Bagian Kedua', $newVersion->sections[1]['title']);
        $this->assertDatabaseCount('terms_versions', 2);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSeeInOrder(['Bagian Pertama', 'Bagian Kedua'])
            ->assertSee('Versi '.$newVersion->version_label);

        $unchangedOrder = $order->fresh('termsVersion');
        $this->assertSame($originalVersion->id, $unchangedOrder->terms_version_id);
        $this->assertSame('prototype-1.0', $unchangedOrder->terms_version);
        $this->assertSame($acceptedAt->timestamp, $unchangedOrder->terms_accepted_at->timestamp);
        $this->assertSame('archived', $unchangedOrder->termsVersion->status);
    }

    public function test_admin_can_restore_a_historical_version_to_a_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $originalVersion = TermsVersion::currentPublished();
        $this->actingAs($admin)->post(route('admin.terms.publish'), [
            'sections' => [
                ['key' => 'replacement', 'title' => 'Versi pengganti', 'content' => '<p>Konten terkini.</p>', 'position' => 0],
            ],
        ])->assertRedirect(route('admin.terms.edit'));
        $publishedVersion = TermsVersion::currentPublished();

        $this->actingAs($admin)
            ->post(route('admin.terms.restore', $originalVersion))
            ->assertRedirect(route('admin.terms.edit'))
            ->assertSessionHas('status');

        $draft = TermsVersion::query()->where('status', 'draft')->firstOrFail();
        $this->assertSame('Pre-order dan pembayaran', $draft->sections[0]['title']);
        $this->assertStringContainsString('prototype-1.0', $draft->summary);
        $this->assertSame($publishedVersion->id, TermsVersion::currentPublished()->id);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Versi pengganti')
            ->assertDontSee('Pre-order dan pembayaran');
    }
}
