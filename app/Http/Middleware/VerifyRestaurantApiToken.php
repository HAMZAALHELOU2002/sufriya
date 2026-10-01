<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyRestaurantApiToken
{
    public function handle(Request $request, Closure $next)
    {
        // جلب الـ Token إما من الـ Headers أو من الـ Request
        $token = $request->header('X-API-Token') ?? $request->input('api_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'مفتاح المصادقة مفقود (API Token is required).'
            ], 401);
        }

        // استخراج معرف المطعم من الرابط (Route Parameters)
        $restaurantId = $request->route('restaurantId');

        if (!$restaurantId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف المطعم غير موجود في الرابط.'
            ], 400);
        }

        // التحقق من صحة المفتاح الخاص بهذا المطعم
        $restaurant = DB::table('restaurants')
            ->where('id', $restaurantId)
            ->where('api_token', $token)
            ->first();

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'مفتاح المصادقة غير صحيح لهذا المطعم.'
            ], 403);
        }

        return $next($request);
    }
}
