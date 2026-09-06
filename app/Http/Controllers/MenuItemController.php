<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::where('status', 'active')->first();

        if (!$restaurant) {
            return redirect()->back()->with('error', 'لا يوجد مطعم نشط حالياً');
        }

        $menuItems = MenuItem::where('restaurant_id', $restaurant->id)->get();

        return view('menu.index', compact('menuItems', 'restaurant'));
    }

   public function create()
    {
        $restaurant = Restaurant::where('status', 'active')->first();

        if (!$restaurant) {
            return redirect()->back()->with('error', 'لا يوجد مطعم نشط حالياً');
        }

        return view('menu.create', compact('restaurant'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'restaurant_id' => 'required|exists:restaurants,id',
            'description' => 'nullable|string',
        ]);

        MenuItem::create($request->all());

        return redirect()->route('menu-items.index')->with('success', 'تم إضافة الوجبة بنجاح');
    }

    public function edit(MenuItem $menuItem)
    {
        return view('menu.edit', compact('menuItem'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $menuItem->update($request->all());

        return redirect()->route('menu-items.index')->with('success', 'تم تحديث الوجبة بنجاح');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return redirect()->route('menu-items.index')->with('success', 'تم حذف الوجبة بنجاح');
    }
}
