<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

   //  جلب معرف المطعم الحالي للمستخدم المجهّز حالياً.

    private function getCurrentRestaurantId()
    {
        $restaurantUser = DB::table('restaurant_users')
            ->where('user_id', Auth::id())
            ->first();

        return $restaurantUser ? $restaurantUser->restaurant_id : DB::table('restaurants')->value('id');
    }

    /**
     * عرض قائمة الفواتير الخاصة بالمطعم الحالي.
     */
    public function index()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $invoices = Invoice::where('restaurant_id', $restaurantId)
            ->with(['order', 'customer'])
            ->latest()
            ->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * توليد فاتورة جديدة بناءً على طلب مكتمل أو مؤكد.
     */
    public function store(Request $request)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // التأكد من أن الطلب يتبع لنفس المطعم الحالي
        $order = Order::where('id', $request->order_id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        // التأكد من عدم وجود فاتورة سابقة لنفس الطلب
        $existingInvoice = Invoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return redirect()->back()->with('error', 'توجد فاتورة مسبقة لهذا الطلب بالفعل!');
        }

        // حساب المبالغ المالية بدقة
        $subtotal = $order->total_price;
        $tax = $request->tax_amount ?? 0;
        $discount = $request->discount ?? 0;
        $total = ($subtotal + $tax) - $discount;

        // توليد رقم فاتورة فريد ومخصص لكل مطعم على حدة لتجنب تكرار الأرقام
        $lastInvoiceCount = Invoice::where('restaurant_id', $restaurantId)->count();
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($lastInvoiceCount + 1, 4, '0', STR_PAD_LEFT);

        // إنشاء الفاتورة داخل قاعدة البيانات
        $invoice = Invoice::create([
            'restaurant_id' => $restaurantId,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'invoice_number' => $invoiceNumber,
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'discount' => $discount,
            'total_price' => $total,
            'payment_method' => $request->payment_method,
            'status' => 'paid',
            'notes' => $request->notes,
        ]);

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'تم إصدار الفاتورة بنجاح');
    }

    /**
     * عرض تفاصيل وطباعة الفاتورة.
     */
    public function show($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $invoice = Invoice::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->with(['order', 'customer', 'restaurant'])
            ->firstOrFail();

        return view('invoices.show', compact('invoice'));
    }

    /**
     * حذف الفاتورة.
     */
    public function destroy($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $invoice = Invoice::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }
}
