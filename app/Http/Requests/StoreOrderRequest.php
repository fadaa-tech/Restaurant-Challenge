<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'branch_id' => 'required|integer|exists:branches,id',
            'name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'items.*.special_instructions' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch ID is required.',
            'branch_id.exists' => 'The selected branch does not exist.',
            'name.required' => 'Order name is required.',
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Please provide a valid email address.',
            'items.required' => 'At least one item is required for the order.',
            'items.min' => 'At least one item is required for the order.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.product_id.exists' => 'The selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 100.',
        ];
    }
}
