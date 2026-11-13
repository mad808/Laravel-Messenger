<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_gateway_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->text('message');
            $table->tinyInteger('status')->default(0);
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'locked_at']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_gateway_messages');
    }
};