<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Product categories only, with no demo data, so it is safe to run on production:
 * `php artisan db:seed --class=CategorySeeder --force`. Re-running it does not duplicate rows.
 */
class CategorySeeder extends Seeder
{
    /**
     * @var list<array{name: string, slug: string}>
     */
    public const CATEGORIES = [
        ['name' => 'Makanan', 'slug' => 'makanan'],
        ['name' => 'Alat Tulis', 'slug' => 'alat-tulis'],
        ['name' => 'Aksesori', 'slug' => 'aksesori'],
        ['name' => 'Dekorasi', 'slug' => 'dekorasi'],
        ['name' => 'Perawatan', 'slug' => 'perawatan'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
