<?php

namespace App\Services\Guiding;

use App\Models\BoatExtras;
use App\Models\Inclussion;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Small master tables (targets, inclusions, waters, methods, boat extras) loaded once per
 * request. Guiding's name getters used to run one find() per stored ID, so an offer page with
 * its similar-tour cards made about 250 queries, most of them repeats.
 * Bound as scoped: every request and every queued job reads fresh rows.
 */
class GuidingMasterDataLookup
{
    private const MODELS = [Target::class, Inclussion::class, Water::class, Method::class, BoatExtras::class];

    /** @var array<class-string<Model>, Collection<int, Model>> */
    private array $tables = [];

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function find(string $modelClass, int|string $id): ?Model
    {
        if (! in_array($modelClass, self::MODELS, true)) {
            throw new InvalidArgumentException("{$modelClass} is not a cached master table.");
        }

        $this->tables[$modelClass] ??= $modelClass::query()->get()->keyBy('id');

        return $this->tables[$modelClass]->get((int) $id);
    }
}
