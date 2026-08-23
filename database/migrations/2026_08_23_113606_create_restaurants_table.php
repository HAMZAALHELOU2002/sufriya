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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chain_id')->nullable()->index();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('location')->nullable();
            $table->json('business_hours')->nullable();
            $table->enum('status', ['trial', 'active', 'past_due', 'suspended'])->default('trial');
            $table->string('whatsapp_phone_number_id')->nullable()->unique();
            $table->string('whatsapp_business_account_id')->nullable();
            $table->enum('whatsapp_verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->decimal('assumed_commission_rate', 5, 2)->default(30.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
