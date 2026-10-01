<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $restaurantUser = DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->first();

        $restaurant = null;
        $subscription = null;

        if ($restaurantUser) {
            $restaurant = DB::table('restaurants')->where('id', $restaurantUser->restaurant_id)->first();

            $subscription = DB::table('subscriptions')
                ->where('restaurant_id', $restaurant->id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return view('subscription.index', compact('restaurant', 'subscription'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $restaurantUser = DB::table('restaurant_users')->where('user_id', $userId)->first();

        if (!$restaurantUser) {
            return back()->with('error', 'لم يتم العثور على مطعم مرتبطة بحسابك.');
        }

        // إضافة اشتراك جديد متوافق مع أعمدة الجدول تماماً
        DB::table('subscriptions')->insert([
            'uuid' => (string) Str::uuid(),
            'restaurant_id' => $restaurantUser->restaurant_id,
            'plan' => $request->input('plan', 'الباقة الشهرية المميزة'),
            'status' => 'active',
            'billing_cycle_date' => now()->addDays(30),
            'amount' => $request->input('amount', 99.00),
            'created_at' => now(),
        ]);

        return redirect()->route('subscription.index')->with('success', 'تم تجديد الاشتراك بنجاح وتفعيل المنصة!');
    }
}
