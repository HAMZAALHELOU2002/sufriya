<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuCategoryController extends Controller
{
    private function getCurrentRestaurantId()
    {
        $restaurantUser = DB::table('restaurant_users')
            ->where('user_id', Auth::id())
            ->first();

        return $restaurantUser ? $restaurantUser->restaurant_id : DB::table('restaurants')->value('id');
    }

    public function index()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->back()->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        // جلب الأقسام مع الترتيب الصحيح
        $categories = DB::table('menu_categories')
            ->where('restaurant_id', $restaurantId)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->back()->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        DB::table('menu_categories')->insert([
            'restaurant_id' => $restaurantId,
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => 1, // افتراضياً مفعل لكي يظهر مباشرة
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // التعديل هنا: التوجيه لصفحة الـ index بدلاً من back()
        return redirect()->route('categories.index')->with('success', 'تم إضافة القسم بنجاح!');
    }

    public function edit($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $category = DB::table('menu_categories')
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'القسم غير موجود أو ليس لديك صلاحية.');
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $category = DB::table('menu_categories')
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$category) {
            return redirect()->route('categories.index')->with('error', 'القسم غير موجود أو ليس لديك صلاحية لتعديله.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        DB::table('menu_categories')->where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'updated_at' => now(),
        ]);

        // التعديل هنا: التوجيه لصفحة الـ index بدلاً من back() ليعرض رسالة النجاح ويعود للجدول
        return redirect()->route('categories.index')->with('success', 'تم تحديث البيانات بنجاح!');
    }

    public function destroy($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $category = DB::table('menu_categories')
            ->where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$category) {
            return redirect()->back()->with('error', 'القسم غير موجود أو ليس لديك صلاحية لحذفه.');
        }

        DB::table('menu_categories')->where('id', $id)->delete();

        return redirect()->route('categories.index')->with('success', 'تم حذف القسم بنجاح!');
    }
}
