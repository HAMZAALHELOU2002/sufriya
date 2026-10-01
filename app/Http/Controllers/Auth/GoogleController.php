<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    // التوجيه إلى صفحة تسجيل الدخول في جوجل
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // استلام البيانات من جوجل بعد الموافقة
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // البحث عما إذا كان المستخدم مسجلاً مسبقاً بنفس البريد
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // إذا لم يكن موجوداً، نقوم بإنشاء حساب جديد له
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // كلمة مرور عشوائية وآمنة
                    'restaurant_name' => $googleUser->getName() . ' Restaurant', // اسم افتراضي مؤقت
                    'slug' => Str::slug($googleUser->getName() . '-' . Str::random(5)),
                ]);
            }

            // تسجيل دخول المستخدم للمنصة
            Auth::login($user);

            // توجيهه إلى لوحة التحكم الخاصة بالمطعم
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'حدث خطأ أثناء تسجيل الدخول عبر جوجل. يجيب المحاولة مرة أخرى.');
        }
    }
}
