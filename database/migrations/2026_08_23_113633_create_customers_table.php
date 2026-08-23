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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
             $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('wa_phone_number');
            $table->string('name')->nullable();
            $table->timestamp('last_ordered_at')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id', 'wa_phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
