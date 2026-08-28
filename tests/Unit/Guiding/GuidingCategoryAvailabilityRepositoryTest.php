<?php

namespace Tests\Unit\Guiding;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\User;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GuidingCategoryAvailabilityRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('guiding_category_availability_v1');
    }

    private function createTour(array $overrides = []): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Availability Tour '.uniqid(),
            'slug' => 'availability-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }

    public function test_country_with_a_tour_is_available(): void
    {
        $marker = 'test-zedonia-'.uniqid();

        $this->createTour(['country' => $marker]);
        Cache::forget('guiding_category_availability_v1');

        $repo = app(GuidingCategoryAvailabilityRepository::class);

        $this->assertTrue($repo->hasGuidingsForCountry($marker));
    }

    public function test_country_without_any_tour_is_not_available(): void
    {
        $marker = 'test-zedonia-none-'.uniqid();

        $repo = app(GuidingCategoryAvailabilityRepository::class);

        $this->assertFalse($repo->hasGuidingsForCountry($marker));
    }

    public function test_target_fish_with_a_tour_is_available(): void
    {
        $target = new Target();
        $target->forceFill([
            'name' => 'Availability Pike '.uniqid(),
            'name_en' => 'Availability Pike',
        ])->save();

        $this->createTour(['target_fish' => json_encode([$target->id])]);
        Cache::forget('guiding_category_availability_v1');

        $repo = app(GuidingCategoryAvailabilityRepository::class);

        $this->assertTrue($repo->hasGuidingsForTarget($target->id));
        $this->assertContains($target->id, $repo->targetIdsWithGuidings());
    }

    public function test_target_fish_without_any_tour_is_not_available(): void
    {
        $target = new Target();
        $target->forceFill([
            'name' => 'Availability Ghost Pike '.uniqid(),
            'name_en' => 'Availability Ghost Pike',
        ])->save();

        $repo = app(GuidingCategoryAvailabilityRepository::class);

        $this->assertFalse($repo->hasGuidingsForTarget($target->id));
    }

    public function test_method_with_a_tour_is_available(): void
    {
        $method = new Method();
        $method->forceFill([
            'name' => 'Availability Fly '.uniqid(),
            'name_en' => 'Availability Fly',
        ])->save();

        $this->createTour(['fishing_methods' => json_encode([$method->id])]);
        Cache::forget('guiding_category_availability_v1');

        $repo = app(GuidingCategoryAvailabilityRepository::class);

        $this->assertTrue($repo->hasGuidingsForMethod($method->id));
        $this->assertContains($method->id, $repo->methodIdsWithGuidings());
    }

    public function test_method_without_any_tour_is_not_available(): void
    {
        $method = new Method();
        $method->forceFill([
            'name' => 'Availability Ghost Fly '.uniqid(),
            'name_en' => 'Availability Ghost Fly',
        ])->save();

        $repo = app(GuidingCategoryAvailabilityRepository::class);

        $this->assertFalse($repo->hasGuidingsForMethod($method->id));
    }
}
