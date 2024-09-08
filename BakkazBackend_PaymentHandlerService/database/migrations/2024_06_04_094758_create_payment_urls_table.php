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
        Schema::create('payment_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->longText('payment_url');
            $table->datetime('expires_at')->nullable();
            $table->enum('status', ['Active', 'Expired'])->default('Active');

            
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_urls');
    }
};
