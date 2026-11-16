<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_product_categories_without_demo_data(): void
    {
        $this->seed(CategorySeeder::class);

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseHas('categories', ['slug' => 'aksesori', 'name' => 'Aksesori']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_running_twice_does_not_duplicate_categories(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(CategorySeeder::class);

        $this->assertDatabaseCount('categories', 5);
    }
}
