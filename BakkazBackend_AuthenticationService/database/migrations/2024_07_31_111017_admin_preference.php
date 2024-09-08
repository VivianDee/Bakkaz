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
        /// done
        Schema::create('admin_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('permissions_id');
            $table->boolean('preference_status')->default(true);
            $table->timestamps();
        });

        /// done
        Schema::create('admin_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('platform_id');
            $table->boolean('access')->default(true);
            $table->timestamps();
        });

        /// done
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->string('description');
            $table->string('meta_data')->nullable();
            $table->timestamps();
        });


        /// done
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');


            // Tabs
            $table->boolean('can_view_dashboard_tab')->default(true);
            $table->boolean('can_view_users_tab')->default(true);
            $table->boolean('can_view_posts_tab')->default(true);
            $table->boolean('can_view_reports_tab')->default(true);
            $table->boolean('can_view_supports_tab')->default(true);
            $table->boolean('can_view_logs_tab')->default(true);

            // Content
            $table->boolean('can_view_comments')->default(true);
            $table->boolean('can_view_reactions')->default(true);
            $table->boolean('can_view_user_views')->default(true);
            $table->boolean('can_view_user_details')->default(true);

            // Actions
            $table->boolean('can_delete')->default(true);
            $table->boolean('can_validate')->default(true);
            $table->boolean('can_update')->default(true);
            $table->boolean('can_verify')->default(true);
            $table->boolean('can_create')->default(true);

            // Dashboard
            $table->boolean('can_view_pending_reviews_stats')->default(true);
            $table->boolean('can_view_all_users_stats')->default(true);
            $table->boolean('can_view_active_users_stats')->default(true);

            // Super access
            $table->boolean('can_alter_admin_permissions')->default(true);
            $table->boolean('can_alter_admin_platforms_list')->default(true);
            $table->boolean('can_view_admin_details')->default(true);
            $table->boolean('can_toggle_disable_admin')->default(true);
            $table->boolean('can_update_admin_details')->default(true);

            $table->string('meta_data')->nullable();
            $table->timestamps();
        });

      
        /// done
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ref_id');
            $table->string('ref_name');
            $table->string('action_type');
            $table->string('log_description');
            $table->string('meta_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_preferences');
        Schema::dropIfExists('admin_platforms');
        Schema::dropIfExists('platforms');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_logs');
    }
};
