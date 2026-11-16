<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('midtrans_order_id', 50)->nullable()->unique();
        });

        // Snap transactions created before this column existed used the order code as their Midtrans order_id.
        $orderCodes = DB::table('orders')->pluck('order_code', 'id');

        DB::table('payments')
            ->where('gateway', 'midtrans')
            ->whereNotNull('snap_redirect_url')
            ->orderBy('id')
            ->get(['id', 'order_id'])
            ->each(fn (object $payment) => DB::table('payments')
                ->where('id', $payment->id)
                ->update(['midtrans_order_id' => $orderCodes[$payment->order_id] ?? null]));
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn('midtrans_order_id');
        });
    }
};
