<?php

namespace Database\Factories;

use App\Models\BlockedEvent;
use App\Models\Guiding;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_paid'=>$this->faker->boolean,
            'user_id'=>User::all()->random()->id,
            'guiding_id'=>Guiding::all()->random()->id,
            'blocked_event_id'=>BlockedEvent::all()->random()->id,
        ];
    }
}
