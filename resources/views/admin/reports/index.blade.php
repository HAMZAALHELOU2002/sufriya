@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h2><i class="fas fa-chart-pie text-primary ml-2"></i> التحليلات والتقارير المالية</h2>
        </div>
    </div>

    <!-- بطاقات الملخص -->
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>${{ number_format($totalSales, 2) }}</h3>
                    <p>إجمالي العائدات المالية</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ $totalOrdersCount }}</h3>
                    <p>إجمالي الطلبات المسجلة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول الوجبات الأكثر طلباً -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white font-weight-bold">
            <i class="fas fa-utensils text-success ml-1"></i> الوجبات والأطباق الأكثر طلباً
        </div>
        <div class="card-body">
            <table class="table table-bordered text-center">
                <thead class="thead-light">
                    <tr>
                        <th>اسم الوجبة</th>
                        <th>عدد مرات الطلب</th>
                        <th>إجمالي الإيرادات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topItems as $item)
                    <tr>
                        <td class="font-weight-bold">{{ $item->item_name }}</td>
                        <td><span class="badge badge-primary">{{ $item->count }}</span></td>
                        <td class="text-success font-weight-bold">${{ number_format($item->revenue, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
