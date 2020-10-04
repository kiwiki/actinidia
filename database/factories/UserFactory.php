<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'name' => $this->faker->name,
            // faker->userName uses dots, which is not a valid character for us
            // small slugs are used instead
            'username' => $this->faker->slug(1),
            'email' => $this->faker->unique()->safeEmail,
            'last_login_at' => now(),
            'score' => $this->faker->numberBetween(0, 500),
        ];
    }
}
