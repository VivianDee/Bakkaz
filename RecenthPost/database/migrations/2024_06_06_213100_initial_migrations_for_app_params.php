<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Posts table
        Schema::create("posts", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string("title");
            $table->string("category_id");
            $table->text("content");
            $table->timestamp("expiration_time")->nullable();
            $table->boolean("deleted")->default(false);
            $table->string("file")->nullable();
            $table->string("link")->nullable();
            $table->string("post_type")->default("post");
            $table->string("device")->nullable();
            $table->string("action")->nullable();
            $table->string("plan")->nullable();
            $table->string("package")->nullable();
            $table->string("state")->nullable();
            $table->string("city")->nullable();
            $table->boolean("paid_status")->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        // Comments table
        Schema::create("comments", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->unsignedBigInteger("user_id");
            $table->text("content");
            $table->timestamps();
            $table->softDeletes();
        });

        // Replies table
        Schema::create("replies", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("comment_id");
            $table->unsignedBigInteger("user_id");
            $table->text("content");
            $table->timestamps();
            $table->softDeletes();
        });

        // Views table
        Schema::create("views", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->unsignedBigInteger("user_id");
            $table->timestamps();
        });

        // Reactions table
        Schema::create("reactions", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("post_id")->nullable();
            $table->unsignedBigInteger("comment_id")->nullable();
            $table->unsignedBigInteger("reply_id")->nullable();
            $table->enum("type", ["like", "love", "dislike", "angry"]);
            $table->timestamps();
        });

        // Polls table
        Schema::create("polls", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string("poll_question");
            $table->string("state")->nullable();
            $table->string("city")->nullable();
            $table->string("device")->nullable();
            $table->string("post_type")->default("poll");
            $table->timestamp("expires_at");
            $table->timestamps();
        });

        // Poll options table
        Schema::create("poll_options", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("poll_id");
            $table->string("poll_option_votes");
            $table->string("option_value");
            $table->timestamps();
        });

        // Poll votes table
        Schema::create("poll_votes", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("poll_option_id");
            $table->timestamps();
        });

        // Interests table
        Schema::create("interests", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("ref_id");
            $table->string("ref_name");
            $table->boolean("interested")->default(true);
            $table->timestamps();
        });

        // Favorites table
        Schema::create("favorites", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("ref_id");
            $table->string("ref_name");
            $table->timestamps();
        });

        // Countries table
        Schema::create("countries", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->string("country_iso");
            $table->timestamps();
        });
        // Sessions table

        Schema::create("sessions", function (Blueprint $table) {
            $table->string("id")->primary();
            $table->foreignId("user_id")->nullable()->index();
            $table->string("ip_address", 45)->nullable();
            $table->text("user_agent")->nullable();
            $table->longText("payload");
            $table->integer("last_activity")->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reactions");
        // Schema::dropIfExists("reports");
        Schema::dropIfExists("views");
        Schema::dropIfExists("replies");
        Schema::dropIfExists("comments");
        Schema::dropIfExists("posts");
        Schema::dropIfExists("poll_votes");
        Schema::dropIfExists("poll_options");
        Schema::dropIfExists("polls");
        Schema::dropIfExists("interests");
        Schema::dropIfExists("favorites");
        Schema::dropIfExists("sessions");
        // Schema::dropIfExists("blocked_users");
    }
};
