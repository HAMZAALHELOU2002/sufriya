@extends('layouts.app')

@section('title', 'لوحة تحكم مطعم حمزة')

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
                        <h1 class="text-2xl font-black tracking-wide" id="header-title">إدارة طلبات مطعم حمزة</h1>
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

            <!-- نموذج إضافة طلب جديد يدوياً -->
            <div class="max-w-7xl mx-auto bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-8 shadow-xl theme-card" id="form-container">
                <h3 class="font-bold text-sm mb-3 flex items-center gap-2" id="form-title">
                    <span>📝</span>
                    <span>إضافة طلب جديد يدوياً</span>
                </h3>

                <form action="{{ route('orders.testStore') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    @csrf

                    <!-- اسم العميل -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">اسم العميل</label>
                        <input type="text" name="customer_name" required placeholder="مثال: محمد أحمد"
                            class="bg-gray-950 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 w-full focus:outline-none focus:border-amber-500 theme-input">
                    </div>

                    <!-- رقم الهاتف -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">رقم الهاتف</label>
                        <input type="text" name="customer_phone" required placeholder="0599000000" dir="ltr"
                            class="bg-gray-950 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 w-full focus:outline-none focus:border-amber-500 text-right theme-input">
                    </div>

                    <!-- اسم الوجبة -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">اسم الوجبة</label>
                        <input type="text" name="item_name" required placeholder="مثال: وجبة برجر دجاج"
                            class="bg-gray-950 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 w-full focus:outline-none focus:border-amber-500 theme-input">
                    </div>

                    <!-- السعر -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">السعر (₪)</label>
                        <input type="number" step="0.01" name="price" required placeholder="25"
                            class="bg-gray-950 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 w-full focus:outline-none focus:border-amber-500 theme-input">
                    </div>

                    <!-- زر الإرسال -->
                    <div>
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 active:scale-95 text-gray-950 font-black text-xs py-2.5 px-4 rounded-xl transition-all shadow flex items-center justify-center gap-1.5 h-[38px]">
                            <span>➕</span>
                            <span>إضافة الطلب</span>
                        </button>
                    </div>
                </form>
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
            const formContainer = document.getElementById('form-container');
            const formTitle = document.getElementById('form-title');
            const themeCards = document.querySelectorAll('.theme-card');
            const themeInputs = document.querySelectorAll('.theme-input');
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

                if(formContainer) {
                    formContainer.classList.remove('bg-gray-900', 'border-gray-800');
                    formContainer.classList.add('bg-white', 'border-gray-200', 'shadow-md');
                }
                if(formTitle) {
                    formTitle.classList.remove('text-white');
                    formTitle.classList.add('text-gray-900');
                }

                themeCards.forEach(card => {
                    card.classList.remove('bg-gray-900', 'border-gray-800');
                    card.classList.add('bg-white', 'border-gray-200', 'shadow-sm');
                });

                themeInputs.forEach(input => {
                    input.classList.remove('bg-gray-950', 'border-gray-700', 'text-white');
                    input.classList.add('bg-gray-50', 'border-gray-300', 'text-gray-900');
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

                if(formContainer) {
                    formContainer.classList.remove('bg-white', 'border-gray-200', 'shadow-md');
                    formContainer.classList.add('bg-gray-900', 'border-gray-800');
                }
                if(formTitle) {
                    formTitle.classList.remove('text-gray-900');
                    formTitle.classList.add('text-white');
                }

                themeCards.forEach(card => {
                    card.classList.remove('bg-white', 'border-gray-200', 'shadow-sm');
                    card.classList.add('bg-gray-900', 'border-gray-800');
                });

                themeInputs.forEach(input => {
                    input.classList.remove('bg-gray-50', 'border-gray-300', 'text-gray-900');
                    input.classList.add('bg-gray-950', 'border-gray-700', 'text-white');
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
