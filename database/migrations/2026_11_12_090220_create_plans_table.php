<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration_days')->default(30);
            $table->json('features')->nullable();
            
            $table->boolean('accepts_crypto')->default(false);
            $table->string('bep20_wallet_address')->nullable();
            
            $table->integer('max_file_size_mb')->default(15);
            $table->integer('max_story_duration_sec')->default(30);
            $table->boolean('can_hide_profile')->default(false);
            $table->boolean('can_block_screenshots')->default(false);
            $table->boolean('has_anonymous_mode')->default(false);
            $table->boolean('can_set_profile_music')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};