<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_splits', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });
        Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decimal', function (Blueprint $table) {
            //
        });
    }
};
