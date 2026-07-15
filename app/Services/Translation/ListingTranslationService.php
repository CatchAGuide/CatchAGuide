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

    return $translation->content !== $currentHash;
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
   * @param  array<string, string>  $originalFields
   * @param  array<string, string>  $translatedFields
   * @return array<string, mixed>
   */
  private function reconstructFields(Model $listing, string $listingType, array $translatedFields): array
  {
    $reconstructed = [];

    foreach ($this->jsonFieldNames($listingType) as $jsonField) {
      $value = $listing->{$jsonField} ?? null;
      $decoded = $this->decodeValue($value);

      if (! is_array($decoded)) {
        continue;
      }

      $reconstructed[$jsonField] = $this->reconstructIndexedArray($decoded, $jsonField, $translatedFields);
    }

    foreach ($translatedFields as $key => $value) {
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
      'meeting_point',
      'accommodation_description',
      'boat_information',
      'provider_name',
      'provider_experience',
      'provider_certifications',
      'boat_staff',
      'cancellation_policy',
      'fishing_style',
      'best_arrival_options',
      'arrival_day',
      'boat_type',
      'accommodation_type',
      'nearest_airport',
      'distance_to_water',
    ]);

    foreach (['trip_highlights', 'included', 'excluded', 'additional_info', 'catering', 'room_types'] as $jsonField) {
      $fields = array_merge($fields, $this->collectListField($listing, $jsonField));
    }

    return $fields;
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
   * @return array<int, string>
   */
  private function jsonFieldNames(string $listingType): array
  {
    return match ($listingType) {
      self::TYPE_CAMP => ['target_fish', 'extras'],
      self::TYPE_TRIP => ['trip_highlights', 'included', 'excluded', 'additional_info', 'catering', 'room_types'],
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

    return array_values($reconstructed);
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
}
