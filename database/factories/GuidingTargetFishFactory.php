<?php

namespace Database\Factories;

use App\Models\Guiding;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuidingTargetFishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'=>$this->faker->name,
            'guiding_id'=>Guiding::all()->random()->id
        ];
    }
}
