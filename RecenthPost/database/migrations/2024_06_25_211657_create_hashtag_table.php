<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("hashtags", function (Blueprint $table) {
            $table->id();
            $table->string("hashtag");
            $table->timestamps();
        });

        Schema::create("post_hashtags", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("hashtag_id");
            $table->unsignedBigInteger("post_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("hashtags", function (Blueprint $table) {
            //
        });
    }
};
