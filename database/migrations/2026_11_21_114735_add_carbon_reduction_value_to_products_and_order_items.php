<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('carbon_reduction_value', 18, 6)->nullable()->after('stock');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('carbon_reduction_value_at_purchase', 18, 6)->nullable()->after('subtotal');
        });

        DB::table('products')
            ->whereNotNull('carbon_baseline_emission_factor')
            ->whereNotNull('carbon_footprint_value')
            ->update([
                'carbon_reduction_value' => DB::raw('carbon_baseline_emission_factor - carbon_footprint_value'),
            ]);

        DB::table('order_items')
            ->whereNotNull('carbon_baseline_emission_factor_at_purchase')
            ->whereNotNull('carbon_footprint_value_at_purchase')
            ->update([
                'carbon_reduction_value_at_purchase' => DB::raw('carbon_baseline_emission_factor_at_purchase - carbon_footprint_value_at_purchase'),
            ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('carbon_reduction_value_at_purchase');
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('carbon_reduction_value');
        });
    }
};
