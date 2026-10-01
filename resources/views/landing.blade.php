<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سفريا - Sufriya | أدر مطعمك واستقبل طلباتك بذكاء</title>
    <!-- تضمين ستايلات Tailwind CSS للسرعة والتصميم العصري -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=fallback" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header / شريط التنقل -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-black text-orange-600">سفريا | Sufriya</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="#features" class="text-gray-600 hover:text-orange-600 font-medium hidden sm:block transition">المميزات</a>
                <a href="#how-it-works" class="text-gray-600 hover:text-orange-600 font-medium hidden sm:block transition">كيف يعمل النظام</a>
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-orange-600 font-bold transition">تسجيل الدخول</a>
                <a href="{{ route('register') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg shadow-orange-600/20">أنشئ مطعمك</a>
            </div>
        </div>
    </header>

    <!-- Hero Section / القسم الرئيسي -->
    <section class="relative overflow-hidden py-20 lg:py-32 bg-gradient-to-b from-orange-50/50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-block bg-orange-100 text-orange-700 text-sm font-bold px-4 py-1.5 rounded-full mb-6">المنصة الأذكية لإدارة المطاعم والطلب السريع 🚀</span>
            <h1 class="text-4xl sm:text-6xl font-black text-gray-900 leading-tight mb-6">
                أدر مطعمك، استقبل طلباتك، <br>ونمي مبيعاتك بكل <span class="text-orange-600">سهولة وسرعة</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                منصة سفريا تمنحك منيو رقمياً تفاعلياً، إدارة لحظية للطلبات، وتجربة سلسة لعملائك تضاعف أرباحك وتلغي الازدحام.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('register') }}" class="bg-orange-600 hover:bg-orange-700 text-white text-lg font-bold px-8 py-4 rounded-2xl transition shadow-xl shadow-orange-600/30">أنشئ مطعمك مجاناً</a>
                <a href="#features" class="bg-white hover:bg-gray-100 text-gray-800 border border-gray-200 text-lg font-bold px-8 py-4 rounded-2xl transition">اكتشف المميزات</a>
            </div>
        </div>
    </section>

    <!-- Features Section / مميزات النظام -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-black text-gray-900 mb-4">لماذا يختار أصحاب المطاعم منصة سفريا؟</h2>
                <p class="text-gray-600">صُمم النظام خصيصاً ليناسب احتياجات المطاعم العصرية ويوفر تجربة استخدام فائقة السرعة.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- ميزة 1 -->
                <div class="bg-gray-50 p-8 rounded-3xl border border-gray-100 hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl font-bold mb-6">⚡</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">سرعة فائقة وأداء عالي</h3>
                    <p class="text-gray-600">تصفح قوائم الطعام واستقبال الطلبات بأجزاء من الثانية لضمان راحة عملائك.</p>
                </div>
                <!-- ميزة 2 -->
                <div class="bg-gray-50 p-8 rounded-3xl border border-gray-100 hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl font-bold mb-6">📱</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">منيو رقمي متطور (QR)</h3>
                    <p class="text-gray-600">منيو أنيق ومحدث دائماً، يمكن لعملائك تصفحه والطلب منه مباشرة عبر الهاتف.</p>
                </div>
                <!-- ميزة 3 -->
                <div class="bg-gray-50 p-8 rounded-3xl border border-gray-100 hover:shadow-xl transition">
                    <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl font-bold mb-6">📊</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">لوحة تحكم ذكية</h3>
                    <p class="text-gray-600">إدارة الطلبات الحية، متابعة الحالات (قيد التجهيز، مكتمل)، وإحصائيات المبيعات بدقة.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section / كيف يعمل النظام -->
    <section id="how-it-works" class="py-20 bg-gray-50 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-black text-gray-900 mb-4">كيف تبدأ مع سفريا في 3 خطوات بسيطة؟</h2>
                <p class="text-gray-600">انضم إلينا اليوم وابدأ في استقبال طلبات زباينك بكل احترافية.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-3xl shadow-sm text-center">
                    <div class="w-12 h-12 bg-orange-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-6">1</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">سجل مطعمك مجاناً</h3>
                    <p class="text-gray-600">أنشئ حسابك الخاص خلال ثوانٍ معدودة وأدخل بيانات مطعمك الأساسية.</p>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm text-center">
                    <div class="w-12 h-12 bg-orange-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-6">2</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">أضف المنيو والأطباق</h3>
                    <p class="text-gray-600">قم بترتيب أقسام الأطعمة وإضافة أطباقك مع الأسعار والصور بكل سهولة.</p>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm text-center">
                    <div class="w-12 h-12 bg-orange-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-6">3</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">استقبل طلباتك</h3>
                    <p class="text-gray-600">شارك رابط المنيو أو الـ QR مع زباينك واستقبل الطلبات مباشرة على لوحة التحكم.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer / التذييل -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400">جميع الحقوق محفوظة © 2026 منصة سفريا (Sufriya).</p>
        </div>
    </footer>

</body>
</html>
