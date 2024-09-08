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
        Schema::create('privacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preference_id')->constrained('preferences')->onDelete('cascade');
            $table->enum('visibility', ['everyone', 'favourite', 'none'])->default('everyone');
            $table->boolean('privacy_mode')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('privacies');
    }
};
