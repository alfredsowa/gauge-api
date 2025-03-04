<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Production>
 */
class ProductionFactory extends Factory
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
            'title' => Str::headline(fake()->word()),
            'priority' => fake()->randomElement(config('options.priority')),
            'status' => fake()->randomElement(config('options.production_status')),
            'description' => fake()->paragraph(3, false),
            'user_id' => 1,
            'assignee_id' => fake()->randomElement(Employee::pluck('id')->toArray()),
            'labour_cost' => fake()->randomFloat(2,0,190),
            'quantity' => fake()->randomNumber(2),
            'deadline_date' => fake()->date(),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'type' => fake()->randomElement(config('options.production_type')),
            'estimated_hours' => fake()->randomNumber(1),
            'actual_hours' => fake()->randomNumber(1),
        ];
    }
}
