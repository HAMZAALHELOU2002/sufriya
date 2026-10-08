<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('restrictOnDelete');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');

            $table->string('invoice_number')->unique(); // رقم الفاتورة التسلسلي (مثال: INV-2026-0001)
            $table->decimal('subtotal', 10, 2); // المجموع قبل الضريبة
            $table->decimal('tax_amount', 10, 2)->default(0); // قيمة الضريبة
            $table->decimal('discount', 10, 2)->default(0); // الخصم إن وجد
            $table->decimal('total_price', 10, 2); // المبلغ الإجمالي النهائي

            $table->string('payment_method')->default('cash'); // طريقة الدفع (cash, online, card)
            $table->string('status')->default('paid'); // حالة الفاتورة (paid, unpaid, refunded)
            $table->text('notes')->nullable(); // ملاحظات الفاتورة

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
