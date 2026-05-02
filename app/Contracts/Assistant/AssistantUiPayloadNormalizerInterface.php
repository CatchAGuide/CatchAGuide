<?php

namespace App\Contracts\Assistant;

interface AssistantUiPayloadNormalizerInterface
{
    /**
     * @param  array<string, mixed>|null  $ui
     * @return array<string, mixed>|null
     */
    public function normalize(?array $ui): ?array;

    /**
     * @param  array<string, mixed>|null  $ui
     * @param  array<int, array<string, mixed>>  $fallbackCards
     * @return array<string, mixed>|null
     */
    public function mergeCatalogFallback(?array $ui, array $fallbackCards): ?array;
}
