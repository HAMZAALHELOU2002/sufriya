@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h2><i class="fas fa-crown text-warning ml-2"></i> إدارة عملاء الـ VIP والمميزين</h2>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم العميل</th>
                            <th>رقم الهاتف</th>
                            <th>عدد الطلبات</th>
                            <th>إجمالي المبيعات المنفقة</th>
                            <th>التصنيف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vipCustomers as $index => $customer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="font-weight-bold">{{ $customer->customer_name }}</td>
                            <td>{{ $customer->customer_phone }}</td>
                            <td><span class="badge badge-info px-2 py-1">{{ $customer->total_orders }} طلبات</span></td>
                            <td class="text-success font-weight-bold">${{ number_format($customer->total_spent, 2) }}</td>
                            <td><span class="badge badge-warning text-dark"><i class="fas fa-star ml-1"></i> VIP</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-muted py-4">لا توجد بيانات عملاء كافية حالياً</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection
