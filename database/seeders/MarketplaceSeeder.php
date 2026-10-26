<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Makanan', 'slug' => 'makanan'],
            ['name' => 'Alat Tulis', 'slug' => 'alat-tulis'],
            ['name' => 'Aksesori', 'slug' => 'aksesori'],
            ['name' => 'Dekorasi', 'slug' => 'dekorasi'],
            ['name' => 'Perawatan', 'slug' => 'perawatan'],
        ])->mapWithKeys(fn (array $category): array => [
            $category['slug'] => Category::updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        foreach ([
            ['Class 1', 'Green Economy'],
            ['Class 2', 'Robotics Lab'],
            ['Class 3', 'Urban Farming'],
            ['Class 4', 'Digital Art'],
            ['Class 5', 'Water Cycle'],
            ['Class 6', 'Local History'],
            ['Class 7', 'Ocean Cleanup'],
            ['Class 8', 'Paper Craft'],
            ['Class 9', 'Coding Club'],
            ['Class 10', 'Textile Design'],
            ['Class 11', 'Solar Cars'],
            ['Class 12', 'Community Garden'],
        ] as [$className, $title]) {
            Project::query()
                ->where('class_name', $className)
                ->where('title', $title)
                ->update(['is_published' => false]);
        }

        $projects = [
            [
                'class_name' => 'XI Agribisnis 1',
                'grade' => 11,
                'title' => 'Kebun Tempe Tanpa Plastik',
                'introduction' => 'Kelas Agribisnis memproduksi tempe dengan bungkus daun pisang dari kebun sendiri dan mengompos sisanya.',
                'goal' => 'Mengganti seluruh kemasan tempe kelas dengan daun pisang dan menurunkan sampah plastik kelas hingga nol selama satu periode PO.',
                'story' => 'Setiap minggu kantin sekolah membuang puluhan bungkus plastik bekas tempe. Kelas XI Agribisnis 1 ingin membuktikan bahwa tempe tetap bisa dijual rapi tanpa plastik.',
                'process' => [
                    'Menanam dan merawat pisang di lahan belakang sekolah.',
                    'Menguji daya simpan tempe dalam daun pisang selama lima hari.',
                    'Mengemas, menimbang, dan memberi label tiap bungkus secara manual.',
                    'Mengumpulkan sisa daun untuk kompos kebun.',
                ],
                'outcome' => 'Target PO ini: 120 bungkus tempe dan 60 cup es krim tanpa satu pun kemasan plastik.',
            ],
            [
                'class_name' => 'XI Tata Boga 2',
                'grade' => 11,
                'title' => 'Dapur Pepes Zero Waste',
                'introduction' => 'Tata Boga menukar aluminium foil dengan daun dan memakai ikan dari kolam sekolah.',
                'goal' => 'Membuat menu pepes dan sambal yang seluruh kemasannya bisa dipakai ulang atau dikomposkan.',
                'story' => 'Pepes biasanya dikukus dengan aluminium foil yang jarang didaur ulang. Kelas XI Tata Boga 2 mencari cara agar rasa tetap sama tanpa foil.',
                'process' => [
                    'Menguji lima jenis daun untuk membungkus dan mengukus.',
                    'Menentukan resep bumbu dari kebun herbal kelas.',
                    'Mengumpulkan dan mensterilkan botol kaca dari kantin.',
                    'Menghitung porsi supaya tidak ada makanan sisa.',
                ],
                'outcome' => 'Hasil uji: daun pisang menahan uap paling baik. Target PO: 80 bungkus pepes dan 50 botol sambal.',
            ],
            [
                'class_name' => 'X Desain Grafis',
                'grade' => 10,
                'title' => 'Kertas Kedua',
                'introduction' => 'Desain Grafis mengubah kertas ujian bekas menjadi buku catatan dan kanvas bekas menjadi tote bag.',
                'goal' => 'Menunjukkan bahwa bahan bekas bisa diberi desain yang rapi dan pantas dijual.',
                'story' => 'Sekolah menghasilkan berkarung-karung kertas ujian bekas tiap semester. Kelas X Desain Grafis mengubahnya menjadi produk yang layak pakai.',
                'process' => [
                    'Memilah kertas bekas berdasarkan warna dan ketebalan.',
                    'Membubur, mencetak, dan mengeringkan lembaran baru.',
                    'Menjilid buku dengan benang katun.',
                    'Menyablon tote bag dari kanvas spanduk bekas.',
                ],
                'outcome' => 'Target PO: 70 buku catatan dan 40 tote bag, dengan sampah kertas kelas berkurang sekitar 30 kg.',
            ],
            [
                'class_name' => 'XII Kriya Kreatif',
                'grade' => 12,
                'title' => 'Dari Jelantah Jadi Cahaya',
                'introduction' => 'Kriya Kreatif memberi hidup kedua pada jelantah, tanah liat, dan kulit jeruk dari kantin.',
                'goal' => 'Mengurangi limbah dapur kantin sambil melatih keterampilan kriya yang bisa dijual.',
                'story' => 'Minyak jelantah dan limbah kulit jeruk dari kantin sering berakhir di saluran air. Kelas XII Kriya Kreatif mengolahnya menjadi lilin, sabun, dan pot.',
                'process' => [
                    'Mengumpulkan jelantah dan menyaringnya tiga kali.',
                    'Mencor lilin ke gelas kaca bekas.',
                    'Membuat sabun dingin dan memeramnya empat minggu.',
                    'Membentuk dan membakar pot di tungku sekolah.',
                ],
                'outcome' => 'Target PO: 50 lilin, 60 sabun, dan 60 pot. Seluruh wadah memakai bahan bekas.',
            ],
        ];

        $projectModels = collect($projects)->mapWithKeys(function (array $project): array {
            $model = Project::updateOrCreate(
                ['class_name' => $project['class_name']],
                [...$project, 'is_published' => true],
            );

            return [$project['class_name'] => $model];
        });

        $productsByClass = [
            'XI Agribisnis 1' => [
                ['Tempe Daun Pisang', 'makanan', 12000, 40, 'Tempe kedelai fermentasi, dibungkus daun pisang dari kebun sekolah.', 'Fermentasi tempe memakai ragi dan daun pisang dari kebun kelas, bukan plastik. Setiap bungkus ditimbang 250 gram. Sisa daun dikomposkan di kebun yang sama, jadi siklusnya tertutup.', 'published'],
                ['Es Krim Ijo Pandan', 'makanan', 10000, 8, 'Es krim pandan alpukat dalam cup daun pisang yang dijahit lidi.', 'Cup daun pisang dipilih karena bisa dikomposkan dan tahan beku. Rasa pandan dan alpukat berasal dari tanaman kebun kelas. Stok terbatas karena dibuat sehari sebelum acara.', 'published'],
                ['Keripik Tempe Panggang', 'makanan', 15000, 30, 'Keripik tempe dipanggang, bukan digoreng, dalam toples kaca.', 'Dipanggang dengan oven kelas agar tidak memakai minyak banyak. Toples kaca bisa dikembalikan saat PO berikutnya untuk potongan harga.', 'draft'],
            ],
            'XI Tata Boga 2' => [
                ['Pepes Ikan Nila', 'makanan', 22000, 25, 'Pepes ikan nila berbumbu kemangi, dibungkus daun pisang dan dikukus.', 'Ikan nila berasal dari kolam sekolah. Daun pisang menggantikan aluminium foil. Bumbu dibuat dari kebun herbal kelas Tata Boga.', 'published'],
                ['Sambal Matah Botol Kaca', 'makanan', 18000, 0, 'Sambal matah segar dalam botol kaca bekas, tahan tiga hari di kulkas.', 'Botol kaca dikumpulkan dari kantin dan disterilkan. Karena dibuat segar, jumlahnya selalu terbatas dan cepat habis.', 'published'],
            ],
            'X Desain Grafis' => [
                ['Tote Bag Kanvas Sablon', 'aksesori', 45000, 20, 'Tote bag kanvas dengan sablon tangan bertema ekosistem sekolah.', 'Kanvas berasal dari spanduk bekas yang dicuci dan dijahit ulang. Desain sablon dibuat siswa dengan tinta berbasis air.', 'published'],
                ['Buku Catatan Kertas Daur Ulang', 'alat-tulis', 28000, 35, 'Buku catatan 80 halaman dari kertas ujian bekas yang dibubur ulang.', 'Kertas bekas dibubur, dicetak, lalu dijilid tangan. Tiap buku sedikit berbeda karena tekstur kertasnya berbeda.', 'published'],
                ['Paket Stiker Satwa Sekolah', 'alat-tulis', 15000, 50, 'Sepuluh stiker vinil bergambar satwa yang hidup di kebun sekolah.', 'Ilustrasi dibuat dari pengamatan lapangan oleh siswa.', 'draft'],
            ],
            'XII Kriya Kreatif' => [
                ['Pot Tanah Liat Mini', 'dekorasi', 20000, 28, 'Pot tanah liat mini dengan bibit lidah buaya.', 'Dibentuk tangan dari tanah liat lokal dan dibakar di tungku sekolah. Setiap pot dijual bersama satu bibit.', 'published'],
                ['Lilin Minyak Jelantah', 'dekorasi', 35000, 15, 'Lilin dari minyak jelantah kantin yang disaring, dalam gelas kaca bekas.', 'Jelantah dari kantin disaring tiga kali sebelum dicor menjadi lilin. Nyala bersih, tanpa bau gorengan.', 'published'],
                ['Sabun Batang Kulit Jeruk', 'perawatan', 17000, 40, 'Sabun batang dengan serbuk kulit jeruk dan minyak kelapa.', 'Kulit jeruk dari kantin dikeringkan lalu digiling. Sabun dibuat dengan metode dingin dan diperam selama empat minggu.', 'draft'],
            ],
        ];

        foreach ($productsByClass as $className => $products) {
            $project = $projectModels->get($className);
            $email = 'demo+'.Str::slug($className).'@fortexiv.invalid';
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Tim '.$className,
                    'password' => Str::random(64),
                    'role' => 'seller',
                    'is_active' => true,
                ],
            );
            $seller = Seller::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['project_id' => $project->id, 'store_name' => $className, 'description' => 'Kelas peserta FORTEXIV.'],
            );

            foreach ($products as [$name, $categorySlug, $price, $stock, $description, $story, $status]) {
                Product::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'seller_id' => $seller->id,
                        'project_id' => $project->id,
                        'category_id' => $categories->get($categorySlug)->id,
                        'name' => $name,
                        'description' => $description,
                        'story' => $story,
                        'price' => $price,
                        'stock' => $stock,
                        'status' => $status,
                    ],
                );
            }
        }
    }
}
