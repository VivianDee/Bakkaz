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
    Schema::create('user_stats', function (Blueprint $table) {
      $table->id();
      $table->string('user_id');
      $table->integer('posts_count')->default(0);
      $table->integer('replies_count')->default(0);
      $table->integer('comments_count')->default(0);
      $table->integer('likes_count')->default(0);
      $table->integer('shares_count')->default(0);
      $table->integer('views_count')->default(0);
      $table->integer('5_star_count')->default(0);
      $table->integer('4_star_count')->default(0);
      $table->integer('3_star_count')->default(0);
      $table->integer('2_star_count')->default(0);
      $table->integer('1_star_count')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    //
  }
};
