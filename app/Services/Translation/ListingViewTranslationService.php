<?php

namespace App\Services\Translation;

use App\Models\Guiding;
use Illuminate\Database\Eloquent\Model;

/**
 * Applies pre-translated content from the languages table to listing models for frontend display.
 * Mirrors the guiding pattern: offline translation in artisan commands, zero API calls on page load.
 */
class ListingViewTranslationService
{
    public function __construct(
        private ListingTranslationService $listingTranslationService,
        private GuidingTranslationService $guidingTranslationService,
    ) {}

    public function applyToModel(Model $listing, string $listingType, ?string $locale = null): void
    {
        $locale = $this->resolveLocale($locale);

        if ($this->isSourceLocale($locale)) {
            return;
        }

        $translated = $this->listingTranslationService->getTranslatedListing($listing, $listingType, $locale);

        if ($translated === null) {
            return;
        }

        $this->mergeTranslationOntoModel($listing, $translated);
    }

    /**
     * @param  iterable<int, Model>  $listings
     */
    public function applyToCollection(iterable $listings, string $listingType, ?string $locale = null): void
    {
        $locale = $this->resolveLocale($locale);

        if ($this->isSourceLocale($locale)) {
            return;
        }

        $byId = [];
        $ids = [];

        foreach ($listings as $listing) {
            if (! $listing instanceof Model) {
                continue;
            }

            $id = (int) $listing->getKey();
            $byId[$id] = $listing;
            $ids[] = $id;
        }

        if ($ids === []) {
            return;
        }

        $translations = $this->listingTranslationService->getTranslatedListingsBatch(
            array_values(array_unique($ids)),
            $listingType,
            $locale
        );

        foreach ($translations as $id => $translated) {
            if (isset($byId[$id]) && is_array($translated)) {
                $this->mergeTranslationOntoModel($byId[$id], $translated);
            }
        }
    }

    public function applyToGuiding(Guiding $guiding, ?string $locale = null): void
    {
        $locale = $this->resolveLocale($locale);
        $sourceLanguage = $guiding->language ?? ListingTranslationService::defaultSourceLanguage();

        if ($sourceLanguage === $locale) {
            return;
        }

        $translated = $this->guidingTranslationService->getTranslatedGuiding($guiding, $locale);

        if ($translated !== null) {
            $guiding->translated = $translated;
        }
    }

    /**
     * @param  iterable<int, Guiding>  $guidings
     */
    public function applyToGuidings(iterable $guidings, ?string $locale = null): void
    {
        $locale = $this->resolveLocale($locale);

        $idsNeedingTranslation = [];
        $guidingsById = [];

        foreach ($guidings as $guiding) {
            if (! $guiding instanceof Guiding) {
                continue;
            }

            $sourceLanguage = $guiding->language ?? ListingTranslationService::defaultSourceLanguage();

            if ($sourceLanguage === $locale) {
                continue;
            }

            $idsNeedingTranslation[] = $guiding->id;
            $guidingsById[$guiding->id] = $guiding;
        }

        if ($idsNeedingTranslation === []) {
            return;
        }

        $translationMap = $this->guidingTranslationService->getTranslatedGuidingsBatch(
            array_values(array_unique($idsNeedingTranslation)),
            $locale
        );

        foreach ($translationMap as $id => $translated) {
            if (isset($guidingsById[$id])) {
                $guidingsById[$id]->translated = $translated;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $translated
     */
    private function mergeTranslationOntoModel(Model $listing, array $translated): void
    {
        $fillable = array_flip($listing->getFillable());

        foreach ($translated as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (! isset($fillable[$field])) {
                continue;
            }

            $listing->setAttribute($field, $value);
        }
    }

    private function resolveLocale(?string $locale): string
    {
        return $locale ?: (app()->getLocale() ?: ListingTranslationService::defaultSourceLanguage());
    }

    private function isSourceLocale(string $locale): bool
    {
        return $locale === ListingTranslationService::defaultSourceLanguage();
    }
}
