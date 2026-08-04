<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlockedEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'from' => Carbon::now(),
            'due'=>Carbon::now(),
            'type'=> $this->faker->randomElement(['private', 'booking']),
            'user_id' => User::whereVerifiedGuide()->get()->random()->id
        ];
    }
}
