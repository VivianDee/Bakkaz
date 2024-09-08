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
        Schema::create('securities', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('preference_id')->constrained('preferences')->onDelete('cascade');
            $table->boolean('remember_me')->default(true);
            $table->boolean('biometric_id')->default(false);
            $table->boolean('face_id')->default(false);
            $table->boolean('sms_authenticator')->default(false);
            $table->boolean('google_authenticator')->default(false);
            $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securities');
    }
};
