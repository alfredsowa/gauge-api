<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "business_id" => fake()->randomElement(Business::pluck('id')->toArray()),
            "contact_person" => fake()->name(),
            "company_name" => fake()->name(),
            "contact_detail" => fake()->randomElement([fake()->phoneNumber(),fake()->safeEmail()]),
            "location" => fake()->city(),
            "total_spending" => fake()->randomFloat(2),
            "total_orders" => fake()->randomNumber(3),
            "last_order" => fake()->date(),
            "note" => fake()->paragraph(3, false),
        ];
    }
}
