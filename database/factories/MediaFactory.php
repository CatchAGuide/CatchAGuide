<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'file_path'=>$this->faker->filePath(),
            'file_type'=>$this->faker->filePath(),
        ];
    }
}
