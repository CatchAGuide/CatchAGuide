<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Domain\Vacation\ViewModels\VacationPillarIndexViewModel;
use App\Models\Country;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class VacationPillarIndexViewModelTest extends TestCase
{
    public function test_country_page_uses_cms_title_intro_content_and_faq_title(): void
    {
        $destination = new Country([
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
        $destination = new Country([
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
}
