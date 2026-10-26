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
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('handed_over_at')->nullable();
            $table->foreignId('handed_over_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['order_id', 'seller_id']);
        });

        $tickets = DB::table('tickets')
            ->whereIn('order_id', DB::table('order_items')->select('order_id'))
            ->get()
            ->keyBy('order_id');

        DB::table('order_items')
            ->select('order_id', 'seller_id')
            ->distinct()
            ->orderBy('order_id')
            ->get()
            ->each(function (object $item) use ($tickets): void {
                $ticket = $tickets->get($item->order_id);
                $wasHandedOver = $ticket?->picked_up_at !== null;

                DB::table('order_fulfillments')->insert([
                    'order_id' => $item->order_id,
                    'seller_id' => $item->seller_id,
                    'status' => $wasHandedOver ? 'handed_over' : 'pending',
                    'handed_over_at' => $wasHandedOver ? $ticket->picked_up_at : null,
                    'handed_over_by' => $wasHandedOver ? $ticket->picked_up_by : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fulfillments');
    }
};
