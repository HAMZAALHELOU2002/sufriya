<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique();
            $table->string('phone_number')->nullable();
            $table->string('direction')->default('incoming');
            $table->string('status')->default('processing');
            $table->text('message')->nullable();
            $table->text('reply')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['phone_number', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
