<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClassManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@fortexiv.local',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_access_classes_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.classes.index'));

        $response->assertOk();
        $response->assertSee('Kelola Kelas & Akun Seller');
    }

    public function test_admin_can_initialize_11_levels(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.initialize'));

        $response->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseCount('projects', 11);

        for ($level = 1; $level <= 11; $level++) {
            $this->assertDatabaseHas('projects', [
                'grade' => $level,
                'class_name' => (string) $level,
            ]);
        }
    }

    public function test_admin_can_add_class_without_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), [
            'grade' => 1,
            'class_name' => '1',
        ]);

        $response->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseHas('projects', [
            'grade' => 1,
            'class_name' => '1',
        ]);
        $this->assertDatabaseCount('sellers', 0);
    }

    public function test_admin_can_add_class_with_seller_account_and_seller_can_login(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.store'), [
            'grade' => 1,
            'class_name' => '1',
            'username' => 'kelas1',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.classes.index'));

        $project = Project::where('grade', 1)->firstOrFail();
        $this->assertDatabaseHas('users', [
            'username' => 'kelas1',
            'role' => 'seller',
        ]);
        $this->assertDatabaseHas('sellers', [
            'project_id' => $project->id,
            'store_name' => '1',
        ]);

        // Now test seller login via /login using username and password!
        $this->post(route('logout'));

        $loginResponse = $this->post(route('login.store'), [
            'email' => 'kelas1', // input field can accept username or email
            'password' => 'secret123',
        ]);

        $loginResponse->assertRedirect(route('seller.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('seller', auth()->user()->role);
        $this->assertEquals($project->id, auth()->user()->seller->project_id);
    }

    public function test_admin_can_edit_class_and_credentials(): void
    {
        $project = Project::create([
            'grade' => 2,
            'class_name' => '2',
            'title' => 'Proyek 2',
            'introduction' => 'Intro 2',
            'is_published' => true,
        ]);

        $user = User::create([
            'name' => 'Tim 2',
            'username' => 'kelas2_old',
            'email' => 'kelas2@seller.fortexiv.local',
            'password' => 'oldpassword',
            'role' => 'seller',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Seller::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'store_name' => '2',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.classes.update', $project), [
            'grade' => 3,
            'class_name' => 'Kelas Tiga',
            'username' => 'kelas3_new',
            'password' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.classes.index'));

        $project->refresh();
        $this->assertEquals(3, $project->grade);
        $this->assertEquals('Kelas Tiga', $project->class_name);

        $user->refresh();
        $this->assertEquals('kelas3_new', $user->username);

        // Verify login with new credentials
        $this->post(route('logout'));
        $loginResponse = $this->post(route('login.store'), [
            'email' => 'kelas3_new',
            'password' => 'newpassword123',
        ]);

        $loginResponse->assertRedirect(route('seller.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_admin_can_remove_seller_account_from_class(): void
    {
        $project = Project::create([
            'grade' => 4,
            'class_name' => '4',
            'title' => 'Proyek 4',
            'introduction' => 'Intro 4',
            'is_published' => true,
        ]);

        $user = User::create([
            'name' => 'Tim 4',
            'username' => 'kelas4',
            'email' => 'kelas4@seller.fortexiv.local',
            'password' => 'password',
            'role' => 'seller',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Seller::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'store_name' => '4',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.classes.update', $project), [
            'grade' => 4,
            'class_name' => '4',
            'remove_account' => '1',
        ]);

        $response->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('sellers', ['project_id' => $project->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_admin_can_delete_class(): void
    {
        $project = Project::create([
            'grade' => 5,
            'class_name' => '5',
            'title' => 'Proyek 5',
            'introduction' => 'Intro 5',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.classes.destroy', $project));

        $response->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
