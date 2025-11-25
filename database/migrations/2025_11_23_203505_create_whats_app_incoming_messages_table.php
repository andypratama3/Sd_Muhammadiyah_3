<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_incoming_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique();
            $table->string('phone');
            $table->enum('type', ['text', 'image', 'document', 'button', 'interactive']);
            $table->json('content')->nullable();
            $table->string('profile_name')->nullable();
            $table->enum('status', ['received', 'processed', 'replied'])->default('received');
            $table->timestamps();

            $table->index('phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_incoming_messages');
    }
};