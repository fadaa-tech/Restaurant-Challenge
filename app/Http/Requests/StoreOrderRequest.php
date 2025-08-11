<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:paypal,cod',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->isEmpty()) return false;
            
            $items = collect($this->input('items'))
                ->groupBy('product_id')
                ->map(fn($group) => [ 
                    'product_id' => $group[0]['product_id'],
                    'quantity' => $group->sum('quantity')
                ])
                ->values();

            // Optionally, you can merge duplicated items together
            $this->merge(['items' => $items]);

            $products = Product::whereIn('id', $items->pluck('product_id'))->get()->keyBy('id');

            foreach ($items as $item) {
                if ($products->get($item['product_id'])->available < $item['quantity']) {
                    $validator->errors()->add(
                        "items.*.quantity",
                        "The quantity for product {$products->get($item['product_id'])->name} exceeds available stock ({$products->get($item['product_id'])->available})."
                    );
                }
            }

            return true;
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array

    {
        return [
            'customer_id.required' => 'The customer ID is required.',
            'status.required' => 'The order status is required.',
            'total_amount.required' => 'The total amount is required.',
            'items.required' => 'At least one item is required in the order.',
            'items.array' => 'The items must be an array.',
            'items.*.product_id.required' => 'The product ID is required for each item.',
            'items.*.product_id.exists' => 'The selected product does not exist.',
            'items.*.quantity.required' => 'The quantity is required for each item.',
            'items.*.quantity.integer' => 'The quantity must be an integer.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
