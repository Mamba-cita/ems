<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique()->nullable();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password_hash');
            $table->string('role')->default('user');
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('profile_pic')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('youtube_channel_id')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
