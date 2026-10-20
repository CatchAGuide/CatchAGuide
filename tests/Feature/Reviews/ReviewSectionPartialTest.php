<?php

namespace Tests\Feature\Reviews;

use App\Models\Review;
use App\Models\User;
use App\Models\VacationReview;
use App\Presenters\Reviews\ReviewSectionPresenter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Shared product page review partials (partials/reviews/*) fed by ReviewSectionPresenter.
 */
class ReviewSectionPartialTest extends TestCase
{
    private const VIEW = 'partials.reviews.mobile';

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
    }

    private function review(array $overrides = []): Review
    {
        $comment = $overrides['comment'] ?? 'Great day on the water.';

        if ($comment !== null && $comment !== '') {
            // translate() would call Google Translate on a cache miss.
            Cache::put(translation_cache_key($comment, 'en'), $comment);
        }

        $review = new Review(array_merge([
            'comment' => $comment,
            'overall_score' => 10,
            'guide_score' => 10,
            'region_water_score' => 9,
            'grandtotal_score' => 9.7,
            'is_automatic' => false,
        ], $overrides));

        $review->created_at = Carbon::parse('2023-10-26');
        $review->setRelation('user', new User(['firstname' => $overrides['firstname'] ?? 'Niklas']));
        $review->setRelation('booking', null);

        return $review;
    }

    /**
     * @param  Collection<int, Review>  $reviews
     */
    private function section(Collection $reviews): array
    {
        return app(ReviewSectionPresenter::class)->tour($reviews, $reviews->count(), [
            'grandtotal' => 9.7,
            'overall' => 10,
            'guide' => 10,
            'region_water' => 4.4,
        ]);
    }

    private function render(Collection $reviews, string $view = self::VIEW): string
    {
        return View::make($view, ['section' => $this->section($reviews)])->render();
    }

    public function test_renders_score_band_metrics_and_first_comment(): void
    {
        $html = $this->render(collect([$this->review(['comment' => 'Hugely instructive day.'])]));

        $this->assertStringContainsString('tour-reviews-m__hero', $html);
        $this->assertStringContainsString('9,7', $html);
        $this->assertStringContainsString('Excellent', $html);
        $this->assertStringContainsString('Based on 1 verified review', $html);
        $this->assertStringContainsString('Real reviews from real guests', $html);

        foreach (['Overall', 'Guide', 'Region &amp; Water'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->assertStringContainsString('Guest comments', $html);
        $this->assertStringContainsString('NIKLAS', strtoupper($html));
        $this->assertStringContainsString('Oct 26, 2023', $html);
        $this->assertStringContainsString('&ldquo;Hugely instructive day.&rdquo;', $html);
        $this->assertStringContainsString('9.7<small>/10</small>', $html);
    }

    public function test_metric_ticks_are_filled_from_the_rounded_score(): void
    {
        $html = $this->render(collect([$this->review()]));

        // Overall 10 + Guide 10 fill all ten ticks; Region & Water 4.4 fills four.
        $this->assertSame(10 + 10 + 4, substr_count($html, '<i class="is-on">'));
        $this->assertSame(0 + 0 + 6, substr_count($html, '<i class="">'));
    }

    public function test_single_review_has_no_arrows_or_dots(): void
    {
        $html = $this->render(collect([$this->review()]));

        $this->assertStringNotContainsString('class="tour-reviews-m__arrow"', $html);
        $this->assertStringNotContainsString('class="tour-reviews-m__dot"', $html);
    }

    public function test_few_reviews_use_dots_and_arrows(): void
    {
        $html = $this->render(collect([$this->review(), $this->review(['firstname' => 'Marie']), $this->review(['firstname' => 'Jonas'])]));

        $this->assertSame(2, substr_count($html, 'class="tour-reviews-m__arrow"'));
        $this->assertSame(3, substr_count($html, 'class="tour-reviews-m__dot"'));
        $this->assertStringNotContainsString('class="tour-reviews-m__counter"', $html);
        $this->assertStringContainsString('Show comment 3', $html);
    }

    public function test_many_reviews_swap_dots_for_a_counter(): void
    {
        $reviews = collect(range(1, 9))->map(fn () => $this->review());

        $html = $this->render($reviews);

        $this->assertStringNotContainsString('class="tour-reviews-m__dot"', $html);
        $this->assertStringContainsString('class="tour-reviews-m__counter"', $html);
        $this->assertStringContainsString('1 / 9', $html);
        $this->assertStringContainsString('Based on 9 verified reviews', $html);
    }

    public function test_review_without_comment_skips_the_text_and_toggle(): void
    {
        $html = $this->render(collect([$this->review(['comment' => null])]));

        $this->assertStringContainsString('tour-reviews-m__slide', $html);
        $this->assertStringNotContainsString('class="tour-reviews-m__text"', $html);
        $this->assertStringNotContainsString('class="tour-reviews-m__more"', $html);
    }

    public function test_automatic_reviews_show_the_badge(): void
    {
        $html = $this->render(collect([$this->review(['is_automatic' => true])]));

        $this->assertStringContainsString('Automatic review by Catch A Guide', $html);
    }

    public function test_new_copy_keys_exist_in_english_and_german(): void
    {
        foreach (['en', 'de'] as $locale) {
            foreach (['reviews_based_on', 'guest_comments', 'previous_comment', 'next_comment', 'show_comment'] as $key) {
                $this->assertTrue(Lang::has("guidings.{$key}", $locale), "Missing guidings.{$key} for {$locale}");
            }
        }

        $this->assertSame(
            '18 verifizierte Bewertungen',
            trans_choice('guidings.reviews_based_on', 18, ['count' => 18], 'de')
        );

        foreach (['en', 'de'] as $locale) {
            $this->assertTrue(Lang::has('vacations.reviews_disclaimer', $locale), "Missing vacations.reviews_disclaimer for {$locale}");
        }
    }

    public function test_desktop_section_renders_score_ring_bars_and_comment_rail(): void
    {
        $html = $this->render(collect([
            $this->review(['comment' => 'Hugely instructive day.']),
            $this->review(['firstname' => 'Marie', 'is_automatic' => true]),
        ]), 'partials.reviews.section');

        $this->assertStringContainsString('class="listing-reviews"', $html);
        $this->assertStringContainsString('listing-reviews__ring', $html);
        $this->assertStringContainsString('Excellent', $html);
        $this->assertStringContainsString('Based on 2 verified reviews', $html);
        $this->assertStringContainsString('id="ratings-container-mobile"', $html);
        // Desktop and mobile block each fill 10 + 10 + 4 ticks.
        $this->assertSame(2 * (10 + 10 + 4), substr_count($html, '<i class="is-on">'));
        $this->assertSame(2, substr_count($html, 'class="listing-reviews__item"'));
        $this->assertSame(2, substr_count($html, 'class="listing-reviews__arrow"'));
        $this->assertStringContainsString('&ldquo;Hugely instructive day.&rdquo;', $html);
        $this->assertStringContainsString('Automatic review by Catch A Guide', $html);
        $this->assertStringContainsString(e(__('guidings.Real_experiences_description')), $html);
    }

    public function test_section_rail_id_can_be_set_for_existing_anchors(): void
    {
        $html = View::make('partials.reviews.section', [
            'section' => $this->section(collect([$this->review()])),
            'railId' => 'ratings-container',
        ])->render();

        $this->assertStringContainsString('id="ratings-container"', $html);
        $this->assertStringNotContainsString('class="listing-reviews__arrow"', $html);
    }

    public function test_no_section_renders_nothing(): void
    {
        $this->assertSame('', trim(View::make('partials.reviews.section', ['section' => null])->render()));
        $this->assertNull(app(ReviewSectionPresenter::class)->vacation(null));
        $this->assertNull(app(ReviewSectionPresenter::class)->tour(collect(), 0, ['grandtotal' => 0, 'overall' => 0, 'guide' => 0, 'region_water' => 0]));
    }

    public function test_vacation_section_uses_camp_trip_categories_and_guest_first_name(): void
    {
        $review = new VacationReview([
            'guest_name' => 'Lea',
            'overall_score' => 9,
            'accommodation_score' => 8,
            'fishing_score' => 10,
            'value_score' => 7,
            'comment' => 'Super Hütte am See.',
            'language' => 'en',
        ]);
        $review->grandtotal_score = 8.5;
        $review->created_at = Carbon::parse('2026-02-28');

        $section = app(ReviewSectionPresenter::class)->vacation([
            'count' => 1,
            'averages' => ['grandtotal' => 8.5, 'overall' => 9, 'accommodation' => 8, 'fishing' => 10, 'value' => 7],
            'reviews' => collect([$review]),
        ]);

        $this->assertSame(['Overall', 'Accommodation', 'Fishing & region', 'Value for money'], array_column($section['categories'], 'label'));
        $this->assertSame(__('vacations.reviews_disclaimer'), $section['disclaimer']);
        $this->assertSame(__('guidings.Very_Good'), $section['label']);
        $this->assertSame([
            'name' => 'Lea',
            'initial' => 'L',
            'date' => 'Feb 28, 2026',
            'score' => 8.5,
            'text' => 'Super Hütte am See.',
            'automatic' => false,
        ], $section['reviews'][0]);
    }
}
