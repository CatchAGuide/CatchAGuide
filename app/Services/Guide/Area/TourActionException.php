<?php

namespace App\Services\Guide\Area;

use RuntimeException;

/**
 * A Guide Area tour action that can't be done: the guide isn't verified yet, or the tour still
 * lacks data (gaps = keys of Guiding::publishingGaps()).
 */
class TourActionException extends RuntimeException
{
    public const NOT_VERIFIED = 'not_verified';
    public const INCOMPLETE = 'incomplete';

    /** @param  list<string>  $gaps */
    public function __construct(public readonly string $reason, public readonly array $gaps = [])
    {
        parent::__construct(__('guide_area.tours.errors.'.$reason));
    }
}
