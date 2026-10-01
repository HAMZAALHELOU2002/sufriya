@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark">
          <i class="fas fa-crown text-warning ml-2"></i> إدارة اشتراك المنصة
        </h1>
      </div>
      <div class="col-sm-6 text-left">
        <span class="text-muted small font-weight-bold">متابعة حالة باقاتك وتجديد الاشتراك</span>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
        <i class="fas fa-check-circle ml-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
        <i class="fas fa-exclamation-triangle ml-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
    @endif

    {{-- حالة الاشتراك الحالي --}}
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card card-outline card-primary shadow-sm border-0 rounded-lg">
          <div class="card-body p-4">
            <h4 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle text-primary ml-1"></i> معلومات اشتراك مطعمك</h4>
            @if($subscription && $subscription->status == 'active' && \Carbon\Carbon::parse($subscription->billing_cycle_date)->isFuture())
              <div class="alert alert-success border-0 rounded-lg mb-3">
                <h5><i class="fas fa-shield-alt ml-1"></i> اشتراكك ساري المفعول</h5>
                <p class="mb-1">الباقة الحالية: <strong>{{ $subscription->plan }}</strong></p>
                <p class="mb-0">تاريخ انتهاء الدورة: <strong>{{ \Carbon\Carbon::parse($subscription->billing_cycle_date)->format('Y-m-d') }}</strong> ({{ \Carbon\Carbon::parse($subscription->billing_cycle_date)->diffForHumans() }})</p>
              </div>
            @else
              <div class="alert alert-danger border-0 rounded-lg mb-3">
                <h5><i class="fas fa-exclamation-circle ml-1"></i> اشتراكك منتهي أو غير متوفر</h5>
                <p class="mb-0">يرجى اختيار باقة أدناه وتجديد الاشتراك لاستعادة كامل صلاحيات استقبال الطلبات.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- باقات الاشتراك المتاحة --}}
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-lg subscription-card">
          <div class="card-header bg-gradient-primary text-white text-center py-4">
            <h3 class="font-weight-bold m-0"><i class="fas fa-star ml-1"></i> الباقة الشهرية الشاملة</h3>
            <span class="badge badge-light text-primary mt-2 px-3 py-1 font-weight-bold rounded-pill">الأكثر طلباً</span>
          </div>
          <div class="card-body p-4 text-center">
            <h1 class="display-4 font-weight-bold text-primary mb-3">99 <small class="h5 text-muted">ر.س / شهرياً</small></h1>
            <ul class="list-unstyled text-right mb-4 pr-3">
              <li class="mb-2"><i class="fas fa-check text-success ml-2"></i> استقبال الطلبات الحية والـ POS</li>
              <li class="mb-2"><i class="fas fa-check text-success ml-2"></i> إدارة المنيو والأقسام غير محدودة</li>
              <li class="mb-2"><i class="fas fa-check text-success ml-2"></i> ربط بوت واتساب للإشعارات</li>
              <li class="mb-2"><i class="fas fa-check text-success ml-2"></i> تقارير وتحليلات المبيعات المتقدمة</li>
            </ul>

            <form action="{{ route('subscription.store') }}" method="POST">
              @csrf
              <input type="hidden" name="plan" value="الباقة الشهرية الشاملة">
              <input type="hidden" name="amount" value="99.00">
              <button type="submit" class="btn btn-success btn-lg btn-block rounded-pill font-weight-bold shadow-sm">
                <i class="fas fa-credit-card ml-2"></i> تفعيل أو تجديد الاشتراك الآن
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
@endsection
