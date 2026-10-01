<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckRestaurantRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  الأدوار المسموح لها بالدخول مفصولة بـ فاصلة
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $userId = Auth::id();

        // جلب علاقة المستخدم بالمطعم الحالي
        $restaurantUser = DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->first();

        if (!$restaurantUser) {
            abort(403, 'عذراً، ليس لديك صلاحية للوصول إلى هذا المطعم.');
        }

        // التحقق مما إذا كان دور المستخدم ضمن الأدوار المسموح لها
        if (!empty($roles) && !in_array($restaurantUser->role, $roles)) {
            abort(403, 'ليس لديك الصلاحية الكافية لتنفيذ هذا الإجراء أو دخول هذه الصفحة.');
        }

        return $next($request);
    }
}
