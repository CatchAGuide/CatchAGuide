<?php

namespace Tests\Unit\Vacation;

use App\Models\Target;
use App\Services\Vacation\TargetFishNameResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TargetFishNameResolverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_resolves_numeric_ids_from_the_targets_table(): void
    {
        $target = new Target();
        $target->name = 'Hecht';
        $target->name_en = 'Pike';
        $target->save();

        app()->setLocale('en');

        $resolved = app(TargetFishNameResolver::class)->resolve([$target->id]);

        $this->assertCount(1, $resolved);
        $this->assertSame($target->id, $resolved[0]['id']);
        $this->assertSame('Pike', $resolved[0]['name']);
    }

    public function test_matches_stored_german_names_to_the_targets_table(): void
    {
        $target = new Target();
        $target->name = 'Zander-'.uniqid();
        $target->name_en = 'Pike-perch-'.uniqid();
        $target->save();

        app()->setLocale('en');

        $resolved = app(TargetFishNameResolver::class)->resolve([$target->name]);

        $this->assertCount(1, $resolved);
        $this->assertSame($target->id, $resolved[0]['id']);
        $this->assertSame($target->name_en, $resolved[0]['name']);
    }

    public function test_keeps_custom_names_without_a_table_id(): void
    {
        $custom = 'Custom reef species '.uniqid();

        $resolved = app(TargetFishNameResolver::class)->resolve($custom);

        $this->assertCount(1, $resolved);
        $this->assertNull($resolved[0]['id']);
        $this->assertSame($custom, $resolved[0]['name']);
    }

    public function test_splits_comma_separated_legacy_strings(): void
    {
        $target = new Target();
        $target->name = 'Wels-'.uniqid();
        $target->name_en = 'Catfish-'.uniqid();
        $target->save();

        app()->setLocale('de');

        $resolved = app(TargetFishNameResolver::class)->resolve($target->name.', Mystery Carp');

        $this->assertCount(2, $resolved);
        $this->assertSame($target->id, $resolved[0]['id']);
        $this->assertSame($target->name, $resolved[0]['name']);
        $this->assertNull($resolved[1]['id']);
        $this->assertSame('Mystery Carp', $resolved[1]['name']);
    }

    public function test_returns_empty_for_blank_input(): void
    {
        $resolver = app(TargetFishNameResolver::class);

        $this->assertSame([], $resolver->resolve(null));
        $this->assertSame([], $resolver->resolve(''));
        $this->assertSame([], $resolver->resolve([]));
    }
}
