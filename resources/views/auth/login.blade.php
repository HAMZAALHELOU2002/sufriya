<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - سفريا</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-0 m-0">

    <div class="w-full min-h-screen flex flex-col lg:flex-row">

        <!-- الجانب الأيمن: نموذج تسجيل الدخول الأبيض والنظيف -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12 bg-white">
            <div class="w-full max-w-md space-y-6">

                <!-- رأس النموذج (الشعار النصي الاحترافي) -->
                <div class="text-right">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-black text-xl shadow-md shadow-orange-500/30">س</div>
                        <span class="text-2xl font-black text-gray-900 tracking-tight">سَفـريـا <span class="text-orange-500 text-sm font-semibold">| لوحة المطاعم</span></span>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mt-4">مرحباً بعودتك!</h2>
                    <p class="text-sm text-gray-500 mt-1">سجل دخولك إلى حسابك في سفريا وأبدأ إدارة مطعمك بكل سهولة.</p>
                </div>

                <!-- رسائل الخطأ -->
                @if ($errors->any())
                    <div class="bg-red-50 border-r-4 border-red-500 p-4 text-sm text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- نموذج الدخول -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition"
                            placeholder="name@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">كلمة المرور</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-gray-800 bg-gray-50/50 transition"
                            placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                            <span class="mr-2 text-gray-600 font-medium">تذكرني</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-orange-600 font-semibold hover:underline">نسيت كلمة المرور؟</a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/30 transition duration-200 mt-2">
                        تسجيل الدخول
                    </button>
                </form>

                <!-- رابط إنشاء حساب جديد -->
                <div class="text-center pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600">
                        ليس لديك حساب؟
                        <a href="{{ route('register') }}" class="text-orange-600 font-bold hover:underline">إنشاء حساب جديد</a>
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
                <p class="text-xl font-medium text-gray-200">سجل دخولك وأبدأ إدارة مطعمك بكل سهولة مع سفريا</p>
            </div>
        </div>

    </div>

</body>
</html>
