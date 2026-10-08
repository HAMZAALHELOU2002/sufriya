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
     * جلب المطعم الخاص بالمستخدم الحالي.
     * وإنشاء مطعم افتراضي إذا لم يكن مرتبطاً بمطعم.
     */
    private function getCurrentRestaurant()
    {
        $userId = Auth::id();

        $restaurantId = DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->value('restaurant_id');

        if (!$restaurantId) {
            $user = Auth::user();

            $restaurantId = DB::transaction(function () use ($user, $userId) {
                $restaurantId = DB::table('restaurants')->insertGetId([
                    'name' => $user->name . ' مطعم',
                    'location' => '',
                    'status' => 'active',
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

                return $restaurantId;
            });
        }

        return Restaurant::find($restaurantId);
    }

    // ================= إعدادات المطعم ================= //

    public function edit()
    {
        $restaurant = $this->getCurrentRestaurant();

        return view('restaurant.settings', compact('restaurant'));
    }

    public function update(Request $request)
    {
        $restaurant = $this->getCurrentRestaurant();

        if (!$restaurant) {
            return back()->with('error', 'لم يتم العثور على المطعم.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:500',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'whatsapp_business_account_id' => 'nullable|string|max:255',
            'assumed_commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:trial,active,closed,past_due,suspended',
            'close_message' => 'nullable|string|max:500',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'location' => $request->location,
            'whatsapp_phone_number_id' => $request->whatsapp_phone_number_id,
            'whatsapp_business_account_id' => $request->whatsapp_business_account_id,
            'assumed_commission_rate' => $request->assumed_commission_rate,
            'status' => $request->status,
            'close_message' => $request->close_message,
        ];

        // معالجة رفع الشعار
        if ($request->hasFile('logo_path')) {
            if (
                $restaurant->logo_path &&
                Storage::disk('public')->exists($restaurant->logo_path)
            ) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }

            $data['logo_path'] = $request
                ->file('logo_path')
                ->store('logos', 'public');
        }

        $restaurant->update($data);

        return redirect()
            ->route('restaurant.settings')
            ->with(
                'success',
                'تم تحديث إعدادات وحالة المطعم بنجاح!'
            );
    }

    // ================= إدارة فريق العمل والصلاحيات ================= //

    public function staffIndex()
    {
        $restaurant = $this->getCurrentRestaurant();

        if (!$restaurant) {
            return back()->with('error', 'لم يتم العثور على المطعم.');
        }

        $staffMembers = DB::table('restaurant_users')
            ->join(
                'users',
                'restaurant_users.user_id',
                '=',
                'users.id'
            )
            ->where(
                'restaurant_users.restaurant_id',
                $restaurant->id
            )
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'restaurant_users.role'
            )
            ->get();

        return view(
            'restaurant.staff',
            compact('staffMembers', 'restaurant')
        );
    }

    public function updateRole(Request $request, $userId)
    {
        $restaurant = $this->getCurrentRestaurant();

        if (!$restaurant) {
            return back()->with('error', 'لم يتم العثور على المطعم.');
        }

        $request->validate([
            'role' => 'required|in:owner,manager,staff',
        ]);

        $staff = DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (!$staff) {
            return back()->with(
                'error',
                'المستخدم غير موجود ضمن فريق هذا المطعم.'
            );
        }

        // منع تغيير صلاحية المالك الحالي من هنا
        if (
            $staff->role === 'owner' &&
            $userId != Auth::id()
        ) {
            return back()->with(
                'error',
                'لا يمكن تغيير صلاحية مالك المطعم من هنا.'
            );
        }

        DB::table('restaurant_users')
            ->where('user_id', $userId)
            ->where('restaurant_id', $restaurant->id)
            ->update([
                'role' => $request->role,
            ]);

        return back()->with(
            'success',
            'تم تحديث صلاحية المستخدم بنجاح.'
        );
    }
}
