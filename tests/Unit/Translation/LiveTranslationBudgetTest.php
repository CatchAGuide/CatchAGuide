<?php

namespace Tests\Unit\Translation;

use App\Jobs\TranslateStringJob;
use App\Services\Camp\CampCacheService;
use App\Services\Queue\QueueRecoveryService;
use App\Services\SpecialOffer\SpecialOfferCacheService;
use App\Services\Translation\FreeGoogleTranslateClient;
use App\Services\Translation\LiveTranslationBudget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class LiveTranslationBudgetTest extends TestCase
{
    /** @var list<array{text: string, locale: string, options: array}> */
    private array $liveCalls = [];

    private bool $failLiveCalls = false;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Queue::fake();
        config(['services.translation.live_budget' => [
            'max_calls' => 2,
            'max_ms' => 2000,
            'timeout' => 3,
            'connect_timeout' => 2,
        ]]);

        $this->app->instance(FreeGoogleTranslateClient::class, new class($this) extends FreeGoogleTranslateClient
        {
            public function __construct(private LiveTranslationBudgetTest $test) {}

            public function translate(string $text, string $locale, array $httpOptions): string
            {
                return $this->test->recordLiveCall($text, $locale, $httpOptions);
            }
        });
    }

    public function recordLiveCall(string $text, string $locale, array $options): string
    {
        $this->liveCalls[] = ['text' => $text, 'locale' => $locale, 'options' => $options];
        if ($this->failLiveCalls) {
            throw new RuntimeException('endpoint down');
        }

        return "[{$locale}] {$text}";
    }

    public function test_a_web_request_makes_at_most_the_budgeted_live_calls_and_queues_the_rest(): void
    {
        $this->app->make(LiveTranslationBudget::class)->enforce();

        $results = array_map(fn (string $text) => translate($text, 'de'), ['Hecht', 'Zander', 'Barsch', 'Wels']);

        $this->assertSame(['[de] Hecht', '[de] Zander', 'Barsch', 'Wels'], $results);
        $this->assertCount(2, $this->liveCalls);
        $this->assertSame(['timeout' => 3, 'connect_timeout' => 2], $this->liveCalls[0]['options']);
        Queue::assertPushed(TranslateStringJob::class, 2);
        Queue::assertPushed(TranslateStringJob::class, fn (TranslateStringJob $job) => $job->text === 'Barsch'
            && $job->locale === 'de'
            && $job->connection === 'database'
            && $job->queue === TranslateStringJob::QUEUE);
        // The untranslated fallback is not cached, so the job's result is used on the next view.
        $this->assertFalse(Cache::has(translation_cache_key('Barsch', 'de')));
    }

    public function test_a_later_request_leaves_queued_strings_to_the_job(): void
    {
        $this->app->make(LiveTranslationBudget::class)->enforce();
        TranslateStringJob::dispatchOnce('Barsch', 'de');

        $this->assertSame('Barsch', translate('Barsch', 'de'));
        $this->assertSame('[de] Hecht', translate('Hecht', 'de'));
        $this->assertSame(['Hecht'], array_column($this->liveCalls, 'text'));
    }

    public function test_the_job_still_translates_a_queued_string(): void
    {
        TranslateStringJob::dispatchOnce('Barsch', 'de');

        (new TranslateStringJob('Barsch', 'de'))->handle();

        $this->assertSame('[de] Barsch', Cache::get(translation_cache_key('Barsch', 'de')));
    }

    public function test_cached_strings_do_not_use_the_budget(): void
    {
        $this->app->make(LiveTranslationBudget::class)->enforce();
        Cache::forever(translation_cache_key('Hecht', 'de'), 'Hecht (cached)');
        Cache::forever(translation_cache_key('Zander', 'de'), 'Zander (cached)');

        $this->assertSame('Hecht (cached)', translate('Hecht', 'de'));
        $this->assertSame('Zander (cached)', translate('Zander', 'de'));
        $this->assertSame('[de] Barsch', translate('Barsch', 'de'));
        $this->assertCount(1, $this->liveCalls);
    }

    public function test_failed_calls_count_against_the_budget(): void
    {
        $this->app->make(LiveTranslationBudget::class)->enforce();
        $this->failLiveCalls = true;

        foreach (['Hecht', 'Zander', 'Barsch'] as $text) {
            $this->assertSame($text, translate($text, 'de'));
        }

        $this->assertCount(2, $this->liveCalls);
        Queue::assertPushed(TranslateStringJob::class, 1);
    }

    public function test_the_time_budget_stops_live_calls_too(): void
    {
        $budget = $this->app->make(LiveTranslationBudget::class)->enforce();
        $budget->record(2500);

        $this->assertSame('Hecht', translate('Hecht', 'de'));
        $this->assertCount(0, $this->liveCalls);
        Queue::assertPushed(TranslateStringJob::class, 1);
    }

    public function test_console_and_queue_runs_are_not_limited(): void
    {
        foreach (['Hecht', 'Zander', 'Barsch', 'Wels'] as $text) {
            translate($text, 'de');
        }

        $this->assertCount(4, $this->liveCalls);
        $this->assertSame(['timeout' => 10, 'connect_timeout' => 5], $this->liveCalls[0]['options']);
        Queue::assertNothingPushed();
    }

    public function test_a_string_is_queued_only_once(): void
    {
        TranslateStringJob::dispatchOnce('Hecht', 'de');
        TranslateStringJob::dispatchOnce('Hecht', 'de');
        Cache::forever(translation_cache_key('Zander', 'de'), 'Zander');
        TranslateStringJob::dispatchOnce('Zander', 'de');

        Queue::assertPushed(TranslateStringJob::class, 1);
    }

    public function test_the_job_fills_the_translation_cache(): void
    {
        (new TranslateStringJob('Hecht', 'en'))->handle();

        $this->assertSame('[en] Hecht', Cache::get(translation_cache_key('Hecht', 'en')));
    }

    public function test_the_job_waits_while_the_circuit_breaker_is_open(): void
    {
        Cache::put('translate_circuit:open_until', now()->addMinutes(5)->timestamp, 300);
        $job = (new TranslateStringJob('Hecht', 'en'))->withFakeQueueInteractions();

        $job->handle();

        $job->assertReleased(300);
        $this->assertCount(0, $this->liveCalls);
    }

    public function test_the_worker_drains_booking_emails_before_translations(): void
    {
        $this->assertStringContainsString('--queue=default,translations ', QueueRecoveryService::WORKER_COMMAND);
        $this->assertSame('translations', TranslateStringJob::QUEUE);
    }

    public function test_creating_a_camp_or_special_offer_no_longer_wipes_the_whole_cache(): void
    {
        Cache::forever(translation_cache_key('Hecht', 'de'), 'Hecht');

        $this->app->make(CampCacheService::class)->clearAllCaches();
        $this->app->make(SpecialOfferCacheService::class)->clearAllCaches();

        $this->assertSame('Hecht', Cache::get(translation_cache_key('Hecht', 'de')));
    }
}
