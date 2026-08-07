<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'phone'             => '01' . fake()->randomElement(['3', '7', '8', '9']) . fake()->numerify('########'),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password123'),
            'role'              => 'customer', // ডিফল্ট নিরাপদ রোল
            'avatar'            => null,
            'is_active'         => true,
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * State: Unverified User
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State: Super Admin User
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super-admin',
        ]);
    }

    /**
     * State: Admin User
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * State: Manager User
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
        ]);
    }

    /**
     * State: Inactive / Blocked User
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Multifunctional Hook: Configure factory lifecycle listeners.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Spatie Permission প্যাকেজ ইনস্টল থাকলে ইউজার তৈরির পরপরই অটোমেটিক পারমিশন রোল অ্যাসাইন হয়ে যাবে
            if (class_exists(Role::class)) {
                $roleName = $user->role ?? 'customer';
                $role = Role::findOrCreate($roleName, 'web');
                $user->assignRole($role);
            }
        });
    }
}