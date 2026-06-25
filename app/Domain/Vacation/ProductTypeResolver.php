<?php

namespace App\Domain\Vacation;

use App\Models\Camp;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Model;

final class ProductTypeResolver
{
    public function resolve(Model $listing): ?Pillar
    {
        return match (true) {
            $listing instanceof Camp => Pillar::Camp,
            $listing instanceof Trip => Pillar::Trip,
            default => null,
        };
    }
}
