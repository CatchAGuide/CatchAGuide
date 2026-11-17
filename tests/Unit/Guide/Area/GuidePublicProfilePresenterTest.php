<?php

namespace Tests\Unit\Guide\Area;

use App\Enums\CertificateStatus;
use App\Enums\GuideStatus;
use App\Models\GuideCertificate;
use App\Models\GuideTag;
use App\Models\User;
use App\Presenters\Guiding\GuidePublicProfilePresenter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GuidePublicProfilePresenterTest extends TestCase
{
    use DatabaseTransactions;

    private function guide(): User
    {
        return User::factory()->create([
            'firstname' => 'Lars',
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ])->fresh(['information']);
    }

    private function certificate(User $guide, string $type, CertificateStatus $status, ?string $validUntil = null): void
    {
        GuideCertificate::create([
            'user_id' => $guide->id,
            'type' => $type,
            'file_disk' => 'local',
            'file_path' => 'guide-certificates/'.$guide->id.'/'.$type.'.pdf',
            'original_name' => $type.'.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
            'valid_until' => $validUntil,
            'status' => $status->value,
        ]);
    }

    public function test_guide_without_guide_area_data_falls_back_to_first_name(): void
    {
        $profile = app(GuidePublicProfilePresenter::class)->present($this->guide());

        $this->assertSame('Lars', $profile['name']);
        $this->assertSame([], $profile['specialties']);
        $this->assertSame([], $profile['good_for']);
        $this->assertSame([], $profile['verified_documents']);
    }

    public function test_display_name_and_tags_are_shown(): void
    {
        $guide = $this->guide();
        $guide->information->update(['display_name' => 'Lars from Elbe Guiding']);
        GuideTag::create(['user_id' => $guide->id, 'kind' => GuideTag::KIND_SPECIALTY, 'value' => 'Zander jigging', 'is_custom' => true, 'position' => 0]);
        GuideTag::create(['user_id' => $guide->id, 'kind' => GuideTag::KIND_GOOD_FOR, 'value' => 'Wheelchair friendly', 'is_custom' => true, 'position' => 0]);

        $profile = app(GuidePublicProfilePresenter::class)->present($guide->fresh(['information']));

        $this->assertSame('Lars from Elbe Guiding', $profile['name']);
        $this->assertSame(['Zander jigging'], $profile['specialties']);
        $this->assertSame(['Wheelchair friendly'], $profile['good_for']);
    }

    public function test_only_verified_unexpired_documents_are_listed(): void
    {
        $guide = $this->guide();
        $this->certificate($guide, 'boat_licence', CertificateStatus::Verified);
        $this->certificate($guide, 'first_aid', CertificateStatus::Verified, now()->subDay()->toDateString());
        $this->certificate($guide, 'liability_insurance', CertificateStatus::InReview);

        $profile = app(GuidePublicProfilePresenter::class)->present($guide);

        $this->assertSame([__('guide_area.certificates.types.boat_licence')], $profile['verified_documents']);
    }
}
