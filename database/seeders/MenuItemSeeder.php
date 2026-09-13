<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['id' => 1],
            ['name' => 'مطعم التجربة', 'status' => 'active']
        );

        MenuItem::updateOrCreate(
    ['id' => 1],
    [
        'restaurant_id' => 1,
        'name' => 'برجر دجاج',
        'price' => 25,
        'is_available' => true
    ]
);

        MenuItem::create([
            'id' => 2,
            'restaurant_id' => $restaurant->id,
            'name' => 'وجبة إضافية',
            'price' => 15,
            'is_available' => true
        ]);

    }
}
