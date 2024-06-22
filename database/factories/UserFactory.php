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
        $typeUser = fake()->numberBetween(3, 4);

        return [
            'name' => fake()->name(),
            'cpf' => $typeUser === 4 ?
                fake()->numberBetween(100000000, 999999999) : null,
            'cnpj' => $typeUser === 3 ?
                fake()->numberBetween(10000000000000, 99999999999999) : null,
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'user_type_id' => $typeUser,
            'wallet' => fake()->numberBetween(0, 1000000),
        ];
    }

    public function merchant(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'cpf' => null,
                'cnpj' => fake()->numberBetween(10000000000000, 99999999999999),
                'user_type_id' => 3,
            ];
        });
    }

    public function usual(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'cpf' => fake()->numberBetween(100000000, 999999999),
                'cnpj' => null,
                'user_type_id' => 4,
            ];
        });
    }
}