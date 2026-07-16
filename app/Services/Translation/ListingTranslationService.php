<?php

namespace App\Services\Translation;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Language;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ListingTranslationService
{
  public const TYPE_CAMP = 'camp';

  public const TYPE_TRIP = 'trip';

  public const TYPE_RENTAL_BOAT = 'rental_boat';

  public const TYPE_SPECIAL_OFFER = 'special_offer';

  public const TYPE_ACCOMMODATION = 'accommodation';

  /** @var array<string, array{model: class-string<Model>, language_type: string, status: string}> */
  public const LISTING_TYPES = [
    self::TYPE_CAMP => [
      'model' => Camp::class,
      'language_type' => 'camps',
      'status' => 'active',
    ],
    self::TYPE_TRIP => [
      'model' => Trip::class,
      'language_type' => 'trips',
      'status' => 'active',
    ],
    self::TYPE_RENTAL_BOAT => [
      'model' => RentalBoat::class,
      'language_type' => 'rental_boats',
      'status' => 'active',
    ],
    self::TYPE_SPECIAL_OFFER => [
      'model' => SpecialOffer::class,
      'language_type' => 'special_offers',
      'status' => 'active',
    ],
    self::TYPE_ACCOMMODATION => [
      'model' => Accommodation::class,
      'language_type' => 'accommodations',
      'status' => 'active',
    ],
  ];

  public static function defaultTargetLanguages(): array
  {
    return ['en', 'de'];
  }

  public static function defaultSourceLanguage(): string
  {
    return 'de';
  }

  public function translateListing(
    Model $listing,
    string $listingType,
    string $targetLanguage,
    string $fromLanguage = 'de',
    bool $force = false
  ): bool {
    try {
      $config = $this->configFor($listingType);

      if ($fromLanguage === $targetLanguage) {
        return true;
      }

      if (! $force && ! $this->hasSignificantChanges($listing, $listingType, $targetLanguage)) {
        return true;
      }

      $fields = $this->getTranslatableFields($listing, $listingType);

      if ($fields === []) {
        return true;
      }

      $translatedFields = $this->batchTranslateWithGoogle($fields, $targetLanguage, $fromLanguage);
      $storedFields = $this->reconstructFields($listing, $listingType, $translatedFields);

      $this->storeTranslation($listing, $config['language_type'], $targetLanguage, $storedFields, $fields, $listingType);

      return true;
    } catch (\Throwable $e) {
      Log::error('Listing translation failed', [
        'listing_type' => $listingType,
        'listing_id' => $listing->getKey(),
        'target_language' => $targetLanguage,
        'error' => $e->getMessage(),
      ]);

      return false;
    }
  }

  public function hasTranslation(Model $listing, string $listingType, string $targetLanguage): bool
  {
    $config = $this->configFor($listingType);

    return Language::where([
      'source_id' => $listing->getKey(),
      'type' => $config['language_type'],
      'language' => $targetLanguage,
    ])->exists();
  }

  public function hasSignificantChanges(Model $listing, string $listingType, string $targetLanguage): bool
  {
    $config = $this->configFor($listingType);

    $translation = Language::where([
      'source_id' => $listing->getKey(),
      'type' => $config['language_type'],
      'language' => $targetLanguage,
    ])->first();

    if (! $translation) {
      return true;
    }

    $currentHash = md5(serialize($this->getTranslatableFields($listing, $listingType)));

    if ($translation->content !== $currentHash) {
      return true;
    }

    return $this->hasStructurallyInvalidTranslation($listing, $listingType, $translation);
  }

  /**
   * Whether this listing needs translation work for a target language (missing, outdated, or structurally broken).
   */
  public function needsTranslationUpdate(
    Model $listing,
    string $listingType,
    string $targetLanguage,
    string $fromLanguage = 'de'
  ): bool {
    if ($fromLanguage === $targetLanguage) {
      return false;
    }

    return $this->hasSignificantChanges($listing, $listingType, $targetLanguage);
  }

  /**
   * Human-readable reasons why a listing needs translation (empty = up to date).
   *
   * @return array<int, string>
   */
  public function getTranslationUpdateReasons(
    Model $listing,
    string $listingType,
    string $targetLanguage,
    string $fromLanguage = 'de'
  ): array {
    if ($fromLanguage === $targetLanguage) {
      return [];
    }

    $config = $this->configFor($listingType);

    $translation = Language::where([
      'source_id' => $listing->getKey(),
      'type' => $config['language_type'],
      'language' => $targetLanguage,
    ])->first();

    if (! $translation) {
      return ['missing'];
    }

    $reasons = [];
    $currentHash = md5(serialize($this->getTranslatableFields($listing, $listingType)));

    if ($translation->content !== $currentHash) {
      $reasons[] = 'outdated';
    }

    if ($this->hasStructurallyInvalidTranslation($listing, $listingType, $translation)) {
      $reasons[] = 'incomplete';
    }

    return $reasons;
  }

  /**
   * Active listings that are missing, outdated, or have broken stored translations for any target language.
   *
   * @param  array<int, string>  $targetLanguages
   * @return Collection<int, Model>
   */
  public function getListingsNeedingTranslationUpdate(
    string $listingType,
    array $targetLanguages,
    string $fromLanguage = 'de'
  ): Collection {
    $config = $this->configFor($listingType);

    return $config['model']::query()
      ->where('status', $config['status'])
      ->get()
      ->filter(function (Model $listing) use ($listingType, $targetLanguages, $fromLanguage) {
        foreach ($targetLanguages as $language) {
          if ($this->needsTranslationUpdate($listing, $listingType, $language, $fromLanguage)) {
            return true;
          }
        }

        return false;
      })
      ->values();
  }

  /**
   * @param  array<int, string>  $targetLanguages
   */
  public function getListingsMissingTranslations(string $listingType, array $targetLanguages): Collection
  {
    $config = $this->configFor($listingType);

    return $config['model']::query()
      ->where('status', $config['status'])
      ->get()
      ->filter(function (Model $listing) use ($listingType, $targetLanguages) {
        foreach ($targetLanguages as $language) {
          if (! $this->hasTranslation($listing, $listingType, $language)) {
            return true;
          }
        }

        return false;
      })
      ->values();
  }

  /**
   * @return array<int, string>
   */
  public function getMissingLanguages(Model $listing, string $listingType, array $targetLanguages): array
  {
    $missing = [];

    foreach ($targetLanguages as $language) {
      if (! $this->hasTranslation($listing, $listingType, $language)) {
        $missing[] = $language;
      }
    }

    return $missing;
  }

  /**
   * @return array<int, string>
   */
  public function getListingsNeedingTranslation(string $listingType): array
  {
    $config = $this->configFor($listingType);

    return $config['model']::query()
      ->where('status', $config['status'])
      ->where('updated_at', '>', Carbon::now()->subDays(7))
      ->pluck('id')
      ->map(fn ($id) => (int) $id)
      ->all();
  }

  public function getTranslatedListing(Model $listing, string $listingType, string $targetLanguage): ?array
  {
    $batch = $this->getTranslatedListingsBatch(
      [(int) $listing->getKey()],
      $listingType,
      $targetLanguage
    );

    return $batch[(int) $listing->getKey()] ?? null;
  }

  /**
   * @param  array<int, int>  $listingIds
   * @return array<int, array<string, mixed>>
   */
  public function getTranslatedListingsBatch(array $listingIds, string $listingType, string $targetLanguage): array
  {
    $listingIds = array_values(array_unique(array_filter(array_map('intval', $listingIds))));

    if ($listingIds === []) {
      return [];
    }

    $config = $this->configFor($listingType);
    $results = [];

    foreach ($listingIds as $listingId) {
      $cacheKey = $this->cacheKey($listingType, $listingId, $targetLanguage);
      $cached = Cache::get($cacheKey);

      if (is_array($cached)) {
        $results[$listingId] = $cached;
      }
    }

    $missingIds = array_values(array_diff($listingIds, array_keys($results)));

    if ($missingIds === []) {
      return $results;
    }

    $translations = Language::where('type', $config['language_type'])
      ->where('language', $targetLanguage)
      ->whereIn('source_id', $missingIds)
      ->get();

    foreach ($translations as $translation) {
      $listingId = (int) $translation->source_id;

      if (! $translation->json_data) {
        continue;
      }

      $data = is_array($translation->json_data)
        ? $translation->json_data
        : (array) json_decode($translation->json_data, true);

      $results[$listingId] = $data;
      Cache::put($this->cacheKey($listingType, $listingId, $targetLanguage), $data, 3600);
    }

    return $results;
  }

  public function clearTranslationCache(Model $listing, string $listingType): void
  {
    foreach (self::defaultTargetLanguages() as $language) {
      Cache::forget($this->cacheKey($listingType, (int) $listing->getKey(), $language));
    }
  }

  /**
   * @return array<string, array{model: class-string<Model>, language_type: string, status: string}>
   */
  public function configFor(string $listingType): array
  {
    if (! isset(self::LISTING_TYPES[$listingType])) {
      throw new \InvalidArgumentException("Unknown listing type [{$listingType}]");
    }

    return self::LISTING_TYPES[$listingType];
  }

  /**
   * @return array<string, string>
   */
  public function getTranslatableFields(Model $listing, string $listingType): array
  {
    return match ($listingType) {
      self::TYPE_CAMP => $this->fieldsForCamp($listing),
      self::TYPE_TRIP => $this->fieldsForTrip($listing),
      self::TYPE_RENTAL_BOAT => $this->fieldsForRentalBoat($listing),
      self::TYPE_SPECIAL_OFFER => $this->fieldsForSpecialOffer($listing),
      self::TYPE_ACCOMMODATION => $this->fieldsForAccommodation($listing),
      default => [],
    };
  }

  /**
   * @param  array<string, string>  $translatedFields
   * @return array<string, mixed>
   */
  private function reconstructFields(Model $listing, string $listingType, array $translatedFields): array
  {
    $reconstructed = [];

    if ($listingType === self::TYPE_TRIP) {
      $highlights = $this->decodeValue($listing->trip_highlights ?? null);
      if (is_array($highlights)) {
        $reconstructed['trip_highlights'] = $this->reconstructTripHighlights($highlights, $translatedFields);
      }

      $schedule = $this->decodeValue($listing->trip_schedule ?? null);
      if (is_array($schedule)) {
        $reconstructed['trip_schedule'] = $this->reconstructTripSchedule($schedule, $translatedFields);
      }

      $additionalInfo = $this->decodeValue($listing->additional_info ?? null);
      if (is_array($additionalInfo)) {
        $reconstructed['additional_info'] = $this->reconstructAdditionalInfo($additionalInfo, $translatedFields);
      }
    }

    foreach ($this->jsonFieldNames($listingType) as $jsonField) {
      $value = $listing->{$jsonField} ?? null;
      $decoded = $this->decodeValue($value);

      if (! is_array($decoded)) {
        continue;
      }

      $reconstructed[$jsonField] = $this->reconstructIndexedArray($decoded, $jsonField, $translatedFields);
    }

    foreach ($translatedFields as $key => $value) {
      // Skip flattened JSON keys that should already have been consumed above.
      if (preg_match('/^(trip_highlights_items|trip_schedule|additional_info)_/', $key)) {
        continue;
      }

      if (! preg_match('/_\d+(_name|_value)?$/', $key)) {
        $reconstructed[$key] = $value;
      }
    }

    return $reconstructed;
  }

  /**
   * @param  array<string, string>  $sourceFields
   */
  private function storeTranslation(
    Model $listing,
    string $languageType,
    string $targetLanguage,
    array $storedFields,
    array $sourceFields,
    string $listingType
  ): void {
    Language::updateOrCreate(
      [
        'source_id' => $listing->getKey(),
        'type' => $languageType,
        'language' => $targetLanguage,
      ],
      [
        'title' => $storedFields['title'] ?? $listing->title ?? null,
        'json_data' => $storedFields,
        'content' => md5(serialize($sourceFields)),
        'updated_at' => now(),
      ]
    );

    $this->clearTranslationCache($listing, $listingType);

    if ($listing instanceof Trip && ! empty($listing->slug)) {
      app(\App\Services\Trip\TripCacheService::class)->clearTripOfferCacheBySlug((string) $listing->slug);
    }
  }

  /**
   * @return array<string, string>
   */
  private function fieldsForCamp(Model $listing): array
  {
    $fields = $this->collectScalarFields($listing, [
      'title',
      'description_camp',
      'description_area',
      'description_fishing',
      'location',
      'city',
      'region',
      'country',
      'policies_regulations',
      'travel_information',
      'best_travel_times',
      'distance_to_store',
      'distance_to_nearest_town',
      'distance_to_airport',
      'distance_to_ferry_port',
    ]);

    return array_merge(
      $fields,
      $this->collectListField($listing, 'target_fish'),
      $this->collectListField($listing, 'extras')
    );
  }

  /**
   * @return array<string, string>
   */
  private function fieldsForTrip(Model $listing): array
  {
    $fields = $this->collectScalarFields($listing, [
      'title',
      'description',
      'location',
      'city',
      'region',
      'country',
      'meeting_point',
      'accommodation_description',
      'boat_information',
      'provider_name',
      'provider_experience',
      'provider_certifications',
      'boat_staff',
      'cancellation_policy',
      'downpayment_policy',
      'fishing_style',
      'best_arrival_options',
      'arrival_day',
      'boat_type',
      'accommodation_type',
      'nearest_airport',
      'distance_to_water',
    ]);

    foreach (['included', 'excluded', 'catering', 'room_types'] as $jsonField) {
      $fields = array_merge($fields, $this->collectListField($listing, $jsonField));
    }

    return array_merge(
      $fields,
      $this->collectTripHighlights($listing),
      $this->collectTripSchedule($listing),
      $this->collectAdditionalInfo($listing)
    );
  }

  /**
   * @return array<string, string>
   */
  private function fieldsForRentalBoat(Model $listing): array
  {
    $fields = $this->collectScalarFields($listing, [
      'title',
      'desc_of_boat',
      'location',
      'city',
      'region',
      'country',
    ]);

    foreach (['requirements', 'inclusions', 'boat_extras'] as $jsonField) {
      $fields = array_merge($fields, $this->collectListField($listing, $jsonField));
    }

    $pricingExtra = $this->decodeValue($listing->pricing_extra ?? null);
    if (is_array($pricingExtra)) {
      foreach ($pricingExtra as $index => $item) {
        if (is_array($item) && ! empty($item['name']) && is_string($item['name'])) {
          $fields["pricing_extra_{$index}_name"] = $item['name'];
        }
      }
    }

    $boatInformation = $this->decodeValue($listing->boat_information ?? null);
    if (is_array($boatInformation)) {
      foreach ($boatInformation as $index => $item) {
        if (is_array($item) && ! empty($item['value']) && is_string($item['value']) && ! is_numeric($item['value'])) {
          $fields["boat_information_{$index}_value"] = $item['value'];
        }
      }
    }

    return $fields;
  }

  /**
   * @return array<string, string>
   */
  private function fieldsForSpecialOffer(Model $listing): array
  {
    $fields = $this->collectScalarFields($listing, [
      'title',
      'location',
      'city',
      'region',
      'country',
    ]);

    return array_merge($fields, $this->collectListField($listing, 'whats_included'));
  }

  /**
   * @return array<string, string>
   */
  private function fieldsForAccommodation(Model $listing): array
  {
    $fields = $this->collectScalarFields($listing, [
      'title',
      'location',
      'rental_conditions',
    ]);

    foreach (['extras', 'inclusives', 'amenities', 'kitchen_equipment', 'bathroom_amenities'] as $jsonField) {
      $fields = array_merge($fields, $this->collectListField($listing, $jsonField));
    }

    return $fields;
  }

  /**
   * @param  array<int, string>  $fieldNames
   * @return array<string, string>
   */
  private function collectScalarFields(Model $listing, array $fieldNames): array
  {
    $fields = [];

    foreach ($fieldNames as $fieldName) {
      $value = $listing->{$fieldName} ?? null;

      if (is_string($value) && trim($value) !== '' && ! is_numeric($value)) {
        $fields[$fieldName] = trim($value);
      }
    }

    return $fields;
  }

  /**
   * @return array<string, string>
   */
  private function collectListField(Model $listing, string $fieldName): array
  {
    $fields = [];
    $decoded = $this->decodeValue($listing->{$fieldName} ?? null);

    if (! is_array($decoded)) {
      if (is_string($listing->{$fieldName} ?? null) && str_contains((string) $listing->{$fieldName}, ',')) {
        $decoded = array_map('trim', explode(',', (string) $listing->{$fieldName}));
      } else {
        return $fields;
      }
    }

    foreach ($decoded as $index => $item) {
      if (is_string($item) && trim($item) !== '' && ! is_numeric($item)) {
        $fields["{$fieldName}_{$index}"] = trim($item);
        continue;
      }

      if (is_array($item)) {
        if (! empty($item['value']) && is_string($item['value']) && ! is_numeric($item['value'])) {
          $fields["{$fieldName}_{$index}"] = trim($item['value']);
        } elseif (! empty($item['name']) && is_string($item['name']) && ! is_numeric($item['name'])) {
          $fields["{$fieldName}_{$index}"] = trim($item['name']);
        }
      }
    }

    return $fields;
  }

  /**
   * @return array<string, string>
   */
  private function collectTripHighlights(Model $listing): array
  {
    $fields = [];
    $decoded = $this->decodeValue($listing->trip_highlights ?? null);

    if (! is_array($decoded)) {
      return $fields;
    }

    $items = isset($decoded['items']) && is_array($decoded['items'])
      ? $decoded['items']
      : (array_is_list($decoded) ? $decoded : []);

    foreach ($items as $index => $item) {
      $text = is_array($item) ? ($item['text'] ?? '') : (string) $item;
      $text = trim($text);

      if ($text !== '' && ! is_numeric($text)) {
        $fields["trip_highlights_items_{$index}"] = $text;
      }
    }

    return $fields;
  }

  /**
   * @return array<string, string>
   */
  private function collectTripSchedule(Model $listing): array
  {
    $fields = [];
    $decoded = $this->decodeValue($listing->trip_schedule ?? null);

    if (! is_array($decoded)) {
      return $fields;
    }

    foreach ($decoded as $index => $item) {
      if (! is_array($item)) {
        continue;
      }

      foreach (['day_label', 'description'] as $subKey) {
        $value = $item[$subKey] ?? null;
        if (is_string($value) && trim($value) !== '' && ! is_numeric($value)) {
          $fields["trip_schedule_{$index}_{$subKey}"] = trim($value);
        }
      }
    }

    return $fields;
  }

  /**
   * @return array<string, string>
   */
  private function collectAdditionalInfo(Model $listing): array
  {
    $fields = [];
    $decoded = $this->decodeValue($listing->additional_info ?? null);

    if (! is_array($decoded)) {
      return $fields;
    }

    foreach ($decoded as $key => $config) {
      if (! is_array($config)) {
        continue;
      }

      $details = $config['details'] ?? null;
      if (is_string($details) && trim($details) !== '' && ! is_numeric($details)) {
        $fields["additional_info_{$key}_details"] = trim($details);
      }
    }

    return $fields;
  }

  /**
   * @param  array<string, mixed>  $original
   * @param  array<string, string>  $translatedFields
   * @return array<string, mixed>
   */
  private function reconstructTripHighlights(array $original, array &$translatedFields): array
  {
    $reconstructed = $original;
    $items = isset($original['items']) && is_array($original['items'])
      ? $original['items']
      : (array_is_list($original) ? $original : []);

    foreach ($items as $index => $item) {
      $key = "trip_highlights_items_{$index}";
      if (! isset($translatedFields[$key])) {
        continue;
      }

      if (is_array($item)) {
        $items[$index]['text'] = $translatedFields[$key];
      } else {
        $items[$index] = $translatedFields[$key];
      }

      unset($translatedFields[$key]);
    }

    if (isset($original['items']) && is_array($original['items'])) {
      $reconstructed['items'] = array_values($items);
    } else {
      $reconstructed = array_values($items);
    }

    return $reconstructed;
  }

  /**
   * @param  array<int, mixed>  $original
   * @param  array<string, string>  $translatedFields
   * @return array<int, mixed>
   */
  private function reconstructTripSchedule(array $original, array &$translatedFields): array
  {
    $reconstructed = $original;

    foreach ($original as $index => $item) {
      if (! is_array($item)) {
        continue;
      }

      foreach (['day_label', 'description'] as $subKey) {
        $key = "trip_schedule_{$index}_{$subKey}";
        if (isset($translatedFields[$key])) {
          $reconstructed[$index][$subKey] = $translatedFields[$key];
          unset($translatedFields[$key]);
        }
      }
    }

    return array_values($reconstructed);
  }

  /**
   * Preserve associative keys (license_required, etc.) — never array_values().
   *
   * @param  array<string, mixed>  $original
   * @param  array<string, string>  $translatedFields
   * @return array<string, mixed>
   */
  private function reconstructAdditionalInfo(array $original, array &$translatedFields): array
  {
    $reconstructed = $original;

    foreach ($original as $infoKey => $config) {
      if (! is_array($config)) {
        continue;
      }

      $key = "additional_info_{$infoKey}_details";
      if (isset($translatedFields[$key])) {
        $reconstructed[$infoKey]['details'] = $translatedFields[$key];
        unset($translatedFields[$key]);
      }
    }

    return $reconstructed;
  }

  /**
   * Generic JSON list fields only. Trip highlights/schedule/additional_info are handled separately.
   *
   * @return array<int, string>
   */
  private function jsonFieldNames(string $listingType): array
  {
    return match ($listingType) {
      self::TYPE_CAMP => ['target_fish', 'extras'],
      self::TYPE_TRIP => ['included', 'excluded', 'catering', 'room_types'],
      self::TYPE_RENTAL_BOAT => ['requirements', 'inclusions', 'boat_extras', 'pricing_extra', 'boat_information'],
      self::TYPE_SPECIAL_OFFER => ['whats_included'],
      self::TYPE_ACCOMMODATION => ['extras', 'inclusives', 'amenities', 'kitchen_equipment', 'bathroom_amenities'],
      default => [],
    };
  }

  /**
   * @param  array<int, mixed>  $original
   * @param  array<string, string>  $translatedFields
   * @return array<int, mixed>
   */
  private function reconstructIndexedArray(array $original, string $fieldPrefix, array &$translatedFields): array
  {
    $reconstructed = $original;

    foreach ($original as $index => $item) {
      $key = "{$fieldPrefix}_{$index}";

      if (! isset($translatedFields[$key])) {
        continue;
      }

      if (is_string($item)) {
        $reconstructed[$index] = $translatedFields[$key];
      } elseif (is_array($item)) {
        if (array_key_exists('value', $item)) {
          $reconstructed[$index]['value'] = $translatedFields[$key];
        } elseif (array_key_exists('name', $item)) {
          $reconstructed[$index]['name'] = $translatedFields[$key];
        }
      }

      unset($translatedFields[$key]);
    }

    if ($fieldPrefix === 'pricing_extra') {
      foreach ($original as $index => $item) {
        $nameKey = "pricing_extra_{$index}_name";
        if (isset($translatedFields[$nameKey]) && is_array($reconstructed[$index] ?? null)) {
          $reconstructed[$index]['name'] = $translatedFields[$nameKey];
          unset($translatedFields[$nameKey]);
        }
      }
    }

    if ($fieldPrefix === 'boat_information') {
      foreach ($original as $index => $item) {
        $valueKey = "boat_information_{$index}_value";
        if (isset($translatedFields[$valueKey]) && is_array($reconstructed[$index] ?? null)) {
          $reconstructed[$index]['value'] = $translatedFields[$valueKey];
          unset($translatedFields[$valueKey]);
        }
      }
    }

    // Keep associative keys intact (e.g. keyed maps). Only re-index true lists.
    return array_is_list($reconstructed) ? array_values($reconstructed) : $reconstructed;
  }

  private function decodeValue(mixed $value): mixed
  {
    if (is_array($value)) {
      return $value;
    }

    if (! is_string($value) || trim($value) === '') {
      return null;
    }

    $decoded = json_decode($value, true);

    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
  }

  /**
   * @param  array<string, string>  $fields
   * @return array<string, string>
   */
  private function batchTranslateWithGoogle(array $fields, string $toLanguage, string $fromLanguage): array
  {
    $translatedFields = [];

    foreach ($fields as $key => $text) {
      if ($text === '') {
        $translatedFields[$key] = $text;
        continue;
      }

      try {
        $translated = GoogleTranslate::trans($text, $toLanguage, $fromLanguage);

        if (str_contains($translated, 'Führungen')) {
          $translated = str_replace('Führungen', 'Angelguidings', $translated);
        }

        if (str_contains($translated, 'Führung')) {
          $translated = str_replace('Führung', 'guiding', $translated);
        }

        $translatedFields[$key] = ucfirst($translated);
      } catch (\Throwable $e) {
        Log::error('Listing field translation failed', [
          'key' => $key,
          'error' => $e->getMessage(),
        ]);
        $translatedFields[$key] = $text;
      }
    }

    return $translatedFields;
  }

  private function cacheKey(string $listingType, int $listingId, string $targetLanguage): string
  {
    return "listing_translation_{$listingType}_{$listingId}_{$targetLanguage}";
  }

  /**
   * Detect stored translations that are present but unusable (e.g. corrupted additional_info keys).
   */
  private function hasStructurallyInvalidTranslation(Model $listing, string $listingType, Language $translation): bool
  {
    if ($listingType !== self::TYPE_TRIP) {
      return false;
    }

    $data = $translation->json_data;
    if (! is_array($data)) {
      return true;
    }

    if (isset($data['additional_info']) && is_array($data['additional_info'])) {
      foreach (array_keys($data['additional_info']) as $key) {
        if (is_int($key) || (is_string($key) && ctype_digit($key))) {
          return true;
        }
      }
    }

    $sourceSchedule = $this->decodeValue($listing->trip_schedule ?? null);
    if (is_array($sourceSchedule) && $this->tripScheduleHasContent($sourceSchedule)) {
      $translatedSchedule = $data['trip_schedule'] ?? null;
      if (! is_array($translatedSchedule) || ! $this->tripScheduleHasContent($translatedSchedule)) {
        return true;
      }
    }

    $sourceAdditional = $this->decodeValue($listing->additional_info ?? null);
    if (! is_array($sourceAdditional)) {
      return false;
    }

    foreach ($sourceAdditional as $key => $config) {
      if (! is_string($key) || ctype_digit($key) || ! is_array($config)) {
        continue;
      }

      $details = $config['details'] ?? null;
      if (! is_string($details) || trim($details) === '') {
        continue;
      }

      $translatedConfig = $data['additional_info'][$key] ?? null;
      if (! is_array($translatedConfig)) {
        return true;
      }

      $translatedDetails = $translatedConfig['details'] ?? null;
      if (! is_string($translatedDetails) || trim($translatedDetails) === '') {
        return true;
      }
    }

    return false;
  }

  /**
   * @param  array<int, mixed>  $schedule
   */
  private function tripScheduleHasContent(array $schedule): bool
  {
    foreach ($schedule as $item) {
      if (! is_array($item)) {
        continue;
      }

      foreach (['day_label', 'description'] as $field) {
        $value = $item[$field] ?? null;
        if (is_string($value) && trim($value) !== '') {
          return true;
        }
      }
    }

    return false;
  }
}
