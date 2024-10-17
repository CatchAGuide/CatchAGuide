<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuidingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title'=>$this->faker->name,
            'location'=>$this->faker->city,
            'recommended_for'=>$this->faker->sentence,
            'max_guests'=>$this->faker->numberBetween(1,10),
            'duration' => $this->faker->randomFloat(1, 1, 4),
            'required_special_license'=>$this->faker->sentence,
            'fishing_type'=>$this->faker->name,
            'fishing_from'=>$this->faker->name,
            'description'=>$this->faker->text,
            'required_equipment'=>$this->faker->word,
            'provided_equipment'=>$this->faker->word,
            'additional_information'=>$this->faker->word,
            'price'=> $this->faker->randomFloat(2, 10, 100),
            'price_two_persons'=> $this->faker->randomFloat(2, 10, 100),
            'price_three_persons'=> $this->faker->randomFloat(2, 10, 100),
            'price_four_persons'=> $this->faker->randomFloat(2, 10, 100),
            'price_five_persons'=> $this->faker->randomFloat(2, 10, 100),
            'thumbnail_id'=>Media::all()->random()->id,
            'user_id'=>User::all()->random()->id,
        ];
    }
}
