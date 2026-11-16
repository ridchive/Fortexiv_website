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
        $this->call(CategorySeeder::class);
        $categories = Category::query()->get()->keyBy('slug');

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

        $legacyClassNames = [
            'kelas 6',
            'kelas 9',
            'kelas 10',
            'kelas 11',
            'kelas 12',
            'XI Agribisnis 1',
            'XI Tata Boga 2',
            'X Desain Grafis',
            'XII Kriya Kreatif',
        ];

        foreach (Project::query()->whereIn('class_name', $legacyClassNames)->get() as $legacyProject) {
            $legacyProject->update(['is_published' => false]);
            $legacyProject->products()->where('status', 'published')->update(['status' => 'draft']);
        }

        $projects = [
            [
                'class_name' => 'kelas 1',
                'grade' => 1,
                'title' => 'Kartu Cerita dari Kertas Daur Ulang',
                'introduction' => 'Kelas 1 mengolah kertas bekas menjadi kartu ucapan yang bisa dipakai kembali.',
                'goal' => 'Mengenalkan pemilahan kertas dan kreativitas melalui produk sederhana yang bermanfaat.',
                'story' => 'Kertas yang masih bersih dikumpulkan, dipilah, lalu diolah menjadi kartu dengan hiasan buatan siswa. Proyek ini mengajak kelas 1 mengurangi kertas terbuang sambil belajar membuat karya yang dapat dibagikan.',
                'process' => [
                    'Mengumpulkan dan memilah kertas yang masih layak pakai.',
                    'Membentuk kartu dan menghiasnya dengan bahan yang tersedia.',
                    'Memeriksa hasil akhir dan menyiapkan kemasan sederhana.',
                ],
                'outcome' => 'Kartu buatan tangan yang mendorong kebiasaan menggunakan kembali kertas.',
            ],
            [
                'class_name' => 'kelas 2',
                'grade' => 2,
                'title' => 'Pot Mini untuk Kebun Kelas',
                'introduction' => 'Kelas 2 membuat pot mini dari wadah yang sudah tidak terpakai.',
                'goal' => 'Mengajak siswa merawat tanaman dan melihat manfaat guna ulang bahan sehari-hari.',
                'story' => 'Wadah bekas yang dibersihkan dihias dan digunakan sebagai pot untuk tanaman kecil. Setiap kelompok merawat tanamannya dan mencatat perubahan yang terlihat.',
                'process' => [
                    'Memilih wadah bekas yang aman dan membersihkannya.',
                    'Menghias wadah dan menyiapkannya sebagai pot.',
                    'Menanam bibit dan membuat jadwal perawatan.',
                ],
                'outcome' => 'Pot mini dan pengalaman merawat tanaman bersama.',
            ],
            [
                'class_name' => 'kelas 3',
                'grade' => 3,
                'title' => 'Tempat Pensil Karton Guna Ulang',
                'introduction' => 'Kelas 3 merancang tempat pensil dari karton yang dapat digunakan kembali.',
                'goal' => 'Mengenalkan proses merancang, membuat, dan menghias benda fungsional.',
                'story' => 'Potongan karton sisa disusun menjadi tempat pensil sederhana. Siswa mencoba beberapa bentuk, memilih desain yang stabil, lalu menghiasnya sesuai ide masing-masing.',
                'process' => [
                    'Mengukur dan memotong karton sesuai pola.',
                    'Menyatukan bagian-bagian menjadi tempat pensil.',
                    'Menghias dan menguji kestabilan produk.',
                ],
                'outcome' => 'Tempat pensil sederhana dari karton sisa yang masih layak pakai.',
            ],
            [
                'class_name' => 'kelas 4',
                'grade' => 4,
                'title' => 'Aksesori dari Kain Perca',
                'introduction' => 'Kelas 4 mengubah sisa kain menjadi aksesori kecil yang dapat digunakan sehari-hari.',
                'goal' => 'Mengurangi sisa bahan kain dan melatih keterampilan membuat produk.',
                'story' => 'Sisa kain dengan warna dan motif berbeda dipilih, dipotong, lalu dirangkai menjadi aksesori. Setiap produk memiliki kombinasi kain yang berbeda.',
                'process' => [
                    'Memilah dan membersihkan kain perca.',
                    'Membuat pola dan merangkai potongan kain.',
                    'Memeriksa jahitan dan merapikan hasilnya.',
                ],
                'outcome' => 'Aksesori kain buatan kelas dengan bahan yang dimanfaatkan kembali.',
            ],
            [
                'class_name' => 'kelas 5',
                'grade' => 5,
                'title' => 'Paket Kebun Mini',
                'introduction' => 'Kelas 5 menyiapkan paket sederhana untuk mulai menanam di rumah.',
                'goal' => 'Mendorong kegiatan menanam dan mengenalkan perawatan tanaman dasar.',
                'story' => 'Paket kebun mini berisi kebutuhan awal untuk menanam. Kelas 5 menyusun panduan singkat agar pembeli dapat mencoba menanam dan merawat bibit sendiri.',
                'process' => [
                    'Memilih bibit dan wadah tanam yang sesuai.',
                    'Menyiapkan media tanam dan panduan perawatan.',
                    'Mengemas paket dengan bahan yang dapat digunakan ulang.',
                ],
                'outcome' => 'Paket awal untuk mencoba menanam dan merawat tanaman di rumah.',
            ],
            [
                'class_name' => 'kelas 7',
                'grade' => 7,
                'title' => 'Tas Pakai Ulang',
                'introduction' => 'Kelas 7 membuat tas sederhana yang dapat dipakai berulang kali.',
                'goal' => 'Mengajak warga sekolah membawa tas sendiri saat berbelanja.',
                'story' => 'Tas kain dirancang agar mudah dibawa dan digunakan kembali. Siswa memilih desain sederhana dan menghiasnya dengan gambar buatan kelas.',
                'process' => [
                    'Menyiapkan bahan dan menentukan ukuran tas.',
                    'Merakit bagian tas dan memeriksa kekuatannya.',
                    'Menghias tas dan menyiapkannya untuk digunakan.',
                ],
                'outcome' => 'Tas pakai ulang sebagai alternatif untuk membawa barang sehari-hari.',
            ],
            [
                'class_name' => 'kelas 8',
                'grade' => 8,
                'title' => 'Dekorasi dari Bahan Guna Ulang',
                'introduction' => 'Kelas 8 merancang dekorasi meja dari bahan yang masih bisa dimanfaatkan.',
                'goal' => 'Mengembangkan kreativitas sambil mengurangi bahan yang terbuang.',
                'story' => 'Bahan-bahan bersih yang tersedia di kelas dipilah dan disusun menjadi dekorasi meja. Siswa membuat desain yang berbeda dan memastikan setiap karya tetap fungsional.',
                'process' => [
                    'Memilah bahan yang aman dan masih layak digunakan.',
                    'Membuat rancangan dan menyusun bagian dekorasi.',
                    'Merapikan hasil karya dan menguji kestabilannya.',
                ],
                'outcome' => 'Dekorasi meja hasil karya siswa dengan bahan guna ulang.',
            ],
            [
                'class_name' => 'ISTC basic & Intermediate',
                'grade' => 1,
                'title' => 'Kit Hidroponik Dasar',
                'introduction' => 'Peserta ISTC basic & Intermediate mengenal sistem tanam sederhana melalui kit hidroponik.',
                'goal' => 'Memberi pengalaman praktik merakit dan merawat tanaman dengan metode hidroponik.',
                'story' => 'Kit ini dirancang untuk membantu peserta memahami kebutuhan dasar tanaman dan cara merawatnya. Materi proyek mencakup pengenalan alat, perakitan, serta pencatatan perkembangan tanaman.',
                'process' => [
                    'Mengenali komponen kit dan fungsi masing-masing.',
                    'Merakit media tanam dan menempatkan bibit.',
                    'Mengamati dan mencatat perkembangan tanaman.',
                ],
                'outcome' => 'Kit praktik dan panduan awal untuk belajar hidroponik.',
            ],
            [
                'class_name' => 'ISTC Advance & Master',
                'grade' => 2,
                'title' => 'Lampu Meja Bahan Guna Ulang',
                'introduction' => 'Peserta ISTC Advance & Master merancang lampu meja dekoratif dari bahan guna ulang.',
                'goal' => 'Mengembangkan keterampilan desain dan perakitan melalui proyek produk yang lebih kompleks.',
                'story' => 'Proyek ini menggabungkan perencanaan bentuk, pemilihan bahan, dan proses perakitan menjadi sebuah lampu meja dekoratif. Setiap produk diperiksa sebelum dipresentasikan.',
                'process' => [
                    'Membuat rancangan bentuk dan memilih bahan yang sesuai.',
                    'Merakit bagian dekoratif dan dudukan lampu.',
                    'Memeriksa hasil rakitan sebelum produk ditampilkan.',
                ],
                'outcome' => 'Prototipe lampu meja dekoratif yang dibuat melalui proses desain dan perakitan.',
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
            'kelas 1' => [
                ['Kartu Ucapan Daur Ulang', 'alat-tulis', 12000, 30, 'Kartu ucapan buatan tangan dari kertas yang dimanfaatkan kembali.', 'Setiap kartu dihias dengan ilustrasi sederhana buatan siswa. Cocok untuk pesan singkat dan dapat didaur ulang setelah digunakan.', 'published'],
            ],
            'kelas 2' => [
                ['Pot Mini Kebun Kelas', 'dekorasi', 18000, 20, 'Pot mini hasil hiasan kelas untuk tanaman kecil.', 'Wadah dibersihkan dan dihias oleh siswa. Tanaman dan media tanam tidak termasuk.', 'published'],
            ],
            'kelas 3' => [
                ['Tempat Pensil Karton', 'alat-tulis', 15000, 25, 'Tempat pensil sederhana dari karton sisa yang masih layak pakai.', 'Dibuat dan dihias oleh siswa. Motif dan warna dapat berbeda pada setiap produk.', 'published'],
            ],
            'kelas 4' => [
                ['Gantungan Kunci Kain Perca', 'aksesori', 12000, 30, 'Gantungan kunci kecil dari potongan kain perca.', 'Setiap produk memakai kombinasi sisa kain yang berbeda dan dirangkai secara manual.', 'published'],
            ],
            'kelas 5' => [
                ['Paket Kebun Mini', 'dekorasi', 25000, 20, 'Paket contoh untuk mulai menanam tanaman di rumah.', 'Berisi pot kecil, media tanam, dan panduan sederhana. Jenis bibit mengikuti ketersediaan.', 'published'],
            ],
            'kelas 7' => [
                ['Tas Kain Pakai Ulang', 'aksesori', 35000, 18, 'Tas kain sederhana untuk membawa barang sehari-hari.', 'Dirancang agar dapat digunakan berulang kali. Motif hiasan dibuat oleh siswa.', 'published'],
            ],
            'kelas 8' => [
                ['Dekorasi Meja Guna Ulang', 'dekorasi', 22000, 15, 'Dekorasi meja kecil dari bahan yang masih dapat dimanfaatkan.', 'Dibuat manual dari bahan yang telah dipilah dan dibersihkan. Bentuk produk dapat berbeda.', 'published'],
            ],
            'ISTC basic & Intermediate' => [
                ['Kit Hidroponik Dasar', 'dekorasi', 45000, 12, 'Kit praktik sederhana untuk mengenal cara menanam secara hidroponik.', 'Paket contoh berisi komponen dasar dan panduan perakitan. Periksa petunjuk sebelum digunakan.', 'published'],
            ],
            'ISTC Advance & Master' => [
                ['Lampu Meja Dekoratif', 'dekorasi', 55000, 10, 'Lampu meja dekoratif hasil rancangan dan perakitan peserta.', 'Produk contoh yang dibuat melalui proses desain kelas. Spesifikasi lampu mengikuti unit yang tersedia.', 'published'],
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
                        'review_status' => 'approved',
                    ],
                );
            }
        }
    }
}
