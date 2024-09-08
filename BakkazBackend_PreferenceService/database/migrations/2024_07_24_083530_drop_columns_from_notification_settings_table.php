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
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn([
                'trending_posts',
                'message_reactions',
                'profile_visitor',
                'safety_and_account_alert'
            ]);
            $table->boolean('general_notifications')->default(true)->after('post_replies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->boolean('trending_posts')->default(false);
            $table->boolean('message_reactions')->default(false);
            $table->boolean('profile_visitor')->default(false);
            $table->boolean('safety_and_account_alert')->default(false);
        });
    }
};