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

    public function test_unwraps_json_encoded_csv_with_unicode_escapes_and_wrapping_quotes(): void
    {
        app()->setLocale('de');

        $raw = json_encode('Flussbarsch,Hecht,Äsche,Bachsaibling');
        $this->assertStringContainsString('\\u', $raw);

        $resolved = app(TargetFishNameResolver::class)->resolve($raw);
        $names = array_column($resolved, 'name');

        $this->assertSame(['Flussbarsch', 'Hecht', 'Äsche', 'Bachsaibling'], $names);
        foreach ($names as $name) {
            $this->assertStringStartsNotWith('"', $name);
            $this->assertStringEndsNotWith('"', $name);
            $this->assertStringNotContainsString('\\u', $name);
        }
        $this->assertNotNull($resolved[0]['id']);
        $this->assertNotNull($resolved[2]['id']);
    }

    public function test_strips_html_entities_and_stray_quotes_from_names(): void
    {
        app()->setLocale('de');

        $resolved = app(TargetFishNameResolver::class)->resolve([
            '"Flussbarsch',
            '&Auml;sche',
            'Bachsaibling"',
        ]);

        $this->assertSame(['Flussbarsch', 'Äsche', 'Bachsaibling'], array_column($resolved, 'name'));
        $this->assertNotNull($resolved[0]['id']);
        $this->assertNotNull($resolved[1]['id']);
    }
}
