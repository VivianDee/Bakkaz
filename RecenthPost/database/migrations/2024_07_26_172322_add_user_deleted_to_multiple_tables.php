<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserDeletedToMultipleTables extends Migration
{
    /**
     * The array of table names to which the column should be added.
     *
     * @var array
     */
    protected $tables = [
        'posts',
        'comments',
        'reactions',
        'replies',
        'favorites',
        'plans',
        'user_stats',
        'views',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'user_deleted')) {
                    $table->boolean('user_deleted')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'user_deleted')) {
                    $table->dropColumn('user_deleted');
                }
            });
        }
    }
}
