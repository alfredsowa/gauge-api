<?php

namespace Database\Factories;

use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class EmailChangeFactory extends Factory
{
    protected $model = EmailChange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->randomElement(User::pluck('id')->toArray()),
            'new_email' => fake()->name(),
            'old_email' => fake()->randomElement(User::pluck('email')->toArray()),
            'token' => Str::random(4),
            'changed' => false,
        ];
    }
}
