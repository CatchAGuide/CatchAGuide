<?php

namespace Tests\Unit\CategoryPage;

use App\Models\CategoryPage;
use App\Models\Target;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FavoriteTargetSpeciesResolverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_favorites_are_returned_before_non_favorites(): void
    {
        $marker = 'test-fav-target-'.uniqid();

        $favoriteTarget = new Target();
        $favoriteTarget->name = $marker.'-fav';
        $favoriteTarget->save();

        $otherTarget = new Target();
        $otherTarget->name = $marker.'-other';
        $otherTarget->save();

        CategoryPage::query()->create([
            'name' => $marker.'-fav',
            'type' => 'Targets',
            'slug' => $marker.'-fav',
            'source_id' => (string) $favoriteTarget->id,
            'is_favorite' => true,
        ]);

        CategoryPage::query()->create([
            'name' => $marker.'-other',
            'type' => 'Targets',
            'slug' => $marker.'-other',
            'source_id' => (string) $otherTarget->id,
            'is_favorite' => false,
        ]);

        $resolver = app(FavoriteTargetSpeciesResolver::class);
        $result = $resolver->resolve(500);

        $favoriteRow = $result->first(fn (array $row) => $row['slug'] === $marker.'-fav');
        $otherRow = $result->first(fn (array $row) => $row['slug'] === $marker.'-other');

        $this->assertNotNull($favoriteRow);
        $this->assertNotNull($otherRow);
        $this->assertSame($favoriteTarget->id, $favoriteRow['source_id']);

        $favoriteIndex = $result->search(fn (array $row) => $row['slug'] === $marker.'-fav');
        $otherIndex = $result->search(fn (array $row) => $row['slug'] === $marker.'-other');
        $this->assertLessThan($otherIndex, $favoriteIndex);
    }

    public function test_limit_is_respected(): void
    {
        $resolver = app(FavoriteTargetSpeciesResolver::class);

        $result = $resolver->resolve(3);

        $this->assertLessThanOrEqual(3, $result->count());
    }
}
