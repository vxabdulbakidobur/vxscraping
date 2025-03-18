<?php

namespace Database\Factories;

use App\Enums\DefaultRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('12345678'),
            'status' => $this->faker->randomElement(UserStatusEnum::cases()),
            'role' => DefaultRoleEnum::ADMIN->value,
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): Factory|UserFactory
    {
        return $this->afterCreating(function (User $user) {
            Customer::factory(rand(0, 9))->create([
                'user_id' => $user->id
            ]);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
