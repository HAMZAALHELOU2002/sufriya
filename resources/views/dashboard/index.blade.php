@extends('layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-tachometer-alt text-primary ml-2"></i> لوحة تحكم المطعم
                    </h1>
                </div>
                <div class="col-sm-6 text-left">
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- بطاقات الإحصائيات التفاعلية -->
            <div class="row">
                <!-- الطلبات الجديدة -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-info shadow-sm rounded-lg overflow-hidden position-relative"
                        style="transition: transform 0.3s;">
                        <div class="inner p-4">
                            <h3 class="font-weight-bold">{{ $newOrdersCount ?? 2 }}</h3>
                            <p class="mb-0 font-weight-semibold">الطلبات الجديدة</p>
                        </div>
                        <div class="icon"
                            style="position: absolute; left: 15px; top: 20px; font-size: 50px; opacity: 0.2;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <a href="{{ route('orders.index') }}"
                            class="small-box-footer bg-dark text-white py-2 text-center d-block text-decoration-none">
                            عرض الطلبات <i class="fas fa-arrow-circle-left mr-1"></i>
                        </a>
                    </div>
                </div>

                <!-- إجمالي الإيرادات -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-success shadow-sm rounded-lg overflow-hidden position-relative">
                        <div class="inner p-4">
                            <h3 class="font-weight-bold">${{ $totalRevenue ?? '60.00' }}</h3>
                            <p class="mb-0 font-weight-semibold">إجمالي الإيرادات</p>
                        </div>
                        <div class="icon"
                            style="position: absolute; left: 15px; top: 20px; font-size: 50px; opacity: 0.2;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#"
                            class="small-box-footer bg-dark text-white py-2 text-center d-block text-decoration-none">
                            التفاصيل المالية <i class="fas fa-arrow-circle-left mr-1"></i>
                        </a>
                    </div>
                </div>

                <!-- عملاء جدد / VIP -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-warning shadow-sm rounded-lg overflow-hidden position-relative">
                        <div class="inner p-4 text-dark">
                            <h3 class="font-weight-bold">{{ $vipCount ?? 10 }}</h3>
                            <p class="mb-0 font-weight-semibold">عملاء جدد / VIP</p>
                        </div>
                        <div class="icon text-dark"
                            style="position: absolute; left: 15px; top: 20px; font-size: 50px; opacity: 0.15;">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="#"
                            class="small-box-footer bg-dark text-white py-2 text-center d-block text-decoration-none">
                            إدارة العملاء <i class="fas fa-arrow-circle-left mr-1"></i>
                        </a>
                    </div>
                </div>

                <!-- قيد التحضير -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-danger shadow-sm rounded-lg overflow-hidden position-relative">
                        <div class="inner p-4">
                            <h3 class="font-weight-bold">{{ $preparingCount ?? 2 }}</h3>
                            <p class="mb-0 font-weight-semibold">قيد التحضير</p>
                        </div>
                        <div class="icon"
                            style="position: absolute; left: 15px; top: 20px; font-size: 50px; opacity: 0.2;">
                            <i class="fas fa-fire-alt"></i>
                        </div>
                        <a href="{{ route('orders.index') }}"
                            class="small-box-footer bg-dark text-white py-2 text-center d-block text-decoration-none">
                            متابعة المطبخ <i class="fas fa-arrow-circle-left mr-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- قسم ترحيبي تفاعلي إضافي -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-primary card-outline shadow-sm border-0 rounded-lg">
                        <div class="card-body p-4 text-center">
                            <h4 class="font-weight-bold text-dark mb-2">مرحباً بك في لوحة تحكم مطعمك عبر سُفريّة 🍔</h4>
                            <p class="text-muted">من هنا يمكنك إدارة المنيو، الأقسام، متابعة الطلبات الحية، والتحكم بإعدادات
                                مطعمك بكل سهولة ويسر.</p>
                            <hr class="w-25 my-3">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('categories.index') }}"
                                    class="btn btn-outline-primary mx-1 font-weight-bold">
                                    <i class="fas fa-layer-group ml-1"></i> إدارة الأقسام
                                </a>
                                <a href="{{ route('menu-items.index') }}"
                                    class="btn btn-outline-success mx-1 font-weight-bold">
                                    <i class="fas fa-hamburger ml-1"></i> إدارة الأطباق
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('styles')
    <style>
        .small-box {
            transition: all 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endpush
