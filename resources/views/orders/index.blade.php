@extends('layouts.app')

@section('title', 'لوحة تحكم مطعم ')

@section('content')
    <div id="dashboard-wrapper" class="min-h-screen py-6 px-4 sm:px-6 lg:px-8 transition-colors duration-300 bg-gray-950 text-gray-100 flex flex-col justify-between" dir="rtl">

        <div>
            <!-- Top Header Bar -->
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 mb-8 p-6 rounded-2xl shadow-xl transition-colors duration-300 bg-gray-900 border border-gray-800" id="header-bar">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-2xl shadow-inner">
                        🍔
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-wide" id="header-title">إدارة طلبات مطعم </h1>
                        <p class="text-xs text-gray-400 mt-0.5" id="header-subtitle">تحديث تلقائي لحظي للطلبات</p>
                    </div>
                </div>

                <div class="flex items-center flex-wrap gap-3">
                    <!-- زر تبديل الثيم -->
                    <button onclick="toggleTheme()" type="button" class="bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center gap-2 border border-gray-700" id="theme-toggle-btn">
                        <span id="theme-icon">☀️</span>
                        <span id="theme-text">الوضع الفاتح</span>
                    </button>

                    <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 px-3 py-1.5 rounded-xl" id="status-badge-wrapper">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span class="text-xs font-bold text-emerald-400">النظام متصل</span>
                    </div>
                </div>
            </div>

            <!-- قسم الإحصائيات والتحليلات -->
            @php
                $totalOrdersCount = $orders->count();
                $totalRevenue = $orders->where('status', 'completed')->sum('total_amount');
                $uniqueCustomers = $orders->pluck('customer_id')->unique()->count();
                $repeatCustomersCount = $orders->groupBy('customer_id')->filter(fn($group) => $group->count() > 1)->count();
                $repeatCustomerPercentage = $uniqueCustomers > 0 ? round(($repeatCustomersCount / $uniqueCustomers) * 100) : 0;
            @endphp

            <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-xl flex items-center justify-between theme-card">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">إجمالي الطلبات المكتملة</p>
                        <h3 class="text-xl font-black text-white mt-1">{{ $orders->where('status', 'completed')->count() }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-lg">📦</div>
                </div>
                <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-xl flex items-center justify-between theme-card">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">إجمالي الإيرادات</p>
                        <h3 class="text-xl font-black text-emerald-400 mt-1">{{ number_format($totalRevenue, 2) }} ₪</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-lg">💰</div>
                </div>
                <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-xl flex items-center justify-between theme-card">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">إجمالي العملاء</p>
                        <h3 class="text-xl font-black text-indigo-400 mt-1">{{ $uniqueCustomers }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-lg">👥</div>
                </div>
                <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-xl flex items-center justify-between theme-card">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">نسبة العملاء المتكررين</p>
                        <h3 class="text-xl font-black text-amber-400 mt-1">{{ $repeatCustomerPercentage }}%</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-lg">📈</div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            @php
                $totalOrders = $orders->count();
                $pendingOrders = $orders->where('status', 'pending_acceptance')->count();
                $acceptedOrders = $orders->where('status', 'accepted')->count();
                $preparingOrders = $orders->where('status', 'preparing')->count();
                $readyOrders = $orders->where('status', 'ready')->count();
                $completedOrders = $orders->where('status', 'completed')->count();
            @endphp

            <div class="max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="stat-card p-4 rounded-xl flex flex-col justify-between transition-colors duration-300 bg-gray-900 border border-gray-800 shadow-sm theme-card">
                    <span class="text-xs font-medium text-gray-400">إجمالي الطلبات</span>
                    <span class="text-2xl font-black mt-1 stat-value">{{ $totalOrders }}</span>
                </div>
                <div class="stat-card p-4 rounded-xl flex flex-col justify-between transition-colors duration-300 bg-gray-900 border border-gray-800 shadow-sm theme-card">
                    <span class="text-xs font-medium text-rose-400">قيد الانتظار</span>
                    <span class="text-2xl font-black mt-1 stat-value text-rose-500">{{ $pendingOrders }}</span>
                </div>
                <div class="stat-card p-4 rounded-xl flex flex-col justify-between transition-colors duration-300 bg-gray-900 border border-gray-800 shadow-sm theme-card">
                    <span class="text-xs font-medium text-blue-400">تم القبول</span>
                    <span class="text-2xl font-black mt-1 stat-value text-blue-500">{{ $acceptedOrders }}</span>
                </div>
                <div class="stat-card p-4 rounded-xl flex flex-col justify-between transition-colors duration-300 bg-gray-900 border border-gray-800 shadow-sm theme-card">
                    <span class="text-xs font-medium text-amber-400">قيد التجهيز</span>
                    <span class="text-2xl font-black mt-1 stat-value text-amber-500">{{ $preparingOrders }}</span>
                </div>
                <div class="stat-card p-4 rounded-xl flex flex-col justify-between transition-colors duration-300 bg-gray-900 border border-gray-800 shadow-sm theme-card">
                    <span class="text-xs font-medium text-indigo-400">جاهز للاستلام</span>
                    <span class="text-2xl font-black mt-1 stat-value text-indigo-400">{{ $readyOrders }}</span>
                </div>
                <div class="stat-card p-4 rounded-xl flex flex-col justify-between transition-colors duration-300 bg-gray-900 border border-gray-800 shadow-sm theme-card">
                    <span class="text-xs font-medium text-emerald-400">مكتمل</span>
                    <span class="text-2xl font-black mt-1 stat-value text-emerald-500">{{ $completedOrders }}</span>
                </div>
            </div>

            <!-- Kanban Board Columns -->
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">

                <!-- 1. قيد الانتظار -->
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex flex-col h-[600px] theme-kanban-col">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-800">
                        <h2 class="font-bold text-sm text-rose-400 flex items-center gap-2">
                            <span>⏳</span> قيد الانتظار
                        </h2>
                        <span class="bg-rose-500/20 text-rose-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                            {{ $orders->where('status', 'pending_acceptance')->count() }}
                        </span>
                    </div>
                    <div class="overflow-y-auto space-y-3 pr-1 flex-1">
                        @forelse($orders->where('status', 'pending_acceptance') as $order)
                            @include('orders.partials.card', ['order' => $order])
                        @empty
                            <p class="text-xs text-gray-500 text-center py-8">لا توجد طلبات</p>
                        @endforelse
                    </div>
                </div>

                <!-- 2. تم القبول -->
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex flex-col h-[600px] theme-kanban-col">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-800">
                        <h2 class="font-bold text-sm text-blue-400 flex items-center gap-2">
                            <span>✅</span> تم القبول
                        </h2>
                        <span class="bg-blue-500/20 text-blue-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                            {{ $orders->where('status', 'accepted')->count() }}
                        </span>
                    </div>
                    <div class="overflow-y-auto space-y-3 pr-1 flex-1">
                        @forelse($orders->where('status', 'accepted') as $order)
                            @include('orders.partials.card', ['order' => $order])
                        @empty
                            <p class="text-xs text-gray-500 text-center py-8">لا توجد طلبات</p>
                        @endforelse
                    </div>
                </div>

                <!-- 3. قيد التجهيز -->
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex flex-col h-[600px] theme-kanban-col">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-800">
                        <h2 class="font-bold text-sm text-amber-400 flex items-center gap-2">
                            <span>🔥</span> قيد التجهيز
                        </h2>
                        <span class="bg-amber-500/20 text-amber-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                            {{ $orders->where('status', 'preparing')->count() }}
                        </span>
                    </div>
                    <div class="overflow-y-auto space-y-3 pr-1 flex-1">
                        @forelse($orders->where('status', 'preparing') as $order)
                            @include('orders.partials.card', ['order' => $order])
                        @empty
                            <p class="text-xs text-gray-500 text-center py-8">لا توجد طلبات</p>
                        @endforelse
                    </div>
                </div>

                <!-- 4. جاهز للاستلام -->
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex flex-col h-[600px] theme-kanban-col">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-800">
                        <h2 class="font-bold text-sm text-indigo-400 flex items-center gap-2">
                            <span>🛍️</span> جاهز للاستلام
                        </h2>
                        <span class="bg-indigo-500/20 text-indigo-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                            {{ $orders->where('status', 'ready')->count() }}
                        </span>
                    </div>
                    <div class="overflow-y-auto space-y-3 pr-1 flex-1">
                        @forelse($orders->where('status', 'ready') as $order)
                            @include('orders.partials.card', ['order' => $order])
                        @empty
                            <p class="text-xs text-gray-500 text-center py-8">لا توجد طلبات</p>
                        @endforelse
                    </div>
                </div>

                <!-- 5. مكتمل -->
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 flex flex-col h-[600px] theme-kanban-col">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-800">
                        <h2 class="font-bold text-sm text-emerald-400 flex items-center gap-2">
                            <span>🎉</span> مكتمل
                        </h2>
                        <span class="bg-emerald-500/20 text-emerald-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                            {{ $orders->where('status', 'completed')->count() }}
                        </span>
                    </div>
                    <div class="overflow-y-auto space-y-3 pr-1 flex-1">
                        @forelse($orders->where('status', 'completed') as $order)
                            @include('orders.partials.card', ['order' => $order])
                        @empty
                            <p class="text-xs text-gray-500 text-center py-8">لا توجد طلبات</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- كود JavaScript الخاص بالوضع الداكن والفاتح -->
    <script>
        function toggleTheme() {
            const wrapper = document.getElementById('dashboard-wrapper');
            const headerBar = document.getElementById('header-bar');
            const headerTitle = document.getElementById('header-title');
            const headerSubtitle = document.getElementById('header-subtitle');
            const themeText = document.getElementById('theme-text');
            const themeIcon = document.getElementById('theme-icon');
            const themeToggleBtn = document.getElementById('theme-toggle-btn');
            const themeCards = document.querySelectorAll('.theme-card');
            const themeKanbanCols = document.querySelectorAll('.theme-kanban-col');

            if (wrapper.classList.contains('bg-gray-950')) {
                // التحويل للوضع الفاتح
                wrapper.classList.remove('bg-gray-950', 'text-gray-100');
                wrapper.classList.add('bg-gray-100', 'text-gray-900');

                if(headerBar) {
                    headerBar.classList.remove('bg-gray-900', 'border-gray-800');
                    headerBar.classList.add('bg-white', 'border-gray-200', 'shadow-md');
                }
                if(headerTitle) headerTitle.classList.remove('text-white');
                if(headerTitle) headerTitle.classList.add('text-gray-900');
                if(headerSubtitle) {
                    headerSubtitle.classList.remove('text-gray-400');
                    headerSubtitle.classList.add('text-gray-500');
                }

                if(themeToggleBtn) {
                    themeToggleBtn.classList.remove('bg-gray-800', 'hover:bg-gray-700', 'text-gray-200', 'border-gray-700');
                    themeToggleBtn.classList.add('bg-white', 'hover:bg-gray-100', 'text-gray-800', 'border-gray-300', 'shadow');
                }

                themeCards.forEach(card => {
                    card.classList.remove('bg-gray-900', 'border-gray-800');
                    card.classList.add('bg-white', 'border-gray-200', 'shadow-sm');
                });

                themeKanbanCols.forEach(col => {
                    col.classList.remove('bg-gray-900/60', 'border-gray-800');
                    col.classList.add('bg-white', 'border-gray-200', 'shadow-sm');
                });

                themeText.innerText = 'الوضع الداكن';
                themeIcon.innerText = '🌙';
                localStorage.setItem('theme', 'light');
            } else {
                // التحويل للوضع الداكن
                wrapper.classList.remove('bg-gray-100', 'text-gray-900');
                wrapper.classList.add('bg-gray-950', 'text-gray-100');

                if(headerBar) {
                    headerBar.classList.remove('bg-white', 'border-gray-200', 'shadow-md');
                    headerBar.classList.add('bg-gray-900', 'border-gray-800');
                }
                if(headerTitle) headerTitle.classList.remove('text-gray-900');
                if(headerTitle) headerTitle.classList.add('text-white');
                if(headerSubtitle) {
                    headerSubtitle.classList.remove('text-gray-500');
                    headerSubtitle.classList.add('text-gray-400');
                }

                if(themeToggleBtn) {
                    themeToggleBtn.classList.remove('bg-white', 'hover:bg-gray-100', 'text-gray-800', 'border-gray-300', 'shadow');
                    themeToggleBtn.classList.add('bg-gray-800', 'hover:bg-gray-700', 'text-gray-200', 'border-gray-700');
                }

                themeCards.forEach(card => {
                    card.classList.remove('bg-white', 'border-gray-200', 'shadow-sm');
                    card.classList.add('bg-gray-900', 'border-gray-800');
                });

                themeKanbanCols.forEach(col => {
                    col.classList.remove('bg-white', 'border-gray-200', 'shadow-sm');
                    col.classList.add('bg-gray-900/60', 'border-gray-800');
                });

                themeText.innerText = 'الوضع الفاتح';
                themeIcon.innerText = '☀️';
                localStorage.setItem('theme', 'dark');
            }
        }

        // استرجاع الثيم المحفوظ عند فتح الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('theme') === 'light') {
                toggleTheme();
            }
        });
    </script>
@endsection
