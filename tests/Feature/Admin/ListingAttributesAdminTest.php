<?php

namespace Tests\Feature\Admin;

use App\Models\BathroomAmenity;
use App\Models\Employee;
use App\Services\Admin\ListingAttributeRegistry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ListingAttributesAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function typeSlugProvider(): array
    {
        $cases = [];
        foreach (ListingAttributeRegistry::slugs() as $slug) {
            $cases[$slug] = [$slug];
        }

        return $cases;
    }

    #[DataProvider('typeSlugProvider')]
    public function test_guest_is_redirected_from_attribute_index(string $type): void
    {
        $response = $this->get(route('admin.settings.attributes.index', $type));

        $response->assertRedirect();
    }

    #[DataProvider('typeSlugProvider')]
    public function test_admin_can_view_attribute_index(string $type): void
    {
        $this->actingAsEmployee();

        $config = ListingAttributeRegistry::get($type);
        $response = $this->get(route('admin.settings.attributes.index', $type));

        $response->assertOk();
        $response->assertSee(__('admin.listing_attributes.types.'.$config['label_key']), false);
        $response->assertSee('listing-attributes-table', false);
    }

    #[DataProvider('typeSlugProvider')]
    public function test_admin_can_store_update_and_delete_attribute(string $type): void
    {
        $this->actingAsEmployee();

        $config = ListingAttributeRegistry::get($type);
        $modelClass = $config['model'];
        $fields = $config['fields'];

        $payload = $this->buildPayload($type, $fields, 'Create');

        $store = $this->post(route('admin.settings.attributes.store', $type), $payload);
        $store->assertRedirect();
        $store->assertSessionHas('success');

        $created = $modelClass::query()
            ->where('name_en', $payload['name_en'])
            ->latest('id')
            ->first();

        $this->assertNotNull($created, "Failed to create row for [{$type}]");

        if (in_array('input_type', $fields, true)) {
            $this->assertSame($payload['input_type'], $created->getRawOriginal('input_type'));
        }
        if (in_array('placeholder', $fields, true)) {
            $this->assertSame($payload['placeholder'], $created->getRawOriginal('placeholder'));
        }
        if (in_array('placeholder_en', $fields, true)) {
            $this->assertSame($payload['placeholder_en'], $created->getRawOriginal('placeholder_en'));
        }
        if (in_array('is_active', $fields, true)) {
            $this->assertTrue((bool) $created->is_active);
        }
        if ($type === 'camp-facilities') {
            $this->assertSame($payload['name_en'], $created->getRawOriginal('name'));
            $this->assertSame($payload['name_de'], $created->getRawOriginal('name_de'));
        }

        $updatePayload = $this->buildPayload($type, $fields, 'Update');
        $update = $this->put(route('admin.settings.attributes.update', [$type, $created->id]), $updatePayload);
        $update->assertRedirect();
        $update->assertSessionHas('success');

        $created->refresh();
        $this->assertSame($updatePayload['name_en'], $created->getRawOriginal('name_en'));

        $delete = $this->delete(route('admin.settings.attributes.destroy', [$type, $created->id]));
        $delete->assertRedirect();
        $delete->assertSessionHas('success');

        $this->assertNull($modelClass::query()->find($created->id));
    }

    public function test_unknown_type_returns_404(): void
    {
        $this->actingAsEmployee();

        $this->get('/admin/settings/attributes/not-a-real-type')->assertNotFound();
    }

    public function test_accommodation_write_clears_form_data_cache(): void
    {
        $this->actingAsEmployee();

        Cache::put('accommodation_form_data', ['stale' => true], 3600);
        $this->assertTrue(Cache::has('accommodation_form_data'));

        $response = $this->post(route('admin.settings.attributes.store', 'bathroom-amenities'), [
            'name_en' => 'Cache Flush Amenity EN',
            'name' => 'Cache Flush Amenity DE',
            'is_active' => 1,
            'sort_order' => 99,
        ]);

        $response->assertRedirect();
        $this->assertFalse(Cache::has('accommodation_form_data'));

        BathroomAmenity::query()
            ->where('name_en', 'Cache Flush Amenity EN')
            ->delete();
    }

    public function test_legacy_target_index_still_works(): void
    {
        $this->actingAsEmployee();

        $response = $this->get(route('admin.settings.targetindex'));

        $response->assertOk();
        $response->assertSee(__('admin.listing_attributes.types.targets'), false);
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    private function buildPayload(string $type, array $fields, string $suffix): array
    {
        $unique = $suffix.' '.uniqid('', true);
        $payload = [
            'name_en' => "{$unique} EN",
        ];

        if (in_array('name_de', $fields, true)) {
            $payload['name_de'] = "{$unique} DE";
        } elseif (in_array('name', $fields, true)) {
            $payload['name'] = "{$unique} DE";
        }

        if (in_array('is_active', $fields, true)) {
            $payload['is_active'] = 1;
        }

        if (in_array('sort_order', $fields, true)) {
            $payload['sort_order'] = 10;
        }

        if (in_array('input_type', $fields, true)) {
            $payload['input_type'] = 'text';
        }

        if (in_array('placeholder', $fields, true)) {
            $payload['placeholder'] = "{$unique} placeholder DE";
        }

        if (in_array('placeholder_en', $fields, true)) {
            $payload['placeholder_en'] = "{$unique} placeholder EN";
        }

        return $payload;
    }
}
