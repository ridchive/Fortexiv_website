<?php

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bersihkan seller yatim piatu (project_id NULL atau project_id tidak ada di tabel projects)
        $validProjectIds = Project::pluck('id')->all();

        $orphanedSellers = Seller::whereNull('project_id')
            ->orWhereNotIn('project_id', $validProjectIds)
            ->get();

        foreach ($orphanedSellers as $seller) {
            $user = $seller->user;

            // Periksa apakah seller memiliki transaksi pesanan
            $hasOrders = OrderItem::where('seller_id', $seller->id)->exists();

            if (! $hasOrders) {
                // Hapus produk terkait seller orphan jika ada
                Product::where('seller_id', $seller->id)->delete();
                $seller->delete();
                $user?->delete();
            } else {
                // Jika memiliki riwayat transaksi, nonaktifkan akunnya agar tidak merusak relasi audit
                $seller->update(['store_name' => '[Arsip] ' . $seller->store_name]);
                $user?->update(['is_active' => false]);
            }
        }

        // 2. Bersihkan user bertipe seller yang tidak memiliki data di tabel sellers (misal akun testing 'qwe')
        $orphanedSellerUsers = User::where('role', 'seller')
            ->whereDoesntHave('seller')
            ->get();

        foreach ($orphanedSellerUsers as $user) {
            $hasOrders = DB::table('orders')->where('buyer_id', $user->id)->exists();
            if (! $hasOrders) {
                $user->delete();
            }
        }
    }

    public function down(): void
    {
        // Data sampah yang sudah dibersihkan tidak perlu di-rollback
    }
};
