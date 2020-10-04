<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = "{$this->faker->unique()->firstName} {$this->faker->lastName}";

        return [
            'name' => $name,
            'username' => Str::slug($name),
            'email' => $this->faker->unique()->safeEmail,
            'last_login_at' => now(),
            'score' => $this->faker->numberBetween(0, 500),
            'is_admin' => $this->faker->boolean(10),
        ];
    }
}
