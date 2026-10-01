<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // استثناء صفحة الاشتراكات وتسجيل الخروج لكي لا يحدث تكرار إعادة توجيه
            if ($request->is('subscription*') || $request->is('logout')) {
                return $next($request);
            }

            $userId = Auth::id();

            $restaurantUser = DB::table('restaurant_users')->where('user_id', $userId)->first();

            if ($restaurantUser) {
                $subscription = DB::table('subscriptions')
                    ->where('restaurant_id', $restaurantUser->restaurant_id)
                    ->where('status', 'active')
                    ->where('billing_cycle_date', '>', now())
                    ->first();

                if (!$subscription) {
                    return redirect()->route('subscription.index')
                        ->with('error', 'انتهت صلاحية اشتراك مطعمك. يرجى التجديد للمتابعة واستقبال الطلبات.');
                }
            }
        }

        return $next($request);
    }
}
