<?php

namespace Database\Factories;

use App\Enums\CustomerStatusEnum;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected int|null $user_id = null;

    public function definition(): array
    {
        return [
            'user_id' => $this->user_id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'status' => $this->faker->randomElement(CustomerStatusEnum::cases()),
        ];
    }
}
