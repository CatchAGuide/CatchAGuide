<?php

namespace App\Jobs;

use App\Models\Guiding;
use App\Models\Vacation;
use App\Services\Translation\GuidingTranslationService;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\VacationTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Translates one listing/guiding/vacation into every target language that is missing or
 * outdated, dispatched automatically by TranslatableModelObserver on create/update. Cheap by
 * design: the actual API call only happens when the per-type "needs update" hash check says so
 * (see ListingTranslationService/GuidingTranslationService/VacationTranslationService), so a save
 * that didn't touch translatable content costs one DB lookup per target language, not an API call.
 */
class TranslateListingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $listingType,
        public readonly int $listingId,
    ) {
        // Always dispatch onto the real "database" queue connection, regardless of the app's
        // default queue driver. This must never run inline with the triggering save() — that
        // would make every admin save block on a live translation-API network call, and (since
        // PHPUnit forces QUEUE_CONNECTION=sync for fast, deterministic tests) would mean every
        // test that creates/updates a Trip/Camp/Guiding/Vacation/etc. fires a real translation
        // request. Set in the constructor rather than as a property default — Queueable already
        // declares $connection with no default, and redeclaring it with one is a fatal trait/
        // class property conflict.
        $this->connection = 'database';
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping("translate-{$this->listingType}-{$this->listingId}")];
    }

    public function handle(
        ListingTranslationService $listingTranslation,
        GuidingTranslationService $guidingTranslation,
        VacationTranslationService $vacationTranslation,
    ): void {
        match ($this->listingType) {
            'guiding' => $this->translateGuiding($guidingTranslation),
            'vacation' => $this->translateVacation($vacationTranslation),
            default => $this->translateListing($listingTranslation),
        };
    }

    private function translateListing(ListingTranslationService $service): void
    {
        $config = $service->configFor($this->listingType);
        $listing = $config['model']::find($this->listingId);

        if (! $listing) {
            return;
        }

        $source = ListingTranslationService::defaultSourceLanguage();

        foreach (ListingTranslationService::defaultTargetLanguages() as $target) {
            if ($target === $source) {
                continue;
            }

            if (! $service->needsTranslationUpdate($listing, $this->listingType, $target, $source)) {
                continue;
            }

            if (! $service->translateListing($listing, $this->listingType, $target, $source)) {
                Log::warning('Automatic listing translation failed', [
                    'listing_type' => $this->listingType,
                    'listing_id' => $this->listingId,
                    'target_language' => $target,
                ]);
            }
        }
    }

    private function translateGuiding(GuidingTranslationService $service): void
    {
        $guiding = Guiding::find($this->listingId);

        if (! $guiding) {
            return;
        }

        $source = $guiding->language ?: 'de';

        foreach (GuidingTranslationService::defaultTargetLanguages() as $target) {
            if ($target === $source) {
                continue;
            }

            if (! $service->hasSignificantChanges($guiding, $target)) {
                continue;
            }

            if ($service->translateGuiding($guiding, $target)) {
                $service->markContentUpdated($guiding);
            } else {
                Log::warning('Automatic guiding translation failed', [
                    'guiding_id' => $this->listingId,
                    'target_language' => $target,
                ]);
            }
        }
    }

    private function translateVacation(VacationTranslationService $service): void
    {
        $vacation = Vacation::find($this->listingId);

        if (! $vacation) {
            return;
        }

        $source = $vacation->language ?: 'de';

        foreach (['en', 'de'] as $target) {
            if ($target === $source) {
                continue;
            }

            if (! $service->needsTranslationUpdate($vacation, $target, $source)) {
                continue;
            }

            if (! $service->translateVacation($vacation, $target)) {
                Log::warning('Automatic vacation translation failed', [
                    'vacation_id' => $this->listingId,
                    'target_language' => $target,
                ]);
            }
        }
    }
}
