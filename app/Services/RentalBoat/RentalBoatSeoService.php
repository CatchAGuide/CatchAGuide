<?php

namespace App\Services\RentalBoat;

use App\Models\RentalBoat;
use Illuminate\Support\Str;

class RentalBoatSeoService
{
    /**
     * Generate SEO-friendly slug for rental boat
     */
    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = RentalBoat::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = RentalBoat::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Generate meta title for rental boat
     */
    public function generateMetaTitle(RentalBoat $rentalBoat): string
    {
        $location = $rentalBoat->location ? " in {$rentalBoat->location}" : "";
        return "{$rentalBoat->title} - Boat Rental{$location} | Your Company";
    }

    /**
     * Generate meta description for rental boat
     */
    public function generateMetaDescription(RentalBoat $rentalBoat): string
    {
        $description = "Rent {$rentalBoat->title}";
        
        if ($rentalBoat->location) {
            $description .= " in {$rentalBoat->location}";
        }
        
        if ($rentalBoat->desc_of_boat) {
            $description .= ". " . Str::limit(strip_tags($rentalBoat->desc_of_boat), 120);
        }
        
        $description .= " - Professional boat rental service with competitive prices.";
        
        return Str::limit($description, 160);
    }

    /**
     * Generate structured data for rental boat
     */
    public function generateStructuredData(RentalBoat $rentalBoat): array
    {
        $prices = $rentalBoat->prices ?? [];
        $offers = [];
        
        foreach (['per_hour', 'per_day', 'per_week'] as $priceType) {
            if (isset($prices[$priceType]) && $prices[$priceType] > 0) {
                $offers[] = [
                    '@type' => 'Offer',
                    'price' => $prices[$priceType],
                    'priceCurrency' => 'EUR',
                    'priceSpecification' => [
                        '@type' => 'UnitPriceSpecification',
                        'price' => $prices[$priceType],
                        'priceCurrency' => 'EUR',
                        'unitText' => $this->getPriceUnitText($priceType)
                    ]
                ];
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $rentalBoat->title,
            'description' => $rentalBoat->desc_of_boat,
            'image' => $rentalBoat->thumbnail_path ? asset($rentalBoat->thumbnail_path) : null,
            'offers' => $offers,
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Your Company'
            ],
            'category' => 'Boat Rental'
        ];
    }

    /**
     * Get price unit text for structured data
     */
    private function getPriceUnitText(string $priceType): string
    {
        return match($priceType) {
            'per_hour' => 'HOUR',
            'per_day' => 'DAY',
            'per_week' => 'WEEK',
            default => 'HOUR'
        };
    }

    /**
     * Generate Open Graph data for social sharing
     */
    public function generateOpenGraphData(RentalBoat $rentalBoat): array
    {
        return [
            'og:title' => $this->generateMetaTitle($rentalBoat),
            'og:description' => $this->generateMetaDescription($rentalBoat),
            'og:image' => $rentalBoat->thumbnail_path ? asset($rentalBoat->thumbnail_path) : null,
            'og:type' => 'product',
            'og:site_name' => 'Your Company',
            'product:price:amount' => $this->getLowestPrice($rentalBoat),
            'product:price:currency' => 'EUR',
        ];
    }

    /**
     * Get the lowest available price for SEO
     */
    private function getLowestPrice(RentalBoat $rentalBoat): ?float
    {
        $prices = $rentalBoat->prices ?? [];
        $validPrices = array_filter($prices, function($price) {
            return is_numeric($price) && $price > 0;
        });
        
        return !empty($validPrices) ? min($validPrices) : null;
    }
}
