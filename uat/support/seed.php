<?php

/**
 * Builds the uat database: runs the app migrations + seeders, then adds known accounts, classes and products.
 * Run by support/serve.mjs with DB_DATABASE / LARAVEL_STORAGE_PATH pointing at uat/.data.
 *
 * Fixture overview (see support/helpers.ts for the matching TypeScript constants):
 * - E2E Kelas A (seller@e2e.test, store "Toko E2E A"): Tote Bag, Notebook (published), Draft Lamp, Empty Poster (draft),
 *   Habis Terjual (published, stock 0).
 * - E2E Kelas B (seller2@e2e.test, store "Toko E2E B"): Kartu Pos (published).
 */

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! str_contains((string) config('database.connections.sqlite.database'), 'uat')) {
    fwrite(STDERR, "Refusing to seed: DB_DATABASE does not point at the uat database.\n");
    exit(1);
}

Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

$password = 'Password123!';

/**
 * @param  array{name: string, email: string, role: string, verified?: bool, is_active?: bool}  $attributes
 */
$makeUser = function (array $attributes) use ($password): User {
    $user = User::create([
        'name' => $attributes['name'],
        'email' => $attributes['email'],
        'password' => $password,
        'role' => $attributes['role'],
        'is_active' => $attributes['is_active'] ?? true,
    ]);

    if ($attributes['verified'] ?? true) {
        $user->forceFill(['email_verified_at' => now()])->save();
    }

    return $user;
};

$makeUser(['name' => 'Admin E2E', 'email' => 'admin@e2e.test', 'role' => 'admin']);
$makeUser(['name' => 'Budi Pembeli', 'email' => 'buyer@e2e.test', 'role' => 'buyer']);
$makeUser(['name' => 'Citra Pembeli', 'email' => 'buyer2@e2e.test', 'role' => 'buyer']);
$makeUser(['name' => 'Dodi Nonaktif', 'email' => 'toggle@e2e.test', 'role' => 'buyer']);
$makeUser(['name' => 'Eka Lupa Sandi', 'email' => 'reset@e2e.test', 'role' => 'buyer']);
$makeUser(['name' => 'Fajar Belum Verifikasi', 'email' => 'unverified@e2e.test', 'role' => 'buyer', 'verified' => false]);

$makeProject = fn (string $className, string $title): Project => Project::create([
    'class_name' => $className,
    'grade' => 10,
    'title' => $title,
    'introduction' => "Pengantar proyek {$title} untuk pengujian end-to-end.",
    'goal' => "Tujuan proyek {$title}.",
    'story' => "Latar belakang proyek {$title}.",
    'process' => ['Langkah pertama E2E.', 'Langkah kedua E2E.'],
    'outcome' => "Hasil proyek {$title}.",
    'is_published' => true,
]);

$projectA = $makeProject('E2E Kelas A', 'Proyek Uji Kelas A');
$projectB = $makeProject('E2E Kelas B', 'Proyek Uji Kelas B');

$sellerUserA = $makeUser(['name' => 'Sari Penjual', 'email' => 'seller@e2e.test', 'role' => 'seller']);
$sellerA = Seller::create(['user_id' => $sellerUserA->id, 'project_id' => $projectA->id, 'store_name' => 'Toko E2E A']);

$sellerUserB = $makeUser(['name' => 'Tono Penjual', 'email' => 'seller2@e2e.test', 'role' => 'seller']);
$sellerB = Seller::create(['user_id' => $sellerUserB->id, 'project_id' => $projectB->id, 'store_name' => 'Toko E2E B']);

$category = fn (string $slug): ?int => Category::where('slug', $slug)->value('id');

$products = [
    [$sellerA, $projectA, ['name' => 'E2E Tote Bag', 'slug' => 'e2e-tote-bag', 'price' => 25000, 'stock' => 10, 'status' => 'published', 'category_id' => $category('aksesori'), 'story' => 'Cerita produk E2E Tote Bag dari kain spanduk bekas.']],
    [$sellerA, $projectA, ['name' => 'E2E Notebook', 'slug' => 'e2e-notebook', 'price' => 15000, 'stock' => 5, 'status' => 'published', 'category_id' => $category('alat-tulis')]],
    [$sellerA, $projectA, ['name' => 'E2E Draft Lamp', 'slug' => 'e2e-draft-lamp', 'price' => 50000, 'stock' => 3, 'status' => 'draft', 'category_id' => $category('dekorasi')]],
    [$sellerA, $projectA, ['name' => 'E2E Empty Poster', 'slug' => 'e2e-empty-poster', 'price' => 10000, 'stock' => 0, 'status' => 'draft', 'category_id' => $category('dekorasi')]],
    [$sellerA, $projectA, ['name' => 'E2E Habis Terjual', 'slug' => 'e2e-habis-terjual', 'price' => 9000, 'stock' => 0, 'status' => 'published', 'category_id' => $category('makanan')]],
    [$sellerB, $projectB, ['name' => 'E2E Kartu Pos', 'slug' => 'e2e-kartu-pos', 'price' => 8000, 'stock' => 20, 'status' => 'published', 'category_id' => $category('alat-tulis')]],
    // UAT-58: two buyers compete for the last unit.
    [$sellerB, $projectB, ['name' => 'E2E Stok Terakhir', 'slug' => 'e2e-stok-terakhir', 'price' => 30000, 'stock' => 1, 'status' => 'published', 'category_id' => $category('dekorasi')]],
    // UAT-23: the seller changes this price after it has been ordered.
    [$sellerA, $projectA, ['name' => 'E2E Gelang Anyam', 'slug' => 'e2e-gelang-anyam', 'price' => 12000, 'stock' => 10, 'status' => 'published', 'category_id' => $category('aksesori')]],
];

foreach ($products as [$seller, $project, $product]) {
    Product::create($product + [
        'seller_id' => $seller->id,
        'project_id' => $project->id,
        'description' => "{$product['name']} buatan siswa untuk pengujian end-to-end.",
    ]);
}

echo "E2E database seeded.\n";
