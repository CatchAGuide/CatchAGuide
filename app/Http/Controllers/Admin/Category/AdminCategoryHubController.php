<?php

namespace App\Http\Controllers\Admin\Category;

use App\Domain\CategoryPage\CategoryPageDimension;
use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Language;
use App\Models\Target;

class AdminCategoryHubController extends Controller
{
    public function index()
    {
        $cards = collect(CategoryPageDimension::hubCards())->map(function (array $card) {
            $card['count'] = $this->countForCard($card['key']);

            return $card;
        });

        return view('admin.pages.category.hub', [
            'cards' => $cards,
        ]);
    }

    private function countForCard(string $key): int
    {
        return match ($key) {
            CategoryPageDimension::DESTINATION_HUB => Language::query()
                ->where('type', CategoryPageEntityType::DESTINATION_HUB)
                ->where('source_id', CategoryPageEntityType::DESTINATION_HUB_SOURCE_ID)
                ->where('title', '!=', '')
                ->count() > 0 ? 1 : 0,
            CategoryPageDimension::TARGETS => Target::query()->count(),
            CategoryPageDimension::METHODS => \App\Models\Method::query()->count(),
            CategoryPageDimension::COUNTRY => Country::query()->count(),
            CategoryPageDimension::REGION => \App\Models\Region::query()->count(),
            CategoryPageDimension::CITY => \App\Models\City::query()->count(),
            default => 0,
        };
    }
}
