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
        Schema::create('restaurant_users', function (Blueprint $table) {
            $table->id();
          // تعريف الحقول أولاً كـ unsignedBigInteger لتضمن تطابقها مع id
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('restaurant_id');

            $table->enum('role', ['owner', 'manager', 'staff'])->default('staff');
            $table->timestamps();

            // إضافة العلاقات صراحة
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');

            $table->unique(['user_id', 'restaurant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_users');
    }
};
