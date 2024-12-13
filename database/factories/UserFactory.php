<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_code' => fake()->numberBetween(100,10000000),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'sex' => fake()->randomElement(['male', 'female']),
            'birthday' => fake()->dateTimeThisCentury(),
            'citizen_card_number' => fake()->numberBetween(100000000000,109999999999),
            'issue_date' => fake()->dateTimeThisCentury(),
            'place_of_grant' => fake()->city(),
            'nation' => 'Kinh',
            'avatar' => null,
            'major_code' => 'WD', 
            'course_code' => '1', 
            'semester_code' => 'S1',
            'role' => '3',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
