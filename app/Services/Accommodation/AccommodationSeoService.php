<?php

namespace App\Services\Accommodation;

use App\Models\Accommodation;
use Illuminate\Support\Str;

class AccommodationSeoService
{
    /**
     * Generate SEO-friendly slug for accommodation
     */
    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = Accommodation::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = Accommodation::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Generate meta title for accommodation
     */
    public function generateMetaTitle(Accommodation $accommodation): string
    {
        $location = $accommodation->location ? " in {$accommodation->location}" : "";
        $type = $accommodation->accommodation_type ? " - {$accommodation->accommodation_type}" : "";
        return "{$accommodation->title}{$type}{$location}";
    }

    /**
     * Generate meta description for accommodation
     */
    public function generateMetaDescription(Accommodation $accommodation): string
    {
        $description = $accommodation->title;
        
        if ($accommodation->location) {
            $description .= " in {$accommodation->location}";
        }
        
        if ($accommodation->description) {
            $description .= ". " . Str::limit(strip_tags($accommodation->description), 120);
        }
        
        if ($accommodation->max_occupancy) {
            $description .= " Sleeps up to {$accommodation->max_occupancy} guests.";
        }
        
        return Str::limit($description, 160);
    }

    /**
     * Generate structured data for accommodation
     */
    public function generateStructuredData(Accommodation $accommodation): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $accommodation->title,
            'description' => $accommodation->description,
            'image' => $accommodation->thumbnail_path ? asset($accommodation->thumbnail_path) : null,
        ];

        if ($accommodation->lat && $accommodation->lng) {
            $data['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $accommodation->lat,
                'longitude' => $accommodation->lng
            ];
        }

        if ($accommodation->location) {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $accommodation->city ?? $accommodation->location,
                'addressCountry' => $accommodation->country ?? ''
            ];
        }

        // Add pricing offers
        $offers = [];
        if ($accommodation->price_per_night) {
            $offers[] = [
                '@type' => 'Offer',
                'price' => $accommodation->price_per_night,
                'priceCurrency' => $accommodation->currency ?? 'EUR',
                'priceSpecification' => [
                    '@type' => 'UnitPriceSpecification',
                    'price' => $accommodation->price_per_night,
                    'priceCurrency' => $accommodation->currency ?? 'EUR',
                    'unitText' => 'NIGHT'
                ]
            ];
        }

        if ($accommodation->price_per_week) {
            $offers[] = [
                '@type' => 'Offer',
                'price' => $accommodation->price_per_week,
                'priceCurrency' => $accommodation->currency ?? 'EUR',
                'priceSpecification' => [
                    '@type' => 'UnitPriceSpecification',
                    'price' => $accommodation->price_per_week,
                    'priceCurrency' => $accommodation->currency ?? 'EUR',
                    'unitText' => 'WEEK'
                ]
            ];
        }

        if (!empty($offers)) {
            $data['offers'] = $offers;
        }

        return $data;
    }

    /**
     * Generate Open Graph data for social sharing
     */
    public function generateOpenGraphData(Accommodation $accommodation): array
    {
        return [
            'og:title' => $this->generateMetaTitle($accommodation),
            'og:description' => $this->generateMetaDescription($accommodation),
            'og:image' => $accommodation->thumbnail_path ? asset($accommodation->thumbnail_path) : null,
            'og:type' => 'product',
            'product:price:amount' => $this->getLowestPrice($accommodation),
            'product:price:currency' => $accommodation->currency ?? 'EUR',
        ];
    }

    /**
     * Get the lowest available price for SEO
     */
    private function getLowestPrice(Accommodation $accommodation): ?float
    {
        $prices = array_filter([
            $accommodation->price_per_night,
            $accommodation->price_per_week
        ], function($price) {
            return is_numeric($price) && $price > 0;
        });
        
        return !empty($prices) ? min($prices) : null;
    }
}

