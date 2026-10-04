<?php

use App\model\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            [
                'name' => 'Bike Ride',
                'icon_name' => 'bike-ride.png',
                'display_order' => 1,
                'category_type' => 1,
            ],[
                'name' => 'Taxi Ride',
                'icon_name' => 'taxi-ride.png',
                'display_order' => 2,
                'category_type' => 1,
            ],[
                'name' => 'Coupon Deals',
                'icon_name' => 'coupon-deals.png',
                'display_order' => 3,
                'category_type' => 1,
            ],[
                'name' => 'Courier Service',
                'icon_name' => 'courier-service.png',
                'display_order' => 4,
                'category_type' => 5,
            ],[
                'name' => 'Food Delivery',
                'icon_name' => 'food-delivery.png',
                'display_order' => 5,
                'category_type' => 2,
            ],[
                'name' => 'Grocery Delivery',
                'icon_name' => 'grocery-delivery.png',
                'display_order' => 6,
                'category_type' => 2,
            ],[
                'name' => 'Liquor Delivery',
                'icon_name' => 'liquor-delivery.png',
                'display_order' => 7,
                'category_type' => 2,
            ],[
                'name' => 'Flower Delivery',
                'icon_name' => 'flower-delivery.png',
                'display_order' => 8,
                'category_type' => 2,
            ],[
                'name' => 'Water Delivery',
                'icon_name' => 'water-delivery.png',
                'display_order' => 9,
                'category_type' => 2,
            ],[
                'name' => 'Medicine Delivery',
                'icon_name' => 'medicine-delivery.png',
                'display_order' => 10,
                'category_type' => 2,
            ],[
                'name' => 'Dog Walking',
                'icon_name' => 'dog-walking.png',
                'display_order' => 11,
                'category_type' => 3,
            ],[
                'name' => 'Baby Care',
                'icon_name' => 'baby-care.png',
                'display_order' => 12,
                'category_type' => 3,
            ],[
                'name' => 'Pet Care',
                'icon_name' => 'pet-care.png',
                'display_order' => 13,
                'category_type' => 3,
            ],[
                'name' => 'Workout Trainer',
                'icon_name' => 'workout-trainer.png',
                'display_order' => 14,
                'category_type' => 3,
            ],[
                'name' => 'Security Service',
                'icon_name' => 'security-service.png',
                'display_order' => 15,
                'category_type' => 3,
            ],[
                'name' => 'Tutors',
                'icon_name' => 'tutors.png',
                'display_order' => 16,
                'category_type' => 3,
            ],[
                'name' => 'Beauty Services',
                'icon_name' => 'beauty-services.png',
                'display_order' => 17,
                'category_type' => 3,
            ],[
                'name' => 'Massages Services',
                'icon_name' => 'massages-service.png',
                'display_order' => 18,
                'category_type' => 3,
            ],[
                'name' => 'Home Cleaning',
                'icon_name' => 'home-cleaning.png',
                'display_order' => 19,
                'category_type' => 4,
            ],[
                'name' => 'Gardening',
                'icon_name' => 'gardening.png',
                'display_order' => 20,
                'category_type' => 4,
            ],[
                'name' => 'Snow Blowers',
                'icon_name' => 'snow-blowers.png',
                'display_order' => 21,
                'category_type' => 4,
            ],[
                'name' => 'Laundry Service',
                'icon_name' => 'laundry-service.png',
                'display_order' => 22,
                'category_type' => 4,
            ],[
                'name' => 'Maid Service',
                'icon_name' => 'maid-service.png',
                'display_order' => 23,
                'category_type' => 4,
            ],[
                'name' => 'Pest Control',
                'icon_name' => 'pest-control.png',
                'display_order' => 24,
                'category_type' => 4,
            ],[
                'name' => 'AC Repair',
                'icon_name' => 'ac-repair.png',
                'display_order' => 25,
                'category_type' => 4,
            ],[
                'name' => 'Electricians',
                'icon_name' => 'electricians.png',
                'display_order' => 26,
                'category_type' => 4,
            ],[
                'name' => 'Car Wash',
                'icon_name' => 'car-wash.png',
                'display_order' => 27,
                'category_type' => 4,
            ],[
                'name' => 'Car Repair',
                'icon_name' => 'car-repair.png',
                'display_order' => 28,
                'category_type' => 4,
            ],[
                'name' => 'Tow Truck',
                'icon_name' => 'tow-truck.png',
                'display_order' => 29,
                'category_type' => 4,
            ],[
                'name' => 'Plum bers',
                'icon_name' => 'plumbers.png',
                'display_order' => 30,
                'category_type' => 4,
            ]

        ];

        foreach ($array as $item) {
            $category = new ServiceCategory();
            $category->name = strtoupper($item['name']);
            $category->slug = $category->slug($item['name']);
            $category->icon_name = $item['icon_name'];
            $category->display_order = $item['display_order'];
            $category->category_type = $item['category_type'];
            $category->save();
        }

        echo "Service Category added successfully!";
    }
}
