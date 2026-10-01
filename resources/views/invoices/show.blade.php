@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-receipt text-primary ml-2"></i>تفاصيل الفاتورة</h1>
      </div>
      <div class="col-sm-6 text-left">
        <button onclick="window.print();" class="btn btn-success font-weight-bold shadow-sm">
          <i class="fas fa-print ml-1"></i> طباعة الفاتورة
        </button>
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary font-weight-bold shadow-sm">
          <i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
        </a>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="invoice p-3 mb-3 bg-white rounded shadow-sm" id="printableInvoice">

      <!-- رأس الفاتورة -->
      <div class="row">
        <div class="col-12">
          <h4>
            <i class="fas fa-utensils text-primary"></i> {{ $invoice->restaurant->name ?? 'المطعم' }}
            <small class="float-left text-muted font-weight-bold" style="font-size: 16px;">التاريخ: {{ $invoice->created_at->format('Y-m-d H:i') }}</small>
          </h4>
        </div>
      </div>
      <hr>

      <!-- معلومات الفاتورة والعميل -->
      <div class="row invoice-info mb-4">
        <div class="col-sm-4 invoice-col">
          <strong>معلومات العميل:</strong>
          <address class="text-muted mt-2">
            إسم العميل: <b>{{ $invoice->customer->name ?? 'زائر' }}</b><br>
            الهاتف: {{ $invoice->customer->wa_phone_number ?? '—' }}<br>
          </address>
        </div>
        <div class="col-sm-4 invoice-col">
          <strong>تفاصيل الدفع:</strong>
          <address class="text-muted mt-2">
            طريقة الدفع: <span class="badge badge-success">{{ strtoupper($invoice->payment_method) }}</span><br>
            حالة الدفع: <span class="badge badge-primary">مدفوع</span><br>
          </address>
        </div>
        <div class="col-sm-4 invoice-col">
          <b class="text-primary" style="font-size: 18px;">{{ $invoice->invoice_number }}</b><br>
          <br>
          <b>رقم الطلب المرتبط:</b> #{{ $invoice->order_id }}<br>
        </div>
      </div>

      <!-- جدول تفاصيل المبالغ -->
      <div class="row">
        <div class="col-12 table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>الوصف / رقم الطلب</th>
                <th>طريقة الدفع المرجعية</th>
                <th class="text-left">المبلغ</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>طلب وجبة طعام رقم (#{{ $invoice->order_id }})</td>
                <td>{{ $invoice->order->payment_reference ?? 'طلب مباشر' }}</td>
                <td class="text-left">${{ number_format($invoice->subtotal, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- الإجماليات -->
      <div class="row">
        <div class="col-6">
          <p class="lead">ملاحظات:</p>
          <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
            {{ $invoice->notes ?? 'شكراً لتعاملكم معنا، نسعد بتسوقكم دائماً!' }}
          </p>
        </div>
        <div class="col-6">
          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">المجموع الفرعي:</th>
                <td>${{ number_format($invoice->subtotal, 2) }}</td>
              </tr>
              <tr>
                <th>الضريبة:</th>
                <td>${{ number_format($invoice->tax_amount, 2) }}</td>
              </tr>
              <tr>
                <th>الخصم:</th>
                <td>-${{ number_format($invoice->discount, 2) }}</td>
              </tr>
              <tr>
                <th>الإجمالي النهائي:</th>
                <td><b class="text-success" style="font-size: 18px;">${{ number_format($invoice->total_price, 2) }}</b></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- كسّارة تنسيق الطباعة (تخفي أزرار التحكم الجانبية أثناء الطباعة) -->
<style>
@media print {
  body * {
    visibility: hidden;
  }
  #printableInvoice, #printableInvoice * {
    visibility: visible;
  }
  #printableInvoice {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
  .btn, .content-header {
    display: none !important;
  }
}
</style>
@endsection
