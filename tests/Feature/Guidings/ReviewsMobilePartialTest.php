<?php

namespace Tests\Feature\Guidings;

use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ReviewsMobilePartialTest extends TestCase
{
    private const VIEW = 'pages.guidings.partials.reviews-mobile';

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
    private function render(Collection $reviews, array $scores = []): string
    {
        return View::make(self::VIEW, array_merge([
            'reviews' => $reviews,
            'reviews_count' => $reviews->count(),
            'average_grandtotal_score' => 9.7,
            'average_overall_score' => 10,
            'average_guide_score' => 10,
            'average_region_water_score' => 4.4,
        ], $scores))->render();
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
        $this->assertStringContainsString('OCT 26, 2023', $html);
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
    }
}
