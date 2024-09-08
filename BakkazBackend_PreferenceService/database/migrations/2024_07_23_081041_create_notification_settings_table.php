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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preference_id')->constrained('preferences')->onDelete('cascade');
            $table->boolean('new_favourite')->default(false);
            $table->boolean('likes')->default(false);
            $table->boolean('trending_posts')->default(false);
            $table->boolean('direct_messages')->default(false);
            $table->boolean('message_reactions')->default(false);
            $table->boolean('profile_visitor')->default(false);
            $table->boolean('post_comments')->default(false);
            $table->boolean('post_replies')->default(false);
            $table->boolean('safety_and_account_alert')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
