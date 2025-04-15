<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'date_of_birth' => $this->faker->dateTimeBetween('1990-01-01', '2000-12-31')->format('Y-m-d'),
            'phone' => fake()->phoneNumber,
            'diagnosis' => $this->faker->optional()->sentence
        ];
    }

}
