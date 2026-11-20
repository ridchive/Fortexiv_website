<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('carbon_footprint_value', 18, 6)->nullable()->change();
            $table->decimal('carbon_baseline_emission_factor', 18, 6)->nullable()->after('carbon_footprint_value');
            $table->string('carbon_baseline_source', 2000)->nullable()->after('carbon_baseline_emission_factor');
            $table->string('carbon_product_source', 2000)->nullable()->after('carbon_footprint_unit');
            $table->string('carbon_functional_unit', 120)->nullable()->after('carbon_product_source');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('carbon_footprint_value_at_purchase', 18, 6)->nullable()->change();
            $table->decimal('carbon_contribution', 20, 6)->nullable()->change();
            $table->decimal('carbon_baseline_emission_factor_at_purchase', 18, 6)
                ->nullable()
                ->after('carbon_footprint_unit_at_purchase');
            $table->string('carbon_baseline_source_at_purchase', 2000)
                ->nullable()
                ->after('carbon_baseline_emission_factor_at_purchase');
            $table->string('carbon_product_source_at_purchase', 2000)
                ->nullable()
                ->after('carbon_baseline_source_at_purchase');
            $table->string('carbon_functional_unit_at_purchase', 120)
                ->nullable()
                ->after('carbon_product_source_at_purchase');
            $table->unsignedInteger('carbon_quantity_at_purchase')
                ->nullable()
                ->after('carbon_functional_unit_at_purchase');
            $table->string('carbon_calculation_version_at_purchase', 30)
                ->nullable()
                ->after('carbon_quantity_at_purchase');
            $table->decimal('carbon_legacy_product_footprint_total', 20, 6)
                ->nullable()
                ->after('carbon_calculation_version_at_purchase');
        });

        Schema::table('carbon_impact_adjustments', function (Blueprint $table): void {
            $table->decimal('previous_contribution', 20, 6)->nullable()->change();
            $table->decimal('corrected_contribution', 20, 6)->nullable()->change();
            $table->decimal('adjustment', 20, 6)->nullable()->change();
        });

        DB::table('order_items')
            ->whereNull('carbon_baseline_emission_factor_at_purchase')
            ->whereNotNull('carbon_contribution')
            ->update([
                'carbon_legacy_product_footprint_total' => DB::raw('carbon_contribution'),
                'carbon_contribution' => null,
                'carbon_eligible_at' => null,
                'carbon_snapshot_source' => 'legacy_unverified',
            ]);

        DB::table('order_items')
            ->whereNull('carbon_baseline_emission_factor_at_purchase')
            ->whereNull('carbon_snapshot_source')
            ->update(['carbon_snapshot_source' => 'legacy_unverified']);
    }

    public function down(): void
    {
        Schema::table('carbon_impact_adjustments', function (Blueprint $table): void {
            $table->decimal('previous_contribution', 12, 4)->nullable()->change();
            $table->decimal('corrected_contribution', 12, 4)->nullable()->change();
            $table->decimal('adjustment', 12, 4)->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'carbon_baseline_emission_factor_at_purchase',
                'carbon_baseline_source_at_purchase',
                'carbon_product_source_at_purchase',
                'carbon_functional_unit_at_purchase',
                'carbon_quantity_at_purchase',
                'carbon_calculation_version_at_purchase',
                'carbon_legacy_product_footprint_total',
            ]);
            $table->decimal('carbon_footprint_value_at_purchase', 12, 4)->nullable()->change();
            $table->decimal('carbon_contribution', 12, 4)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'carbon_baseline_emission_factor',
                'carbon_baseline_source',
                'carbon_product_source',
                'carbon_functional_unit',
            ]);
            $table->decimal('carbon_footprint_value', 12, 4)->nullable()->change();
        });
    }
};
