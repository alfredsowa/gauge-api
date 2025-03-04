<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'industry' => fake()->randomElement([
                "Food and Beverage",
                "Retail",
                "Fashion",
                "Wood Work",
                "Healthcare and Wellness",
                "Information Technology",
                "Consulting and Professional Services"
              ]),
            'business_type' => fake()->randomElement([
                "Sole Proprietorship",
                "Partnership",
                "Limited Liability Company (LLC)",
                "Corporation",
                "Nonprofit Organization"
              ]),
            'business_size' => fake()->randomElement([
                "Micro Business",
                "Small Business",
                "Medium-sized Business",
                "Large Business",
                "Enterprise"
              ]),
            'contact' => fake()->phoneNumber(),
            'website' => fake()->domainName(),
            'city' => fake()->city(),
            'tax_identification_number' => fake()->regexify('[A-Z]{3}[0-4]{7}'),
            'logo' => "https://via.placeholder.com/360x360.png/00bbcc?text=logo",
            'country' => fake()->country(),
            'currency' => fake()->randomElement([
                "USD",
                "EUR",
                "GBP",
                "JPY",
                "NGN",
                'GHS'
              ]),
            'language' => fake()->randomElement([
                "English",
                "French",
                "Chinese",
                "Arabic",
                "Spanish",
              ]),
            'timezone' => fake()->randomElement([
                "UTC",
                "GMT",
                "Eastern Time (ET)",
                "Central Time (CT)",
                "Mountain Time (MT)",
                "Pacific Time (PT)",
                "Australian Eastern Time (AET)"
              ]),
            'address' => fake()->address(),
        ];
    }
}
