<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group', 'channel'])->default('private');
            $table->string('title')->nullable();
            $table->string('avatar')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('invite_link')->unique()->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->index('type');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};