<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::create([
            'name' => 'Wafa Admin',
            'email' => 'admin@wafa.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. إنشاء مطعم تجريبي
        $restaurant = Restaurant::create([
            'name' => 'مطعم حمزة للوجبات السريعة',
            'status' => 'active',
            'assumed_commission_rate' => 15.00,
        ]);

        // 3. ربط المستخدم بالمطعم كـ owner
        $user->restaurants()->attach($restaurant->id, ['role' => 'owner']);

        // ضبط الـ Tenant الحقيقي للسيشن لتعبئة البيانات التابعة للمطعم
        app()->instance('current_restaurant_id', $restaurant->id);

        // 4. إنشاء تصنيفات المنيو
        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => ' مسخن فلسطيني',
            'sort_order' => 1,
            'is_active' => true,
        ]);
     

        // 5. إنشاء وجبة في المنيو
        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'شكشوكة',
            'description' => 'وجبة لذيذة من البيض والخضار',
            'price' => 10,
            'is_available' => true,
        ]);

        // 6. إنشاء عميل واتساب تجريبي
        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'wa_phone_number' => '96890000000',
            'name' => ' اشرف كخة وعلي لوكة',
        ]);
        //اكثر من خطا ظهر بسبب عدم قبول القيمة payment and payment_status
       // 7. إنشاء طلب تجريبي
        $order = Order::create([
    'restaurant_id'    => $restaurant->id,
    'customer_id'      => $customer->id,
    'fulfillment_type' => 'delivery',
    'status'           => 'pending_acceptance', // القيمة الصحيحة
    'payment_method'   => 'cash',               // تم التعديل من cod إلى cash
    'payment_status'   => 'pending_cash',        // القيمة الصحيحة
    'total_amount'     => 10.0,
]);

        // 8. إضافة تفاصيل الطلب
        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $item->id,
            'item_name' => $item->name,
            'unit_price' => $item->price,
            'quantity' => 1,
            'subtotal' => 10,
        ]);
    }
}
