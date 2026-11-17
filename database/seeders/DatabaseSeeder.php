<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/**
 * Seeder = fills the database with starter data so every team member (and the
 * lecturer) gets the SAME demo data after running:  php artisan migrate:fresh --seed
 * It uses firstOrCreate / updateOrCreate so running it twice does not duplicate rows.
 * NOTE: prices and vendor names below are SAMPLE data for demonstration only.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 1. Locations (from requirement R1) ----
        foreach (['Student Centre', 'Siwaka', 'Madaraka'] as $name) {
            Location::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // ---- 2. Categories ----
        foreach (['Breakfast', 'Lunch', 'Snacks', 'Drinks', 'Fast Food'] as $name) {
            Category::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // ---- 3. Admin account (demo password - change for real use) ----
        User::updateOrCreate(['username' => 'admin'], [
            'name'      => 'System Administrator',
            'email'     => 'admin@strathmore.test',
            'password'  => 'Admin@1234',    // hashed automatically by the User model cast
            'role'      => User::ROLE_ADMIN,
            'vendor_id' => null,
            'is_active' => true,
        ]);

        // ---- 4. Vendors, their manager account and their menu ----
        // Menu line format: [item name, category, price KES, is_active (optional, default true)]
        $vendors = [
            'Student Centre Cafe' => [
                'location' => 'Student Centre', 'manager' => 'mgr_studentcentre',
                'menu' => [
                    ['Tea (Chai)', 'Drinks', 30], ['Mandazi', 'Breakfast', 15],
                    ['Beans & Rice', 'Lunch', 120], ['Chicken & Chips', 'Fast Food', 320],
                    ['Fresh Juice', 'Drinks', 90, false],   // out of stock - demo of IsActive = false
                ],
            ],
            'Chapati Corner' => [
                'location' => 'Student Centre', 'manager' => 'mgr_chapati',
                'menu' => [
                    ['Chapati', 'Snacks', 20], ['Chapati Beans', 'Lunch', 90],
                    ['Ugali Sukuma', 'Lunch', 100], ['Samosa', 'Snacks', 30],
                ],
            ],
            'Siwaka Grill' => [
                'location' => 'Siwaka', 'manager' => 'mgr_siwaka',
                'menu' => [
                    ['Pilau', 'Lunch', 180], ['Smokie & Kachumbari', 'Snacks', 50],
                    ['Chips Masala', 'Fast Food', 150], ['Soda 300ml', 'Drinks', 60],
                    ['Githeri', 'Lunch', 90],
                ],
            ],
            'Madaraka Bites' => [
                'location' => 'Madaraka', 'manager' => 'mgr_madaraka',
                'menu' => [
                    ['Egg Sandwich', 'Breakfast', 80], ['Burger & Fries', 'Fast Food', 350],
                    ['Fruit Salad', 'Snacks', 100], ['Milk Tea', 'Drinks', 40],
                    ['Rice & Stew', 'Lunch', 130],
                ],
            ],
        ];

        foreach ($vendors as $vendorName => $data) {
            $vendor = Vendor::firstOrCreate(
                ['name' => $vendorName],
                ['location_id' => Location::where('name', $data['location'])->value('id'), 'is_active' => true]
            );

            // One Vendor Manager per vendor (demo password for every manager)
            User::updateOrCreate(['username' => $data['manager']], [
                'name'      => $vendorName . ' Manager',
                'email'     => $data['manager'] . '@strathmore.test',
                'password'  => 'Vendor@1234',
                'role'      => User::ROLE_VENDOR,
                'vendor_id' => $vendor->id,
                'is_active' => true,
            ]);

            foreach ($data['menu'] as $line) {
                MenuItem::firstOrCreate(
                    ['vendor_id' => $vendor->id, 'name' => $line[0]],
                    [
                        'category_id' => Category::where('name', $line[1])->value('id'),
                        'price'       => $line[2],
                        'is_active'   => $line[3] ?? true,
                    ]
                );
            }
        }
    }
}
