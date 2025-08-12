<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {                
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Branch::factory(10)->create();
        Customer::factory(50)->create();
        Product::factory(100)->create();
        Order::factory(20)->create();
        OrderItem::factory(100)->create();
    }
}
