@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark">
          <i class="fas fa-file-invoice-dollar text-primary ml-2"></i>إدارة الفواتير
        </h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="icon fas fa-check-circle ml-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-list ml-1"></i> قائمة الفواتير المصدرة</h3>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped text-nowrap align-middle">
          <thead class="thead-light">
            <tr>
              <th>رقم الفاتورة</th>
              <th>رقم الطلب</th>
              <th>العميل</th>
              <th>المبلغ الإجمالي</th>
              <th>طريقة الدفع</th>
              <th>التاريخ</th>
              <th class="text-center">الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $invoice)
              <tr>
                <td class="font-weight-bold text-primary">{{ $invoice->invoice_number }}</td>
                <td>#{{ $invoice->order_id }}</td>
                <td>{{ $invoice->customer->name ?? 'زائر' }}</td>
                <td class="font-weight-bold text-success">${{ number_format($invoice->total_price, 2) }}</td>
                <td><span class="badge badge-info px-2 py-1">{{ strtoupper($invoice->payment_method) }}</span></td>
                <td class="text-muted text-sm">{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                <td class="text-center">
                  <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-outline-primary btn-sm ml-1" title="عرض وطباعة">
                    <i class="fas fa-print"></i> معاينة / طباعة
                  </a>
                  <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="حذف"><i class="fas fa-trash-alt"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="fas fa-file-invoice fa-3x mb-3 d-block text-secondary"></i>
                  <h5>لا توجد فواتير مسجلة حالياً</h5>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection
