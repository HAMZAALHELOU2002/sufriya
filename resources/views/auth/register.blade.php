<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انضم إلى سفريا - إنشاء حساب جديد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-0 m-0">

    <div class="w-full min-h-screen flex flex-col lg:flex-row">

        <!-- الجانب الأيمن: نموذج التسجيل الأبيض والنظيف -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12 bg-white overflow-y-auto">
            <div class="w-full max-w-md space-y-5">

                <!-- رأس النموذج (الشعار النصي الاحترافي بدل أيقونة البرجر) -->
                <div class="text-right">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black text-xl shadow-md shadow-orange-500/30">س</div>
                        <span class="text-2xl font-black text-gray-900 tracking-tight">سَفـريـا <span class="text-orange-500 text-sm font-semibold">| لوحة المطاعم</span></span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mt-3">انضم إلى منصة سفريا</h2>
                    <p class="text-xs text-gray-500 mt-1">سجل مطعمك الجديد وابدأ باستقبال طلبات زبائنك عبر واتساب بكل احترافية.</p>
                </div>

                <!-- رسائل الأخطاء -->
                @if ($errors->any())
                    <div class="bg-red-50 border-r-4 border-red-500 p-3 text-xs text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- النموذج -->
                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">اسم صاحب المطعم</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition text-sm"
                            placeholder="محمد أحمد">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">اسم المطعم</label>
                        <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition text-sm"
                            placeholder="مثال: مطعم الشاورما الملكية">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">رقم هاتف واتساب المطعم</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition text-sm"
                            placeholder="059xxxxxxx">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition text-sm"
                            placeholder="name@example.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">كلمة المرور</label>
                        <input type="password" name="password" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition text-sm"
                            placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition text-sm"
                            placeholder="••••••••">
                    </div>

                    <button type="submit"
                        class="w-full py-3 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/30 transition duration-200 text-sm mt-2">
                        إنشاء الحساب والبدء
                    </button>
                </form>

                <!-- الانتقال لتسجيل الدخول -->
                <div class="text-center pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-600">
                        لديك حساب بالفعل؟
                        <a href="{{ route('login') }}" class="text-orange-600 font-bold hover:underline">تسجيل الدخول</a>
                    </p>
                </div>

            </div>
        </div>

        <!-- الجانب الأيسر: صورة الطعام والعبارة الفخمة -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900 items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1200&auto=format&fit=crop');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

            <div class="relative z-10 text-center p-12 text-white">
                <h1 class="text-5xl font-black mb-4 tracking-wide text-white drop-shadow-lg">أطلب.. ونوصلك</h1>
                <p class="text-xl font-medium text-gray-200">سجل مطعمك الرقمي وابدأ بإدارة طلباتك بكل سهولة</p>
            </div>
        </div>

    </div>

</body>
</html>
