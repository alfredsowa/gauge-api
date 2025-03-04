<?php

namespace Database\Factories;

use App\Models\IntermediateGoodsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IntermediateGoodsCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = IntermediateGoodsCategory::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "business_id" => fake()->randomElement([1,2]),
            'title' => fake()->word()
        ];
    }
}
