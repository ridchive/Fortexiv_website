<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order_items', 'carbon_snapshot_source')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->string('carbon_snapshot_source', 30)->nullable()->after('carbon_footprint_unit_at_purchase');
            });
        }

        DB::table('products')
            ->whereNotNull('carbon_footprint_value')
            ->update(['carbon_footprint_unit' => 'kg CO2e']);
        DB::table('order_items')
            ->whereNotNull('carbon_footprint_value_at_purchase')
            ->update([
                'carbon_footprint_unit_at_purchase' => 'kg CO2e',
            ]);

        $backfillTime = now();
        DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->where('payments.status', 'verified')
            ->whereIn('orders.status', ['paid', 'partially_picked_up', 'picked_up'])
            ->select([
                'order_items.id',
                'order_items.quantity',
                'order_items.carbon_footprint_value_at_purchase',
                'order_items.carbon_contribution',
                'order_items.carbon_eligible_at',
                'order_items.carbon_snapshot_source',
                'payments.verified_at',
            ])
            ->orderBy('order_items.id')
            ->chunkById(500, function (Collection $items) use ($backfillTime): void {
                foreach ($items as $item) {
                    $snapshot = $item->carbon_footprint_value_at_purchase;
                    $source = $item->carbon_snapshot_source;
                    $updates = [];

                    if ($snapshot === null) {
                        $updates['carbon_snapshot_source'] = 'legacy_unverified';
                        DB::table('order_items')->where('id', $item->id)->update($updates);

                        continue;
                    }

                    if ($item->carbon_contribution === null) {
                        $updates['carbon_contribution'] = round((float) $snapshot * (int) $item->quantity, 4);
                        $updates['carbon_eligible_at'] = $item->carbon_eligible_at
                            ?? $item->verified_at
                            ?? $backfillTime;
                    }

                    if ($source === null) {
                        $updates['carbon_snapshot_source'] = 'legacy_snapshot';
                    }

                    if ($updates !== []) {
                        DB::table('order_items')->where('id', $item->id)->update($updates);
                    }
                }
            }, 'order_items.id', 'id');
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'carbon_snapshot_source')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->dropColumn('carbon_snapshot_source');
            });
        }
    }
};
