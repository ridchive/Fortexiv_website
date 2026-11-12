<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->change();
            $table->string('gateway', 20)->default('manual')->index();
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->text('snap_redirect_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['transaction_id']);
            $table->dropIndex(['gateway']);
            $table->dropColumn(['gateway', 'transaction_id', 'snap_token', 'snap_redirect_url']);
        });
    }
};
