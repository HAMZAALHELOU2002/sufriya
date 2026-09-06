<!DOCTYPE html>
<html lang="ar" dir="rtl" x-data="{ lang: 'ar' }" :dir="lang === 'en' ? 'ltr' : 'rtl'"
    :lang="lang">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة تحكم مطعم حمزة')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js للتفاعل -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <!-- Navbar الثابت لكل الصفحات المستقبلية -->
    <nav class="bg-slate-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="text-2xl">🍔</span>
                <h1 class="text-xl font-bold"
                    x-text="lang === 'ar' ? 'لوحة تحكم مطعم حمزة' : 'Hamza Restaurant Dashboard'"></h1>
            </div>

            <div class="flex items-center space-x-4 space-x-reverse">
                <!-- زر تبديل اللغات -->
                <button @click="lang = lang === 'ar' ? 'en' : 'ar'"
                    class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg border border-slate-700 transition">
                    <span x-show="lang === 'ar'">English 🇬🇧</span>
                    <span x-show="lang === 'en'">عربي 🇸🇦</span>
                </button>

                <!-- مؤشر الاتصال الحي -->
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <span class="w-2 h-2 ml-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span x-text="lang === 'ar' ? 'متصل مباشر (Live)' : 'Live Connected'"></span>
                </span>
            </div>
        </div>
    </nav>

    <!-- محتوى الصفحات المتغيرة (هون رح ينحشر محتوى كل صفحة تلقائياً) -->
    <main>
        @yield('content')
    </main>

    <!-- فوتر ثابت -->
    <footer class="text-center py-6 text-gray-400 text-xs">
        جميع الحقوق محفوظة © 2026 | نظام بوت الواتساب الذكي 🚀
    </footer>

</body>

</html>
