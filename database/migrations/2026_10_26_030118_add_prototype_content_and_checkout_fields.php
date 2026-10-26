<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('goal')->nullable();
            $table->json('process')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('story')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_contact_name', 100)->nullable();
            $table->string('pickup_contact_phone', 30)->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_version', 40)->nullable();
        });

        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->string('pickup_code', 32)->nullable()->unique();
        });

        DB::table('order_fulfillments')
            ->whereNull('pickup_code')
            ->orderBy('id')
            ->get()
            ->each(function (object $fulfillment): void {
                DB::table('order_fulfillments')
                    ->where('id', $fulfillment->id)
                    ->update(['pickup_code' => 'FTXG-'.Str::upper(Str::random(12))]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropUnique(['pickup_code']);
            $table->dropColumn('pickup_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pickup_contact_name', 'pickup_contact_phone', 'terms_accepted_at', 'terms_version']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('story');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['goal', 'process']);
        });
    }
};
