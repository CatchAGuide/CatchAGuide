<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message'=>$this->faker->text,
            'chat_id'=>User::all()->random()->id,
            'user_id'=>User::all()->random()->id,
        ];
    }
}
