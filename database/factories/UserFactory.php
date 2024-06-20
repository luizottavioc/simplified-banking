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
        $typeUser = fake()->numberBetween(2, 3);

        return [
            'name' => fake()->name(),
            'cpf' => $typeUser === 3 ? fake()->numberBetween(100000000, 999999999) : null,
            'cnpj' => $typeUser === 2 ? fake()->numberBetween(10000000000000, 99999999999999) : null,
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'user_type_id' => fake()->numberBetween(2, 3),
            'wallet' => fake()->numberBetween(0, 100000),
        ];
    }
}
