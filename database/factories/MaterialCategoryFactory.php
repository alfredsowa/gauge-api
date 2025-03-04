<?php

namespace Database\Factories;

use App\Models\MaterialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaterialCategory>
 */
class MaterialCategoryFactory extends Factory
{
    protected $model = MaterialCategory::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "business_id" => fake()->randomElement([1,2]),
            'title' => fake()->word(),
            'description' => fake()->sentence()
        ];
    }
}
