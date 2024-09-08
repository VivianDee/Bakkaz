<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // Default password for all users
            'country' => 'NG',
            'admin_tag' => null, // or any default value
            'secret_key' => Str::random(10),
            'fcm' => null,
            'password_history' => null,
            'ip_address' => $this->faker->ipv4,
            'media' => null,
            'account_type' => 'anonymous-signup', // or any default account type
            'active_status' => 1,
            'state' => $this->faker->state,
            'email_verified_at' => now(),
            'unread_message_count' => 0,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
