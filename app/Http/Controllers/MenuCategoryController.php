<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuCategoryController extends Controller
{
    private function getCurrentRestaurantId(): ?int
    {
        return DB::table('restaurant_users')
            ->where('user_id', Auth::id())
            ->value('restaurant_id');
    }

    public function index()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->back()
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $categories = MenuCategory::where('restaurant_id', $restaurantId)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->back()
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        return view('categories.create');
    }

    public function store(Request $request)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->back()
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        MenuCategory::create([
            'restaurant_id' => $restaurantId,
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم إضافة القسم بنجاح!');
    }

    public function edit($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->route('categories.index')
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $category = MenuCategory::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'القسم غير موجود أو ليس لديك صلاحية.');
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->route('categories.index')
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $category = MenuCategory::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with(
                    'error',
                    'القسم غير موجود أو ليس لديك صلاحية لتعديله.'
                );
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم تحديث البيانات بنجاح!');
    }

    public function destroy($id)
    {
        $restaurantId = $this->getCurrentRestaurantId();

        if (!$restaurantId) {
            return redirect()->route('categories.index')
                ->with('error', 'لا يوجد مطعم مرتبط بحسابك.');
        }

        $category = MenuCategory::where('id', $id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with(
                    'error',
                    'القسم غير موجود أو ليس لديك صلاحية لحذفه.'
                );
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم حذف القسم بنجاح!');
    }
}
