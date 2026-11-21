<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'carbon_footprint_value')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('carbon_footprint_value', 12, 4)->nullable()->after('stock');
                $table->string('carbon_footprint_unit', 30)->nullable()->after('carbon_footprint_value');
            });
        }

        if (! Schema::hasColumn('order_items', 'carbon_footprint_value_at_purchase')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->decimal('carbon_footprint_value_at_purchase', 12, 4)->nullable()->after('subtotal');
                $table->string('carbon_footprint_unit_at_purchase', 30)->nullable()->after('carbon_footprint_value_at_purchase');
                $table->decimal('carbon_contribution', 12, 4)->nullable()->after('carbon_footprint_unit_at_purchase');
                $table->timestamp('carbon_eligible_at')->nullable()->after('carbon_contribution');
                $table->index(['carbon_eligible_at', 'carbon_contribution']);
            });
        }

        Schema::dropIfExists('carbon_impact_adjustments');

        Schema::create('carbon_impact_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_item_id')->index();
            $table->integer('performed_by')->nullable()->index();
            $table->decimal('previous_contribution', 12, 4)->nullable();
            $table->decimal('corrected_contribution', 12, 4)->nullable();
            $table->decimal('adjustment', 12, 4)->nullable();
            $table->text('reason');
            $table->timestamps();
            $table->index(['order_item_id', 'created_at']);
        });

        DB::table('products')->update([
            'carbon_footprint_value' => null,
            'carbon_footprint_unit' => null,
        ]);

    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_impact_adjustments');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropIndex(['carbon_eligible_at', 'carbon_contribution']);
            $table->dropColumn([
                'carbon_footprint_value_at_purchase',
                'carbon_footprint_unit_at_purchase',
                'carbon_contribution',
                'carbon_eligible_at',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['carbon_footprint_value', 'carbon_footprint_unit']);
        });
    }
};
