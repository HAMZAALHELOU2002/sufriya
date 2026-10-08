@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark animate-fade-in">
          <i class="fas fa-concierge-bell text-warning ml-2"></i> إدارة الطلبات الحية
        </h1>
      </div>
      <div class="col-sm-6 text-left">
        <a href="{{ route('invoices.index') }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold pulse-btn">
          <i class="fas fa-file-invoice-dollar ml-1"></i> عرض كافة الفواتير
        </a>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- البطاقات الإحصائية العلوية الديناميكية -->
    <div class="row">
      <!-- إجمالي الطلبات -->
      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info shadow-sm rounded-lg overflow-hidden position-relative stat-card">
          <div class="inner p-3">
            <h3 class="font-weight-bold counter-num">{{ $analytics['total_orders'] ?? 0 }}</h3>
            <p class="mb-0 font-weight-semibold">إجمالي الطلبات</p>
          </div>
          <div class="icon floating-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="small-box-footer bg-dark text-white py-1 text-center small">
            حالة الطلبات النشطة
          </div>
        </div>
      </div>

      <!-- إجمالي المبيعات والأموال -->
      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success shadow-sm rounded-lg overflow-hidden position-relative stat-card money-card">
          <div class="inner p-3">
            <h3 class="font-weight-bold counter-num">${{ number_format($analytics['total_revenue'] ?? 0, 2) }}</h3>
            <p class="mb-0 font-weight-semibold">إجمالي المبيعات والأموال</p>
          </div>
          <div class="icon floating-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="small-box-footer bg-dark text-white py-1 text-center small">
            الإيرادات المسجلة
          </div>
        </div>
      </div>

      <!-- إجمالي العملاء -->
      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning shadow-sm rounded-lg overflow-hidden position-relative stat-card">
          <div class="inner p-3 text-dark">
            <h3 class="font-weight-bold counter-num">{{ $analytics['total_customers'] ?? 0 }}</h3>
            <p class="mb-0 font-weight-semibold">إجمالي العملاء</p>
          </div>
          <div class="icon floating-icon text-dark" style="opacity: 0.15;">
            <i class="fas fa-users"></i>
          </div>
          <div class="small-box-footer bg-dark text-white py-1 text-center small">
            العملاء الحاليين
          </div>
        </div>
      </div>

      <!-- نسبة العملاء المتكررين -->
      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger shadow-sm rounded-lg overflow-hidden position-relative stat-card">
          <div class="inner p-3">
            <h3 class="font-weight-bold counter-num">{{ $analytics['repeat_customer_percentage'] ?? 0 }}%</h3>
            <p class="mb-0 font-weight-semibold">نسبة العملاء المتكررين</p>
          </div>
          <div class="icon floating-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="small-box-footer bg-dark text-white py-1 text-center small">
            مؤشر الولاء
          </div>
        </div>
      </div>
    </div>

    <!-- جدول قائمة الطلبات الواردة الديناميكي -->
    <div class="card card-outline card-primary shadow-sm border-0 rounded-lg mt-3">
      <div class="card-header bg-white py-3">
        <h3 class="card-title font-weight-bold text-dark m-0">
          <i class="fas fa-list-alt text-primary ml-1"></i> قائمة الطلبات الواردة
        </h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle text-right mb-0">
            <thead class="thead-light text-secondary font-weight-bold">
              <tr>
                <th class="py-3 text-center" style="width: 80px;">رقم الطلب</th>
                <th class="py-3">العميل</th>
                <th class="py-3">رقم الهاتف</th>
                <th class="py-3">تفاصيل الطبق / الدفع</th>
                <th class="py-3">المبلغ الإجمالي</th>
                <th class="py-3 text-center">الحالة</th>
                <th class="py-3 text-center">تاريخ الطلب</th>
                <th class="py-3 text-center" style="width: 250px;">إجراءات وتحديث الحالة</th>
              </tr>
            </thead>
            <tbody>
              @forelse($orders as $order)
              <tr class="table-row-hover">
                <td class="text-center font-weight-bold text-primary align-middle">{{ $order->id }}</td>
                <td class="align-middle font-weight-bold text-dark">
                  <i class="fas fa-user-circle text-muted ml-1"></i> {{ $order->customer->name ?? 'عميل غير مسجل' }}
                </td>
                <td class="align-middle text-muted" dir="ltr" style="text-align: right;">{{ $order->customer->wa_phone_number ?? '-' }}</td>
                <td class="align-middle">
                  <span class="badge badge-light border px-2 py-1 text-secondary">{{ $order->payment_reference ?? 'وجبة مأکولات' }}</span>
                </td>
                <td class="align-middle font-weight-bold text-success">${{ number_format($order->total_price, 2) }}</td>
                <td class="text-center align-middle">
                  @php
                      $statusClass = match($order->status) {
                          'pending_acceptance' => 'badge-warning',
                          'accepted' => 'badge-info',
                          'preparing' => 'badge-primary',
                          'ready' => 'badge-secondary',
                          'completed' => 'badge-success',
                          'cancelled', 'expired' => 'badge-danger',
                          default => 'badge-light'
                      };
                      $statusText = match($order->status) {
                          'pending_acceptance' => 'قيد الانتظار',
                          'accepted' => 'تم القبول',
                          'preparing' => 'قيد التحضير',
                          'ready' => 'جاهز',
                          'completed' => 'مكتمل',
                          'cancelled' => 'ملغي',
                          'expired' => 'منتهي الصلاحية',
                          default => $order->status
                      };
                  @endphp
                  <span class="badge {{ $statusClass }} px-3 py-2 font-weight-bold shadow-sm status-badge">{{ $statusText }}</span>
                </td>
                <td class="text-center align-middle text-muted small" dir="ltr">
                  <i class="far fa-clock ml-1"></i> {{ $order->created_at->format('Y-m-d H:i') }}
                </td>
                <td class="text-center align-middle">
                  <div class="d-flex align-items-center justify-content-center flex-nowrap" style="gap: 4px;">
                    <!-- فورم تحديث الحالة -->
                    <form action="{{ route('orders.update', $order->id) }}" method="POST" class="d-inline-flex align-items-center mb-0">
                      @csrf
                      <select name="status" class="form-control form-control-sm font-weight-bold text-dark ml-1" style="width: 105px;" onchange="this.form.submit()">
                        <option value="pending_acceptance" {{ $order->status == 'pending_acceptance' ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="accepted" {{ $order->status == 'accepted' ? 'selected' : '' }}>تم القبول</option>
                        <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>قيد التحضير</option>
                        <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>جاهز</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                      </select>

                      <button type="submit" class="btn btn-success btn-sm px-2 shadow-sm action-btn" title="حفظ الحالة">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>

                    <!-- زر الفاتورة -->
                    <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-info btn-sm px-2 shadow-sm text-white action-btn" title="عرض الفاتورة">
                      <i class="fas fa-file-invoice"></i>
                    </a>

                    <!-- فورم الحذف -->
                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm px-2 shadow-sm action-btn" title="حذف الطلب">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  <i class="fas fa-box-open fa-2x mb-2"></i>
                  <p class="mb-0">لا توجد طلبات واردة حالياً.</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>
@endsection


@push('styles')
<style>
  .table td, .table th {
    vertical-align: middle !important;
  }
  .stat-card {
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    cursor: pointer;
  }
  .stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2) !important;
  }
  .money-card:hover {
    filter: brightness(1.05);
  }
  .floating-icon {
    position: absolute;
    left: 15px;
    top: 15px;
    font-size: 45px;
    opacity: 0.2;
    transition: transform 0.4s ease;
  }
  .stat-card:hover .floating-icon {
    transform: scale(1.2) rotate(10deg);
    opacity: 0.35;
  }
  .table-row-hover {
    transition: background-color 0.2s ease, transform 0.2s ease;
  }
  .table-row-hover:hover {
    background-color: rgba(0, 123, 255, 0.03) !important;
  }
  .action-btn {
    transition: transform 0.2s ease;
  }
  .action-btn:hover {
    transform: scale(1.15);
  }
</style>
@endpush
@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.Pusher = Pusher;

    const restaurantId = "{{ $restaurantId ?? '' }}";

    console.log('Restaurant ID:', restaurantId);

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ config('broadcasting.connections.pusher.key') }}",
        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        forceTLS: true
    });

    console.log('Echo initialized');

    const orderSound = new Audio(
        'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3'
    );

    if (restaurantId) {

        const channelName = 'restaurant.' + restaurantId;

        console.log(
            'Listening on channel:',
            channelName
        );

        window.Echo
            .channel(channelName)
            .listen('.new.order', function (event) {

                console.log(
                    '🔥 NEW ORDER EVENT RECEIVED:',
                    event
                );

                console.log(
                    'Order:',
                    event.order
                );

                // تشغيل الصوت
                orderSound.play().catch(function (error) {
                    console.log(
                        'Audio blocked by browser:',
                        error
                    );
                });

                // التنبيه
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '🔔 طلب جديد #' + event.order.id,
                    text:
                        'الإجمالي: ' +
                        event.order.total_price,
                    showConfirmButton: false,
                    timer: 6000,
                    timerProgressBar: true
                });

                // تحديث عداد الإشعارات
                const badge =
                    document.getElementById(
                        'notification-badge'
                    );

                if (badge) {
                    let count =
                        parseInt(badge.textContent) || 0;

                    count++;

                    badge.textContent = count;
                    badge.style.display =
                        'inline-block';
                }

                // تحديث عدد الإشعارات
                const headerCount =
                    document.getElementById(
                        'notification-header-count'
                    );

                if (headerCount) {
                    let count =
                        parseInt(
                            headerCount.textContent
                        ) || 0;

                    headerCount.textContent =
                        (count + 1) +
                        ' إشعارات جديدة';
                }

                // إزالة رسالة لا توجد إشعارات
                const noNotifMsg =
                    document.getElementById(
                        'no-notifications-msg'
                    );

                if (noNotifMsg) {
                    noNotifMsg.remove();
                }

                // إضافة الإشعار للقائمة
                const listContainer =
                    document.getElementById(
                        'notifications-list-container'
                    );

                if (listContainer) {

                    const wrapper =
                        document.createElement('div');

                    wrapper.innerHTML = `
                        <a href="{{ route('orders.index') }}"
                           class="dropdown-item py-2 bg-light">

                            <div class="media align-items-center">

                                <i class="fas fa-shopping-cart text-primary fa-lg ml-3"></i>

                                <div class="media-body">

                                    <p class="text-sm font-weight-bold text-dark mb-0">
                                        طلب جديد وارد رقم #${event.order.id}
                                    </p>

                                    <p class="text-muted text-xs mb-0">
                                        <i class="far fa-clock ml-1"></i>
                                        الآن
                                    </p>

                                </div>

                            </div>

                        </a>

                        <div class="dropdown-divider"></div>
                    `;

                    listContainer.prepend(wrapper);
                }

                // تحديث الصفحة حتى يظهر الطلب في الجدول
                setTimeout(function () {
                    location.reload();
                }, 1500);
            });

    } else {

        console.error(
            '❌ Restaurant ID is missing.'
        );
    }
</script>
@endpush
