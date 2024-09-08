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
        // Users schema
        // Uncomment if the users table needs to be created
        // Schema::create('users', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('first_name');
        //     $table->string('last_name');
        //     $table->string('email')->unique();
        //     $table->string('secret_key');
        //     $table->string('password');
        //     $table->string('country');
        //     $table->string('password_history')->nullable();
        //     $table->ipAddress('ip_address')->nullable();
        //     $table->string('media')->nullable();
        //     $table->string('account_type')->nullable();
        //     $table->boolean('active_status')->default(true);
        //     $table->string('state')->nullable();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->rememberToken();
        //     $table->timestamps();
        // });

        // Assets schema
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('asset_type');
            $table->string('path');
            $table->string('size')->nullable();
            $table->string('group_id');
            $table->string('mime_type');
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Passwords schema
        Schema::create('passwords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('password');
            $table->timestamp('changed_at');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // User devices schema
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_imei')->nullable();
            $table->string('device_software_version')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Locations schema
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('city');
            $table->string('region'); // Assuming 'region' is the state
            $table->string('country');
            $table->string('postal_code');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Logins schema
        Schema::create('logins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->datetime('logged_in_at');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // OTPs schema
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->softDeletes();
        });

        // Token lives schema
        Schema::create('token_lives', function (Blueprint $table) {
            $table->id();
            $table->string('access_token_exp');
            $table->string('refresh_token_exp');
            $table->timestamps();
        });

        // Grouped assets schema
        Schema::create('grouped_assets', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id');
            $table->string('group_id');
            $table->string('asset_type');
            $table->timestamps();
            $table->softDeletes();
        });

        // Categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // // Countries table
        // Schema::create('countries', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code', 3);
        //     $table->string('name');
        //     $table->string('phone_code', 5);
        //     $table->string('flag');
        //     $table->timestamps();
        // });

        
        // Country table
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso', 2);
            $table->string('name');
            $table->string('nicename');
            $table->string('iso3', 3)->nullable();
            $table->integer('numcode')->nullable();
            $table->string('phonecode', 5);
            $table->timestamps();
        });

        // Sessions table
        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('user_id');
        //     $table->string('ip_address', 45)->nullable();
        //     $table->text('user_agent')->nullable();
        //     $table->text('payload');
        //     $table->integer('last_activity');
        //     $table->timestamps();
        // });

        // Sub_categories table
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps();
        });

        // Sub_categories_child table
        Schema::create('sub_categories_child', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories_child');
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('country');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('grouped_assets');
        Schema::dropIfExists('token_lives');
        Schema::dropIfExists('otps');
        Schema::dropIfExists('logins');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('passwords');
        Schema::dropIfExists('assets');
        // Schema::dropIfExists('users'); // Uncomment if users table needs to be dropped
    }
};
