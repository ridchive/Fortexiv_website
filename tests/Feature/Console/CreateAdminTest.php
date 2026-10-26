<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_cli_created_admin_is_verified_and_can_access_admin_dashboard(): void
    {
        $this->artisan('marketplace:create-admin', [
            'name' => 'Marketplace Admin',
            'email' => 'admin@example.test',
        ])
            ->expectsQuestion('Admin password (at least 12 characters)', 'correct-horse-battery-staple')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin@example.test')->firstOrFail();

        $this->assertNotNull($admin->email_verified_at);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
