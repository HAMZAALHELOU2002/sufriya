<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    /**
     * جلب المطعم الخاص بالمستخدم الحالي، وإنشاء مطعم افتراضي تلقائياً إذا لم يكن موجوداً.
     */
    private function getCurrentRestaurant()
    {
        $userId = Auth::id();

        $restaurantUser = DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->first();

        if (!$restaurantUser) {
            $user = Auth::user();

            $restaurantId = DB::table('restaurants')->insertGetId([
                'name' => $user->name . ' مطعم',
                'location' => '',
                'status' => 'active',
                'slug' => \Illuminate\Support\Str::slug($user->name . '-' . \Illuminate\Support\Str::random(5)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('restaurant_users')->insert([
                'user_id' => $userId,
                'restaurant_id' => $restaurantId,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $restaurantUser = DB::table('restaurant_users')
                ->where('user_id', $userId)
                ->first();
        }

        return Restaurant::find($restaurantUser->restaurant_id);
    }

    // ================= قسم إعدادات المطعم ================= //

    public function edit()
    {
        $restaurant = $this->getCurrentRestaurant();
        return view('restaurant.settings', compact('restaurant'));
    }

    public function update(Request $request)
    {
        $restaurant = $this->getCurrentRestaurant();

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:500',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'whatsapp_business_account_id' => 'nullable|string|max:255',
            'assumed_commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,closed',                 // [مضاف حديثاً] التحقق من حالة المطعم
            'close_message' => 'nullable|string|max:500',            // [مضاف حديثاً] التحقق من رسالة الإغلاق
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'location' => $request->location,
            'whatsapp_phone_number_id' => $request->whatsapp_phone_number_id,
            'whatsapp_business_account_id' => $request->whatsapp_business_account_id,
            'assumed_commission_rate' => $request->assumed_commission_rate,
            'status' => $request->status,                            // [مضاف حديثاً] حفظ الحالة
            'close_message' => $request->close_message,              // [مضاف حديثاً] حفظ رسالة الإغلاق
            'updated_at' => now(),
        ];

        // معالجة رفع الشعار
        if ($request->hasFile('logo_path')) {
            if (!empty($restaurant->logo_path)) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }

            $path = $request->file('logo_path')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        $restaurant->update($data);

        return redirect()->route('restaurant.settings')->with('success', 'تم تحديث إعدادات وحالة المطعم بنجاح!');
    }

    // ================= قسم إدارة فريق العمل والصلاحيات ================= //

    public function staffIndex()
    {
        $restaurant = $this->getCurrentRestaurant();

        $staffMembers = DB::table('restaurant_users')
            ->join('users', 'restaurant_users.user_id', '=', 'users.id')
            ->where('restaurant_users.restaurant_id', $restaurant->id)
            ->select('users.id as user_id', 'users.name', 'users.email', 'restaurant_users.role')
            ->get();

        return view('restaurant.staff', compact('staffMembers', 'restaurant'));
    }

    public function updateRole(Request $request, $userId)
    {
        $restaurant = $this->getCurrentRestaurant();

        $request->validate([
            'role' => 'required|string|in:owner,manager,staff'
        ]);

        DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->where('restaurant_id', $restaurant->id)
            ->update(['role' => $request->role]);

        return back()->with('success', 'تم تحديث صلاحية المستخدم بنجاح.');
    }
}
