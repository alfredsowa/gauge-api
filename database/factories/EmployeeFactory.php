<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'phone_number' => fake()->phoneNumber(),
            'image' => fake()->imageUrl(),
            'hire_date' => fake()->date(),
            'title' => fake()->jobTitle(),
            'salary' => fake()->randomNumber(3),
            'department' => fake()->randomElement(['Production','Customer Service','Quality Control']),
        ];
    }
}
