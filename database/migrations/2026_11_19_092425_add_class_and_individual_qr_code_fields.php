<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('class_key', 120)->nullable();
            $table->string('class_name', 100)->nullable();
        });

        foreach (DB::table('order_fulfillments')->orderBy('id')->get() as $fulfillment) {
            $seller = DB::table('sellers')
                ->leftJoin('projects', 'sellers.project_id', '=', 'projects.id')
                ->where('sellers.id', $fulfillment->seller_id)
                ->first(['sellers.project_id', 'sellers.store_name', 'projects.class_name']);

            DB::table('order_fulfillments')
                ->where('id', $fulfillment->id)
                ->update([
                    'project_id' => $seller?->project_id,
                    'class_key' => 'legacy-seller-'.$fulfillment->seller_id,
                    'class_name' => $seller?->class_name ?? $seller?->store_name ?? 'Kelas',
                    'status' => $fulfillment->status === 'handed_over' ? 'delivered' : $fulfillment->status,
                ]);
        }

        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->unique(['order_id', 'class_key']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('order_fulfillment_id')->nullable()->constrained('order_fulfillments')->nullOnDelete();
        });

        Schema::create('order_item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('qr_token', 40)->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['order_item_id', 'status']);
        });

        foreach (DB::table('order_items')->orderBy('id')->get() as $item) {
            $fulfillment = DB::table('order_fulfillments')
                ->where('order_id', $item->order_id)
                ->where('seller_id', $item->seller_id)
                ->first();

            if (! $fulfillment) {
                throw new RuntimeException("Order item {$item->id} has no pickup group to migrate.");
            }

            DB::table('order_items')->where('id', $item->id)->update([
                'order_fulfillment_id' => $fulfillment->id,
            ]);

            $delivered = $fulfillment->status === 'delivered';

            for ($unit = 0; $unit < $item->quantity; $unit++) {
                DB::table('order_item_units')->insert([
                    'order_item_id' => $item->id,
                    'qr_token' => 'FTXP-'.Str::upper(Str::random(28)),
                    'status' => $delivered ? 'delivered' : 'pending',
                    'delivered_at' => $delivered ? $fulfillment->handed_over_at : null,
                    'delivered_by' => $delivered ? $fulfillment->handed_over_by : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('order_fulfillments')
            ->where('status', 'delivered')
            ->update(['status' => 'handed_over']);
        DB::table('order_fulfillments')
            ->where('status', 'partially_delivered')
            ->update(['status' => 'pending']);

        Schema::dropIfExists('order_item_units');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_fulfillment_id');
        });

        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'class_key']);
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['class_key', 'class_name']);
        });
    }
};
