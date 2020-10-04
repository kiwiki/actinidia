<?php

namespace Database\Factories;

use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Component::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->component;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'verified_at' => $this->faker->optional()->dateTime,
            'deleted_at' => $this->faker->optional(0.1)->dateTime
        ];
    }
}
