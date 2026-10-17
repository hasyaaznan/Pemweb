<?php

namespace Database\Factories;

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
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => '08'.fake()->unique()->numerify('##########'),
            'password' => static::$password ??= Hash::make('password'),
            'peran' => User::PERAN_ORANG_TUA,
            'remember_token' => Str::random(10),
        ];
    }

    public function coach(): static
    {
        return $this->state(fn () => ['peran' => User::PERAN_COACH]);
    }

    public function manajemen(): static
    {
        return $this->state(fn () => ['peran' => User::PERAN_MANAJEMEN]);
    }
}
