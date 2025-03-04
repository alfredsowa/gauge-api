<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "business_id" => 1,
            "supplier_id" => fake()->randomElement(Supplier::pluck('id')->toArray()),
            "material_id" => fake()->randomElement(Material::pluck('id')->toArray()),
            "added_by" => 1,
            "status" => fake()->randomElement(['pending','paid','partial']),
            "purchase_date" => fake()->date(),
            "quantity" => fake()->randomNumber(2),
            "unit_price" => fake()->randomFloat(3),
            "amount_paid" => fake()->randomFloat(3),
            "discounts" => 0,
            "shipping" => 0,
            "invoice_number" => fake()->uuid(),
            "invoice_upload" => null,
            "purchase_details" => fake()->paragraph(3, false),
            "notes" => fake()->paragraph(3, false),
        ];
    }
}
