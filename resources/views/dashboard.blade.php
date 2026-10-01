@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">لوحة تحكم المطعم</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">الرئيسية</a></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- البطاقات الإحصائية العلوية -->
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>2</h3>
            <p>الطلبات الجديدة</p>
          </div>
          <div class="icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <a href="{{ route('orders.index') }}" class="small-box-footer">عرض الطلبات <i class="fas fa-arrow-circle-left"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>$60.00</h3>
            <p>إجمالي الإيرادات</p>
          </div>
          <div class="icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <a href="#" class="small-box-footer">التفاصيل المالية <i class="fas fa-arrow-circle-left"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>10</h3>
            <p>عملاء جدد / VIP</p>
          </div>
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
          <a href="#" class="small-box-footer">إدارة العملاء <i class="fas fa-arrow-circle-left"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>2</h3>
            <p>قيد التحضير</p>
          </div>
          <div class="icon">
            <i class="fas fa-utensils"></i>
          </div>
          <a href="{{ route('orders.index') }}" class="small-box-footer">متابعة المطبخ <i class="fas fa-arrow-circle-left"></i></a>
        </div>
      </div>
    </div>

    <!-- بطاقة الترحيب -->
    <div class="card">
      <div class="card-body text-center py-4">
        <h3>مرحباً بك في لوحة تحكم مطعمك عبر سفريّة 🍔</h3>
        <p class="text-muted">من هنا يمكنك إدارة المنيو، الأقسام، متابعة الطلبات الحية، والتحكم بإعدادات مطعمك بكل سهولة ويسر.</p>

        <div class="mt-3">
          <a href="{{ route('categories.index') }}" class="btn btn-primary">
            <i class="fas fa-layer-group"></i> إدارة الأقسام
          </a>
          <a href="{{ route('menu-items.index') }}" class="btn btn-success ml-2">
            <i class="fas fa-hamburger"></i> إدارة الأطباق
          </a>
        </div>
      </div>
    </div>

  </div>
</section>
@endsection
