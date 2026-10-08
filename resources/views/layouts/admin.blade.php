<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>سفوريا | لوحة التحكم</title>

  <!-- Favicon (أيقونة علامة التبويب) -->
<link rel="icon" type="image/svg+xml" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/utensils.svg">  <!-- أو إذا كانت بصيغة .ico -->
   {{-- <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"> --}}

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Cairo:300,400,600,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Theme style AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>

    body {
      font-family: 'Cairo', sans-serif;
      direction: rtl;
      text-align: right;
      background-color: #f4f6f9;
    }
    /* تعديلات القائمة الجانبية للغة العربية */
    .main-sidebar {
      right: 0 !important;
      left: auto !important;
      box-shadow: 0 14px 28px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.22);
    }
    .content-wrapper, .main-header, .main-footer {
      margin-right: 250px !important;
      margin-left: 0 !important;
      transition: margin-right 0.3s ease-in-out;
    }
    @media (max-width: 991.98px) {
      .content-wrapper, .main-header, .main-footer {
        margin-right: 0 !important;
      }
    }
    .brand-link {
      font-size: 1.25rem;
      font-weight: 700;
      background: #1f2d3d;
      border-bottom: 1px solid #4f5962;
    }
    .nav-sidebar .nav-link {
      border-radius: 6px;
      margin-bottom: 4px;
      transition: all 0.2s ease;
    }
    .nav-sidebar .nav-link:hover {
      background-color: rgba(255,255,255,0.1);
      transform: translateX(-4px);
    }
    .nav-sidebar .nav-link.active {
      background-color: #007bff !important;
      color: #fff !important;
      box-shadow: 0 4px 6px rgba(0,123,255,0.3);
    }
    .nav-sidebar .nav-link p {
      margin-right: 8px;
    }
  </style>
  @yield('styles')
</head>
@stack('scripts')
</body>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars fa-lg text-secondary"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('dashboard') }}" class="nav-link font-weight-bold text-dark"><i class="fas fa-home ml-1 text-primary"></i> الرئيسية</a>
      </li>
    </ul>

    <!-- عرض الوقت والتاريخ اللحظي المتحرك واليسار -->
    <ul class="navbar-nav mr-auto align-items-center">
      <li class="nav-item ml-3">
        <span class="badge badge-light border px-3 py-2 shadow-sm font-weight-bold text-secondary" id="live-clock">
          <i class="far fa-clock text-primary ml-1"></i> جاري التحميل...
        </span>
      </li>

      <!-- ========================================== -->
      <!-- زر وقائمة الإشعارات المربوطة بلحظيات Pusher -->
      <!-- ========================================== -->
      <li class="nav-item dropdown ml-3">
        <a class="nav-link position-relative" data-toggle="dropdown" href="#">
          <i class="far fa-bell fa-lg text-dark"></i>
          @php
              $unreadCount = auth()->user() ? auth()->user()->unreadNotifications->count() : 0;
          @endphp
          <span id="notification-badge" class="badge badge-danger navbar-badge" style="right: 5px; top: 5px; {{ $unreadCount == 0 ? 'display: none;' : '' }}">{{ $unreadCount }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-left shadow-lg border-0">
          <span class="dropdown-item dropdown-header font-weight-bold bg-light text-dark text-center" id="notification-header-count">
            {{ $unreadCount }} إشعارات جديدة
          </span>
          <div class="dropdown-divider"></div>

          <div id="notifications-list-container">
            @if(auth()->user())
              @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                <a href="{{ isset($notification->data['order_id']) ? route('orders.invoice', $notification->data['order_id']) : '#' }}" class="dropdown-item py-2">
                  <div class="media align-items-center">
                    <i class="fas fa-shopping-cart text-primary fa-lg ml-3"></i>
                    <div class="media-body">
                      <p class="text-sm font-weight-bold text-dark mb-0">{{ $notification->data['message'] ?? 'طلب جديد وارد' }}</p>
                      <p class="text-muted text-xs mb-0"><i class="far fa-clock ml-1"></i> {{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
              @empty
                <span id="no-notifications-msg" class="dropdown-item text-center text-muted py-3">
                  <i class="fas fa-bell-slash fa-2x mb-2 text-secondary"></i>
                  <p class="mb-0 text-sm">لا توجد إشعارات جديدة</p>
                </span>
              @endforelse
            @endif
          </div>

          <div class="dropdown-divider"></div>
          <a href="{{ route('orders.index') }}" class="dropdown-item dropdown-footer text-center font-weight-bold text-primary">
            <i class="fas fa-list ml-1"></i> عرض كافة الطلبات الحية
          </a>
        </div>
      </li>
      <!-- ========================================== -->

      <!-- Right navbar links (حساب المطعم مع اللوغو والاسم في الهيدر) -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center py-1" data-toggle="dropdown" href="#">
          @php
              $headerRestaurant = null;
              if(auth()->user()) {
                  $headerRestaurant = auth()->user()->restaurant
                      ?? DB::table('restaurant_users')
                          ->join('restaurants', 'restaurant_users.restaurant_id', '=', 'restaurants.id')
                          ->where('restaurant_users.user_id', auth()->id())
                          ->select('restaurants.*')
                          ->first();
              }
          @endphp

          @if($headerRestaurant && !empty($headerRestaurant->logo_path))
              <img src="{{ asset('storage/' . $headerRestaurant->logo_path) }}" alt="Logo" class="rounded-circle ml-2 shadow-sm border" style="width: 32px; height: 32px; object-fit: cover;">
          @else
              <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center ml-2 shadow-sm" style="width: 32px; height: 32px; font-size: 14px;">
                  <i class="fas fa-utensils"></i>
              </div>
          @endif

          <span class="font-weight-bold text-dark mr-1" style="font-size: 0.9rem;">
              {{ $headerRestaurant->name ?? 'المطعم' }}
          </span>
        </a>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-left shadow-lg border-0">
          <span class="dropdown-item dropdown-header font-weight-bold bg-light">
            {{ $headerRestaurant->name ?? 'إعدادات الحساب' }}
          </span>
          <div class="dropdown-divider"></div>
          <a href="{{ route('restaurant.settings') }}" class="dropdown-item py-2">
            <i class="fas fa-cogs ml-2 text-secondary"></i> إعدادات المطعم
          </a>
          <div class="dropdown-divider"></div>
          <form action="{{ url('/logout') }}" method="POST">
            @csrf
            <button type="submit" class="dropdown-item text-danger font-weight-bold py-2">
              <i class="fas fa-sign-out-alt ml-2"></i> تسجيل الخروج
            </button>
          </form>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link text-center py-3">
      <span class="brand-text font-weight-bold text-warning"><i class="fas fa-utensils ml-1"></i> سُفريّة Sufriya</span>
    </a>

    <div class="sidebar">
      <nav class="mt-3">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-line text-info"></i>
              <p>الرئيسية والإحصائيات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-bell text-warning"></i>
              <p>الطلبات الحية <span class="badge badge-danger right">جديد</span></p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-layer-group text-primary"></i>
              <p>أقسام المنيو</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('menu-items.index') }}" class="nav-link {{ request()->routeIs('menu-items.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-hamburger text-success"></i>
              <p>الأطباق والوجبات</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.customers.vip') }}" class="nav-link {{ request()->routeIs('admin.customers.vip') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-shield" style="color: #b084cc;"></i>
              <p>العملاء الـ VIP</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-pie text-danger"></i>
              <p>التحليلات والتقارير</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('subscription.index') }}" class="nav-link {{ request()->routeIs('subscription.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-crown text-warning"></i>
              <p>إدارة الاشتراك</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('restaurant.settings') }}" class="nav-link {{ request()->routeIs('restaurant.settings*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sliders-h text-secondary"></i>
              <p>إعدادات المطعم</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper pt-3">
    @yield('content')
  </div>

  <footer class="main-footer text-center text-muted small py-3">
    <strong>حقوق الطبع محفوظة &copy; 2026 <span class="text-primary font-weight-bold">سُفريّة SaaS</span>.</strong> جميع الحقوق محفوظة.
  </footer>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- سكربت الوقت والتاريخ اللحظي -->
<script>
  function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours ? hours : 12;
    const strTime = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    const clockElement = document.getElementById('live-clock');
    if (clockElement) {
      clockElement.innerHTML = `<i class="far fa-clock text-primary ml-1"></i> ${strTime} | ${year}-${month}-${day}`;
    }
  }
  setInterval(updateClock, 1000);
  updateClock();
</script>

@stack('scripts')
</body>
</html>
