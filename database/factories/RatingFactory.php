<?php

namespace Database\Factories;

use App\Models\Guiding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'description'=>$this->faker->text,
            'rating'=>$this->faker->numberBetween(1,10),
            'user_id'=>User::all()->random()->id,
            'guide_id'=> Guiding::all()->random()->id
        ];
    }
}
