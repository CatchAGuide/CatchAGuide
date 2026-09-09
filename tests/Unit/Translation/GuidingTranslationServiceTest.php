<?php

namespace Tests\Unit\Translation;

use App\Models\Guiding;
use App\Services\Translation\GuidingTranslationService;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Guiding::other_information/requirements/recommendations/pricing_extra each
 * have accessors that return an Illuminate\Support\Collection of enriched
 * {id,name,value} rows rather than the raw id-keyed array stored in the
 * column. getTranslatableFields()/reconstructJsonFields() must read the raw
 * attribute instead of the magic property, or every is_array() check on
 * these fields silently fails and the field is skipped from translation.
 */
class GuidingTranslationServiceTest extends TestCase
{
    private GuidingTranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GuidingTranslationService();
    }

    /**
     * Build a Guiding with attributes as if freshly loaded from the
     * database, so getRawOriginal() reflects them (a bare `new Guiding([...])`
     * leaves $original empty until synced).
     */
    private function guidingWithRawAttributes(array $attributes): Guiding
    {
        $guiding = new Guiding($attributes);
        $guiding->syncOriginal();

        return $guiding;
    }

    private function invokeGetTranslatableFields(Guiding $guiding): array
    {
        $method = new ReflectionMethod($this->service, 'getTranslatableFields');
        $method->setAccessible(true);

        return $method->invoke($this->service, $guiding);
    }

    private function invokeReconstructJsonFields(Guiding $guiding, array $translatedFields): array
    {
        $method = new ReflectionMethod($this->service, 'reconstructJsonFields');
        $method->setAccessible(true);

        return $method->invoke($this->service, $guiding, $translatedFields);
    }

    public function test_get_translatable_fields_includes_other_information_entries(): void
    {
        $guiding = $this->guidingWithRawAttributes([
            'other_information' => json_encode([
                '1' => 'Kinderfreundlich text',
                '4' => 'No alcohol allowed',
            ]),
        ]);

        $fields = $this->invokeGetTranslatableFields($guiding);

        $this->assertSame('Kinderfreundlich text', $fields['other_information_1']);
        $this->assertSame('No alcohol allowed', $fields['other_information_4']);
    }

    public function test_get_translatable_fields_includes_requirements_and_recommendations(): void
    {
        $guiding = $this->guidingWithRawAttributes([
            'requirements' => json_encode(['2' => ['value' => 'Bring a passport']]),
            'recommendations' => json_encode(['3' => 'Sun cream']),
        ]);

        $fields = $this->invokeGetTranslatableFields($guiding);

        $this->assertSame('Bring a passport', $fields['requirements_2']);
        $this->assertSame('Sun cream', $fields['recommendations_3']);
    }

    public function test_get_translatable_fields_includes_pricing_extra_names(): void
    {
        $guiding = $this->guidingWithRawAttributes([
            'pricing_extra' => json_encode(['7' => ['name' => 'Extra rod', 'price' => 10]]),
        ]);

        $fields = $this->invokeGetTranslatableFields($guiding);

        $this->assertSame('Extra rod', $fields['pricing_extra_7_name']);
    }

    public function test_get_translatable_fields_skips_empty_or_null_list_fields_without_error(): void
    {
        $guiding = $this->guidingWithRawAttributes([
            'other_information' => null,
            'requirements' => null,
            'recommendations' => null,
            'pricing_extra' => null,
        ]);

        $fields = $this->invokeGetTranslatableFields($guiding);

        $this->assertArrayNotHasKey('other_information_1', $fields);
        $this->assertSame([], array_filter(array_keys($fields), function ($key) {
            return str_starts_with($key, 'other_information_')
                || str_starts_with($key, 'requirements_')
                || str_starts_with($key, 'recommendations_')
                || str_starts_with($key, 'pricing_extra_');
        }));
    }

    public function test_reconstruct_json_fields_writes_translated_value_back_under_the_matching_id_key(): void
    {
        $guiding = $this->guidingWithRawAttributes([
            'other_information' => json_encode(['1' => 'Child allowed', '4' => 'No alcohol']),
        ]);

        $reconstructed = $this->invokeReconstructJsonFields($guiding, [
            'other_information_1' => 'Kinderfreundlich',
        ]);

        $this->assertSame(
            ['1' => 'Kinderfreundlich', '4' => 'No alcohol'],
            $reconstructed['other_information']
        );
    }

    public function test_reconstruct_json_fields_preserves_nested_value_shape_for_requirements(): void
    {
        $guiding = $this->guidingWithRawAttributes([
            'requirements' => json_encode(['2' => ['value' => 'Bring a passport']]),
        ]);

        $reconstructed = $this->invokeReconstructJsonFields($guiding, [
            'requirements_2' => 'Reisepass mitbringen',
        ]);

        $this->assertSame(
            ['2' => ['value' => 'Reisepass mitbringen']],
            $reconstructed['requirements']
        );
    }
}
