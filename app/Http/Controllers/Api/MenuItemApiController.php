<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemApiController extends Controller
{
    // GET /api/restaurants/{restaurantId}/menu-items
    public function index($restaurantId)
    {
        $items = MenuItem::where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    // POST /api/restaurants/{restaurantId}/menu-items
    public function store(Request $request, $restaurantId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:menu_categories,id',
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurantId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'is_available' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الوجبة بنجاح',
            'data' => $item
        ], 201);
    }

    // DELETE /api/menu-items/{id}
    public function destroy($id)
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الوجبة بنجاح'
        ]);
    }
}
