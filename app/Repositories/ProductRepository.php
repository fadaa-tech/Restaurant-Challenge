<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function updateInventory($items)
    {
        foreach ($items as $item) {
            Product::find($item['product_id'])->decrement('available', $item['quantity']);
        }
    }

    public function getProductsByIds($ids)
    {
        return Product::whereIn('id', $ids)->get();
    }
}
