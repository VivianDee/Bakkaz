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
        Schema::create('premium_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preference_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->nullable();
            $table->string('payment_ref')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('payment_initialized_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            
            $table->foreign('preference_id')->references('id')->on('preferences')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('premium_posts', function (Blueprint $table) {
            //
        });
    }
};
