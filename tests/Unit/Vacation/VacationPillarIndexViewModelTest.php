<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Domain\Vacation\ViewModels\VacationPillarIndexViewModel;
use App\Models\CategoryEntity;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class VacationPillarIndexViewModelTest extends TestCase
{
    public function test_country_page_uses_cms_title_intro_content_and_faq_title(): void
    {
        $destination = new CategoryEntity([
            'type' => 'country',
            'slug' => 'spanien',
            'name' => 'Spain',
        ]);
        $destination->overlayScopedTranslation(new Language([
            'title' => 'CMS Trips Spain Title',
            'sub_title' => 'CMS Trips Spain Subtitle',
            'introduction' => '<p>CMS trips intro</p>',
            'content' => '<p>CMS trips body</p>',
            'faq_title' => 'CMS Trips FAQ',
        ]));

        $vm = new VacationPillarIndexViewModel(
            pillar: VacationPillar::Trips,
            filter: VacationListingFilter::fromRequest([], 'spanien'),
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            countries: collect(),
            speciesOptions: collect(),
            accommodationTypeOptions: collect(),
            tripsTotal: 0,
            campsTotal: 0,
            faq: collect([(object) ['question' => 'Q?', 'answer' => 'A.']]),
            destination: $destination,
        );

        $this->assertTrue($vm->isCountryPage());
        $this->assertSame('CMS Trips Spain Title', $vm->pageTitle());
        $this->assertSame('CMS Trips Spain Subtitle', $vm->headerSubtitle());
        $this->assertSame('<p>CMS trips intro</p>', $vm->introductionHtml());
        $this->assertSame('<p>CMS trips body</p>', $vm->bodyContentHtml());
        $this->assertSame('CMS Trips FAQ', $vm->faqTitle());
    }

    public function test_country_page_falls_back_when_cms_title_empty(): void
    {
        $destination = new CategoryEntity([
            'type' => 'country',
            'slug' => 'spanien',
            'name' => 'Spain',
        ]);

        $vm = new VacationPillarIndexViewModel(
            pillar: VacationPillar::Camps,
            filter: VacationListingFilter::fromRequest([], 'spanien'),
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            countries: collect(),
            speciesOptions: collect(),
            accommodationTypeOptions: collect(),
            tripsTotal: 0,
            campsTotal: 0,
            faq: collect(),
            destination: $destination,
        );

        $this->assertSame(
            __(VacationPillar::Camps->countryTitleKey(), ['country' => translate('Spain')]),
            $vm->pageTitle(),
        );
        $this->assertSame(__('vacations.hub_faq_title'), $vm->faqTitle());
    }

    public function test_pillar_toggle_urls_keep_camp_facets_only_on_camps(): void
    {
        $destination = new CategoryEntity([
            'type' => 'country',
            'slug' => 'sweden',
            'name' => 'Sweden',
        ]);

        $vm = new VacationPillarIndexViewModel(
            pillar: VacationPillar::Camps,
            filter: VacationListingFilter::fromRequest([
                'pillar' => 'camps',
                'accommodation_type' => '3',
                'has_guiding' => '1',
                'has_rental_boat' => '0',
                'sortby' => 'price-asc',
            ], 'sweden'),
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            countries: collect(),
            speciesOptions: collect(),
            accommodationTypeOptions: collect([['id' => 3, 'name' => 'Cabin']]),
            tripsTotal: 2,
            campsTotal: 4,
            faq: collect(),
            destination: $destination,
        );

        $urls = $vm->pillarToggleUrls();

        $this->assertStringContainsString('accommodation_type=3', $urls['camps']);
        $this->assertStringContainsString('has_guiding=1', $urls['camps']);
        $this->assertStringContainsString('has_rental_boat=0', $urls['camps']);
        $this->assertStringContainsString('sortby=price-asc', $urls['camps']);
        $this->assertStringNotContainsString('accommodation_type=', $urls['all']);
        $this->assertStringNotContainsString('has_guiding=', $urls['trips']);
        $this->assertStringNotContainsString('has_rental_boat=', $urls['trips']);
        $this->assertStringContainsString('sortby=price-asc', $urls['trips']);
    }

    public function test_pillar_toggle_urls_keep_duration_only_on_trips(): void
    {
        $destination = new CategoryEntity([
            'type' => 'country',
            'slug' => 'sweden',
            'name' => 'Sweden',
        ]);

        $vm = new VacationPillarIndexViewModel(
            pillar: VacationPillar::Trips,
            filter: VacationListingFilter::fromRequest([
                'pillar' => 'trips',
                'duration' => '4-7',
                'sortby' => 'price-asc',
            ], 'sweden'),
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            countries: collect(),
            speciesOptions: collect(),
            accommodationTypeOptions: collect(),
            tripsTotal: 2,
            campsTotal: 4,
            faq: collect(),
            destination: $destination,
        );

        $urls = $vm->pillarToggleUrls();

        $this->assertStringContainsString('duration=4-7', $urls['trips']);
        $this->assertStringContainsString('sortby=price-asc', $urls['trips']);
        $this->assertStringNotContainsString('duration=', $urls['all']);
        $this->assertStringNotContainsString('duration=', $urls['camps']);
        $this->assertStringContainsString('sortby=price-asc', $urls['camps']);
    }
}
