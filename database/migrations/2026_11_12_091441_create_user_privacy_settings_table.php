<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_privacy_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            $table->boolean('hide_profile_photo')->default(false);
            $table->boolean('hide_last_seen')->default(false);
            $table->boolean('hide_online_status')->default(false);
            $table->boolean('block_screenshots')->default(false); 
            $table->boolean('hide_read_receipts')->default(false);
            $table->boolean('anonymous_mode')->default(false); 
            
            $table->string('profile_music_url')->nullable();
            $table->boolean('show_profile_music')->default(true);
            $table->boolean('hide_phone_number')->default(true);
            $table->boolean('hide_stories_from_non_contacts')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_privacy_settings');
    }
};