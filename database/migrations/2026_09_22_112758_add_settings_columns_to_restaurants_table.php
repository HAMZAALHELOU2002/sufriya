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
       Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('restaurants', 'whatsapp_phone_number_id')) {
                $table->string('whatsapp_phone_number_id')->nullable();
            }
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['phone', 'whatsapp_phone_number_id', 'address']);
        });
    }
};
