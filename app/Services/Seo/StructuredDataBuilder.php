<?php

namespace App\Services\Seo;

use App\Models\Camp;
use App\Models\Trip;
use App\Services\Location\CountryResolver;
use Illuminate\Support\Str;

/**
 * schema.org JSON-LD arrays for page types that had none beyond the site-wide Organization.
 * Built only from real listing data; empty fields are omitted rather than emitted as null.
 */
class StructuredDataBuilder
{
    private const DESCRIPTION_LIMIT = 500;

    public function __construct(
        private readonly CountryResolver $countries,
    ) {}

    /**
     * BreadcrumbList mirroring the visual breadcrumb: Home, then each item. Items without a URL
     * (the current page) link to the current URL, as Google requires an item on every crumb
     * except the last and accepts it there too.
     *
     * @param  list<array{label: string, url?: ?string}>  $items
     */
    public function breadcrumbList(array $items, string $currentUrl): array
    {
        $crumbs = array_merge([['label' => __('message.home'), 'url' => route('welcome')]], $items);

        $elements = [];
        foreach ($crumbs as $crumb) {
            $label = trim(strip_tags((string) ($crumb['label'] ?? '')));
            if ($label === '') {
                continue;
            }
            $elements[] = [
                '@type' => 'ListItem',
                'position' => count($elements) + 1,
                'name' => $label,
                'item' => ! empty($crumb['url']) ? $crumb['url'] : $currentUrl,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    public function trip(Trip $trip, string $url): array
    {
        $price = $trip->getLowestPrice();

        return $this->withoutEmpty([
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $trip->title,
            'description' => $this->plainText($trip->description),
            'url' => $url,
            'image' => $this->image($trip->thumbnail_path),
            'touristType' => 'Fishing trip',
            'areaServed' => array_values(array_filter([$trip->city, $trip->region, $trip->country])),
            'provider' => filled($trip->provider_name) ? ['@type' => 'Organization', 'name' => $trip->provider_name] : null,
            'offers' => $price > 0 ? [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => $trip->currency ?: 'EUR',
                'availability' => 'https://schema.org/InStock',
                'url' => $url,
            ] : null,
        ]);
    }

    public function camp(Camp $camp, string $url): array
    {
        $hasGeo = $camp->latitude !== null && $camp->longitude !== null;

        return $this->withoutEmpty([
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $camp->title,
            'description' => $this->plainText($camp->description_camp),
            'url' => $url,
            'image' => $this->image($camp->thumbnail_path),
            'address' => $this->withoutEmpty([
                '@type' => 'PostalAddress',
                'addressLocality' => $camp->city,
                'addressRegion' => $camp->region,
                // Stored as a localized name/slug ("spanien"); schema.org wants ISO 3166-1.
                'addressCountry' => $this->countries->resolveIso(null, str_replace('-', ' ', (string) $camp->country)),
            ], ['@type']),
            'geo' => $hasGeo ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $camp->latitude,
                'longitude' => (float) $camp->longitude,
            ] : null,
        ]);
    }

    private function plainText(?string $html): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html))) ?? '');

        return $text === '' ? null : Str::limit($text, self::DESCRIPTION_LIMIT);
    }

    private function image(?string $path): ?string
    {
        return media_path_usable($path) ? media_url($path) : null;
    }

    /**
     * Drops null/empty values. Returns null when only the listed always-present keys remain.
     *
     * @param  list<string>  $structuralKeys
     */
    private function withoutEmpty(array $data, array $structuralKeys = []): ?array
    {
        $filtered = array_filter($data, fn ($value) => $value !== null && $value !== '' && $value !== []);

        return array_diff_key($filtered, array_flip($structuralKeys)) === [] && $structuralKeys !== [] ? null : $filtered;
    }
}
