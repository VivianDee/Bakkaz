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
        Schema::create("subscriptions", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("plan_id");
            $table->enum("status", [
                "pending",
                "active",
                "expired",
                "cancelled",
            ]);
            $table->string("payment_ref")->nullable();
            $table->timestamp("payment_initialized_at")->nullable();
            $table->timestamp("payment_verified_at")->nullable();
            $table->timestamps();

            $table
                ->foreign("plan_id")
                ->references("id")
                ->on("plans")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("subscriptions");
    }
};
