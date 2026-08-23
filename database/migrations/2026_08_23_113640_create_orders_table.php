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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('fulfillment_type', ['pickup', 'delivery']);
            $table->enum('status', ['pending_acceptance', 'accepted', 'preparing', 'ready', 'completed', 'cancelled', 'expired'])->default('pending_acceptance');
            $table->enum('payment_method', ['online', 'cash']);
            $table->enum('payment_status', ['pending_online', 'pending_cash', 'paid', 'collected', 'failed', 'refunded'])->default('pending_cash');
            $table->decimal('total_amount', 8, 3);
            $table->integer('outbound_msg_count')->default(0);
            $table->boolean('notified')->default(false);
            $table->string('payment_reference')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
