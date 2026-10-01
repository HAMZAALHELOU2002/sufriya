<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_users', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_users', 'role')) {
                $table->string('role')->default('staff'); // owner أو staff أو cashier
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
