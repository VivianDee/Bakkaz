<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'can_view_dashboard_tab',
        'can_view_users_tab',
        'can_view_posts_tab',
        'can_view_reports_tab',
        'can_view_supports_tab',
        'can_view_logs_tab',
        'can_view_comments',
        'can_view_reactions',
        'can_view_user_views',
        'can_view_user_details',
        'can_delete',
        'can_validate',
        'can_update',
        'can_verify',
        'can_create',
        'can_view_pending_reviews_stats',
        'can_view_all_users_stats',
        'can_view_active_users_stats',
        'can_alter_admin_permissions',
        'can_alter_admin_platforms_list',
        'can_view_admin_details',
        'can_toggle_disable_admin',
        'can_update_admin_details',
        'meta_data'
    ];

    protected $hidden = [
        'meta_data'
    ];
}
