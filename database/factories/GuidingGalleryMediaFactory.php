<?php

namespace Database\Factories;

use App\Models\Guiding;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuidingGalleryMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'media_id'=>Media::all()->random()->id,
            'guiding_id'=>Guiding::all()->random()->id
        ];
    }
}
