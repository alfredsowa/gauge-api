<?php

namespace Database\Factories;

use App\Models\IntermediateGoodsCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
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
            'supplier_id' => fake()->randomElement(Supplier::pluck('id')->toArray()),
            'name' => fake()->word(),
            'slug' => fake()->word(),
            'description' => fake()->paragraph(3, false),
            'user_id' => 1,
            'price' => fake()->randomFloat(2,0,190),
            'discount_price' => fake()->randomNumber(2),
            'product_category_id' => fake()->randomElement(IntermediateGoodsCategory::pluck('id')->toArray()),
            'stock_quantity' => fake()->randomNumber(3),
            'min_stock_quantity' => fake()->randomNumber(1),
            'sku' => fake()->word(),
            'barcode' => fake()->uuid(),
            'image' => fake()->imageUrl(),
            'is_produced' => true,
            'is_active' => true,
            'is_featured' => false,
            'attributes' => null,
        ];
    }
}
