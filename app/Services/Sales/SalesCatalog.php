<?php

namespace App\Services\Sales;

use App\Enums\AccommodationPriceUnit;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Trip;
use App\Models\User;
use App\Services\Checkout\Camp\CampCheckoutPricing;
use App\Services\Checkout\TourCheckoutPricing;
use App\Services\Translation\ListingTranslationService;
use App\Services\Translation\ListingViewTranslationService;
use Illuminate\Support\Str;

/**
 * Read-only access to the listing data the offer builder needs: the "#ID · Name" options of
 * each product type and a product snapshot (texts in the document language, prices, partner).
 *
 * Snapshots are plain arrays so they can live in the Livewire builder state and in the
 * card item's meta; the calculator prices a card from its snapshot alone, so a saved
 * document never changes when the listing does. Prices reuse the checkout pricing classes,
 * so an offer quotes exactly what the public checkout would.
 */
class SalesCatalog
{
    public const TOUR = 'tour';

    public const CAMP = 'camp';

    public const TRIP = 'trip';

    public function __construct(
        private readonly ListingViewTranslationService $translations,
        private readonly ListingTranslationService $listingTranslations,
        private readonly SalesLinks $links,
    ) {}

    /**
     * Selection options ("#1057 · Hecht-Guiding im Bodden"), no prices.
     *
     * @return list<array{id: int, label: string}>
     */
    public function options(string $type): array
    {
        $query = match ($type) {
            self::TOUR => Guiding::query()->where('status', 1),
            self::CAMP => Camp::query()->where('status', 'active'),
            self::TRIP => Trip::query()->where('status', 'active'),
            default => null,
        };

        if ($query === null) {
            return [];
        }

        return $query->orderBy('id')->get(['id', 'title'])
            ->map(fn ($listing) => ['id' => (int) $listing->id, 'label' => self::label((int) $listing->id, (string) $listing->title)])
            ->all();
    }

    public static function label(int $id, string $name): string
    {
        return '#'.$id.' · '.trim($name);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function product(string $type, int $id, string $locale): ?array
    {
        return match ($type) {
            self::TOUR => $this->tour($id, $locale),
            self::CAMP => $this->camp($id, $locale),
            self::TRIP => $this->trip($id, $locale),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tour(int $id, string $locale): ?array
    {
        $guiding = Guiding::with('user')->find($id);
        if ($guiding === null) {
            return null;
        }

        $this->translations->applyToGuiding($guiding, $locale);
        $missing = ($guiding->language ?: ListingTranslationService::defaultSourceLanguage()) !== $locale
            && $guiding->translated === null;

        $pricing = TourCheckoutPricing::for($guiding);

        $prices = $pricing->priceTable();
        $extras = array_map(fn (array $extra) => [
            // Non-numeric so the key stays an object key in the Livewire state.
            'key' => 'e'.$extra['index'],
            'name' => $extra['name'],
            'price' => $extra['price'],
            'unit' => $extra['unit'],
        ], $pricing->extras());

        return $this->withFingerprint([
            'type' => self::TOUR,
            'id' => (int) $guiding->id,
            'title' => trim((string) $guiding->title),
            'location' => $this->location([$guiding->city, $guiding->country], $guiding->location),
            'url' => $guiding->slug ? $this->links->listing('guidings.show', ['slug' => $guiding->slug], $locale) : null,
            'duration' => $this->tourDuration($guiding, $locale),
            'max' => $pricing->maxGuests(),
            'partner' => $this->partner($guiding->user),
            'prices' => $prices,
            'extras' => $extras,
            'translation_missing' => $missing,
        ], ['prices' => $prices, 'extras' => array_map(fn ($e) => [$e['key'], $e['price'], $e['unit']], $extras)]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function camp(int $id, string $locale): ?array
    {
        $camp = Camp::with(CampCheckoutPricing::relations())->with('user')->find($id);
        if ($camp === null) {
            return null;
        }

        $missing = $this->listingTranslationMissing($camp, ListingTranslationService::TYPE_CAMP, $locale);

        $this->translations->applyToModel($camp, ListingTranslationService::TYPE_CAMP, $locale);
        $this->translations->applyToCollection($camp->accommodations, ListingTranslationService::TYPE_ACCOMMODATION, $locale);
        $this->translations->applyToCollection($camp->rentalBoats, ListingTranslationService::TYPE_RENTAL_BOAT, $locale);
        $this->translations->applyToGuidings($camp->guidings, $locale);

        $pricing = CampCheckoutPricing::for($camp);

        $accommodations = [];
        foreach ($pricing->accommodations() as $unit) {
            $accommodations[(string) $unit['id']] = [
                'id' => $unit['id'],
                'name' => $unit['name'],
                'capacity' => $unit['capacity'],
                'unit' => AccommodationPriceUnit::fromListing($unit['unit'])->value,
                'tiers' => array_map(fn (array $tier) => ['persons' => $tier['persons'], ...$tier['rate']->toArray()], $unit['tiers']),
            ];
        }

        $boats = [];
        foreach ($pricing->boats() as $boat) {
            $boats[(string) $boat['id']] = ['id' => $boat['id'], 'name' => $boat['name'], 'capacity' => $boat['capacity'], ...$boat['rate']->toArray()];
        }

        $guidings = [];
        foreach ($pricing->tours() as $tour) {
            $guidings[(string) $tour['id']] = ['id' => $tour['id'], 'name' => $tour['name'], 'capacity' => $tour['capacity'], 'prices' => $tour['prices']];
        }

        return $this->withFingerprint([
            'type' => self::CAMP,
            'id' => (int) $camp->id,
            'title' => trim((string) $camp->title),
            'location' => $this->location([$camp->city, $camp->country], $camp->location),
            'url' => $camp->slug ? $this->links->listing('vacations.camps.show', ['slug' => $camp->slug], $locale) : null,
            'partner' => $this->partner($camp->user),
            'accommodations' => $accommodations,
            'boats' => $boats,
            'guidings' => $guidings,
            'cancellation_policy' => $this->policy($camp->policies_regulations),
            'translation_missing' => $missing,
        ], [
            'accommodations' => array_map(fn ($a) => [$a['unit'], $a['tiers']], $accommodations),
            'boats' => array_map(fn ($b) => [$b['daily'], $b['weekly']], $boats),
            'guidings' => array_map(fn ($g) => $g['prices'], $guidings),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function trip(int $id, string $locale): ?array
    {
        $trip = Trip::with('user')->find($id);
        if ($trip === null) {
            return null;
        }

        $missing = $this->listingTranslationMissing($trip, ListingTranslationService::TYPE_TRIP, $locale);
        $this->translations->applyToModel($trip, ListingTranslationService::TYPE_TRIP, $locale);

        $inclusions = array_values(array_filter(array_map(
            fn ($row) => trim((string) (is_array($row) ? ($row['name'] ?? $row['value'] ?? '') : $row)),
            (array) (decode_if_json($trip->included, true) ?: []),
        )));

        $partner = $this->partner($trip->user);
        if ($partner !== null && filled($trip->provider_name)) {
            $partner['name'] = trim((string) $trip->provider_name);
        }

        $price = round((float) $trip->price_per_person, 2);
        $min = max(1, (int) $trip->group_size_min);
        $max = max($min, (int) ($trip->group_size_max ?: $min));

        return $this->withFingerprint([
            'type' => self::TRIP,
            'id' => (int) $trip->id,
            'title' => trim((string) $trip->title),
            'location' => $this->location([$trip->city, $trip->country], $trip->location),
            'url' => $trip->slug ? $this->links->listing('vacations.trips.show', ['slug' => $trip->slug], $locale) : null,
            'nights' => max(0, (int) $trip->duration_nights),
            'price' => $price,
            'min' => $min,
            'max' => $max,
            'inclusions' => $inclusions,
            'partner' => $partner,
            'cancellation_policy' => $this->policy($trip->cancellation_policy),
            'translation_missing' => $missing,
        ], ['price' => $price, 'nights' => (int) $trip->duration_nights]);
    }

    /**
     * True when the listing's current prices differ from the ones in a stored snapshot.
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function pricesChanged(array $snapshot): bool
    {
        $fresh = $this->product((string) ($snapshot['type'] ?? ''), (int) ($snapshot['id'] ?? 0), 'de');

        return $fresh !== null && ($fresh['fingerprint'] ?? null) !== ($snapshot['fingerprint'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<string, mixed>  $priceData
     * @return array<string, mixed>
     */
    private function withFingerprint(array $product, array $priceData): array
    {
        $product['fingerprint'] = md5((string) json_encode($priceData));

        return $product;
    }

    /**
     * @return array{id: int, name: string, email: string, phone: string}|null
     */
    private function partner(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => (int) $user->id,
            'name' => trim($user->firstname.' '.$user->lastname),
            'email' => (string) $user->email,
            'phone' => trim((string) $user->phone),
        ];
    }

    /**
     * @param  list<mixed>  $parts
     */
    private function location(array $parts, mixed $fallback): string
    {
        $parts = array_values(array_unique(array_filter(array_map(fn ($part) => Str::ucfirst(trim((string) $part)), $parts))));

        return $parts !== [] ? implode(', ', $parts) : trim((string) $fallback);
    }

    /**
     * The host's cancellation policy text from the listing (OQ5), or null when it has none.
     */
    private function policy(mixed $text): ?string
    {
        $text = trim(strip_tags((string) $text));

        return $text !== '' ? $text : null;
    }

    private function tourDuration(Guiding $guiding, string $locale): ?string
    {
        $value = (int) $guiding->duration;
        if ($value <= 0) {
            return null;
        }

        return trans_choice($guiding->duration_type === 'multi_day' ? 'sales.duration_days' : 'sales.duration_hours', $value, ['count' => $value], $locale);
    }

    private function listingTranslationMissing(Camp|Trip $listing, string $type, string $locale): bool
    {
        if ($locale === ListingTranslationService::defaultSourceLanguage()) {
            return false;
        }

        return $this->listingTranslations->getTranslatedListing($listing, $type, $locale) === null;
    }
}
