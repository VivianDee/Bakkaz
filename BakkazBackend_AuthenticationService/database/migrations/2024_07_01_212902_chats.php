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
       
        // Create chat_rooms table
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create messages table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->unsignedBigInteger('chat_room_id')->nullable();
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes for foreign keys
            $table->index('sender_id');
            $table->index('recipient_id');
            $table->index('chat_room_id');
        });

        // Create chat_room_user pivot table
        Schema::create('chat_room_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_room_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Indexes for foreign keys
            $table->index('chat_room_id');
            $table->index('user_id');

            $table->unique(['chat_room_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_room_user');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chat_rooms');
        Schema::dropIfExists('users');
    }
};
