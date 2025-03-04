<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => 1,
            'user_id' => 1,
            'product_id' => fake()->randomElement(Product::where('is_active',true)->where('business_id',1)->pluck('id')->toArray()),
            'employee_id' => fake()->randomElement(Product::where('business_id',1)->pluck('id')->toArray()),
            'customer_id' => fake()->randomElement(Product::where('business_id',1)->pluck('id')->toArray()),
            'sale_type' => fake()->randomElement(config('options.sales_type')),
            'payment_status' => fake()->randomElement(config('options.payment_status')),
            'payment_method' => fake()->randomElement(config('options.payment_method')),
            'order_status' => fake()->randomElement(config('options.order_status')),
            'sale_date_time' => fake()->dateTime(),
            'quantity' => fake()->randomNumber(2),
            'selling_price' => fake()->randomFloat(2,0,1000),
            'total_amount_paid' => fake()->randomFloat(2,0,1000),
            'invoice_number' => fake()->text(10),
            'delivery_details' => fake()->address(),
        ];
    }
}
