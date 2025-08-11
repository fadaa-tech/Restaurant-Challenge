<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;

class BigOrderDataSeeder extends Seeder
{
    public function run()
    {
        OrderItem::factory(10 * 1000)->create();
    }
}
