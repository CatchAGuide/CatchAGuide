<?php

namespace App\Services\Security;

final class CrawlerClassification
{
    public function __construct(
        public readonly CrawlerLane $lane,
        public readonly ?string $name = null,
        public readonly bool $verified = false,
    ) {}

    public function isTrusted(): bool
    {
        return $this->lane->isTrusted();
    }
}
