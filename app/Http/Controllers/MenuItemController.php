<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MenuItemController extends Controller
{
    /**
     * جلب معرف المطعم المرتبط بالحساب الحالي
     */
    private function getCurrentRestaurantId(): ?int
    {
        return DB::table('restaurant_users')
            ->where('user_id', Auth::id())
            ->value('restaurant_id');
    }

    /**
     * عرض قائمة الوجبات
     */
    public function index()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return view('menu.index', [
                'menuItems' => collect(),
                'categories' => collect(),
            ]);
        }

        // جلب الأطباق مع أسماء الأقسام
        $menuItems = DB::table('menu_items')
            ->leftJoin(
                'menu_categories',
                'menu_items.category_id',
                '=',
                'menu_categories.id'
            )
            ->where('menu_items.restaurant_id', $restaurantId)
            ->select(
                'menu_items.*',
                'menu_categories.name as category_name'
            )
            ->get();

        // جلب أقسام المطعم فقط
        $categories = MenuCategory::where(
            'restaurant_id',
            $restaurantId
        )->get();

        return view(
            'menu.index',
            compact('menuItems', 'categories')
        );
    }

    /**
     * صفحة إنشاء وجبة جديدة
     */
    public function create()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        $categories = $restaurantId
            ? MenuCategory::where('restaurant_id', $restaurantId)->get()
            : collect();

        return view('menu.create', compact('categories'));
    }

    /**
     * حفظ وجبة جديدة
     */
    public function store(Request $request)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return back()->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'required',
                Rule::exists('menu_categories', 'id')
                    ->where('restaurant_id', $restaurantId),
            ],
            'price' => 'required|numeric|min:0',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = [
            'restaurant_id' => $restaurantId,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description,
            'is_available' => true,
        ];

        // رفع الصورة
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request
                ->file('image_path')
                ->store('menu-items', 'public');
        }

        MenuItem::create($data);

        return redirect()
            ->route('menu-items.index')
            ->with('success', 'تم إضافة الوجبة بنجاح');
    }

    /**
     * إضافة قسم جديد
     */
    public function storeCategory(Request $request)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return back()->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MenuCategory::create([
            'restaurant_id' => $restaurantId,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with(
            'success',
            'تم إضافة القسم بنجاح، يمكنك الآن اختياره للأطباق.'
        );
    }

    /**
     * صفحة تعديل الوجبة
     */
    public function edit(MenuItem $menuItem)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId || $menuItem->restaurant_id != $restaurantId) {
            return redirect()
                ->route('menu-items.index')
                ->with('error', 'غير مصرح لك بتعديل هذه الوجبة.');
        }

        $categories = MenuCategory::where(
            'restaurant_id',
            $restaurantId
        )->get();

        return view(
            'menu.edit',
            compact('menuItem', 'categories')
        );
    }

    /**
     * تحديث بيانات الوجبة
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId || $menuItem->restaurant_id != $restaurantId) {
            return redirect()
                ->route('menu-items.index')
                ->with('error', 'غير مصرح لك بتعديل هذه الوجبة.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => [
                'required',
                Rule::exists('menu_categories', 'id')
                    ->where('restaurant_id', $restaurantId),
            ],
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'is_available' => $request->has('is_available'),
        ];

        // رفع صورة جديدة وحذف القديمة
        if ($request->hasFile('image_path')) {
            if (
                $menuItem->image_path &&
                Storage::disk('public')->exists($menuItem->image_path)
            ) {
                Storage::disk('public')->delete($menuItem->image_path);
            }

            $data['image_path'] = $request
                ->file('image_path')
                ->store('menu-items', 'public');
        }

        $menuItem->update($data);

        return redirect()
            ->route('menu-items.index')
            ->with('success', 'تم تحديث الوجبة بنجاح');
    }

    /**
     * حذف وجبة
     */
    public function destroy(MenuItem $menuItem)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId || $menuItem->restaurant_id != $restaurantId) {
            return redirect()
                ->route('menu-items.index')
                ->with('error', 'غير مصرح لك بحذف هذه الوجبة.');
        }

        // حذف الصورة
        if (
            $menuItem->image_path &&
            Storage::disk('public')->exists($menuItem->image_path)
        ) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return redirect()
            ->route('menu-items.index')
            ->with('success', 'تم حذف الوجبة بنجاح');
    }
}
