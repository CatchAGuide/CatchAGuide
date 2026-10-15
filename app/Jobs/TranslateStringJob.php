<?php

namespace App\Jobs;

use App\Services\Translation\TranslationCircuitBreaker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

/**
 * Fills the translate() cache for one string that a web request rendered untranslated because
 * its live-call budget was spent (see LiveTranslationBudget). Runs in the background, so the
 * page never waits on Google Translate.
 */
class TranslateStringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** One queued job per string and locale within this window, however many pages render it. */
    public const DEDUPE_SECONDS = 21600;

    /**
     * Its own queue, which the worker drains only after "default" (booking emails), so a
     * translation backlog never delays an email or trips queue:monitor-health.
     */
    public const QUEUE = 'translations';

    public int $tries = 3;

    public function __construct(
        public readonly string $text,
        public readonly string $locale,
    ) {
        // Same reason as TranslateListingJob: never run inline (tests force the sync driver),
        // or the request that queued it would wait on the live call after all.
        $this->connection = 'database';
        $this->queue = self::QUEUE;
    }

    /**
     * Queue the string unless it is already cached or already queued.
     */
    public static function dispatchOnce(string $text, string $locale): void
    {
        $cacheKey = translation_cache_key($text, $locale);

        if (Cache::has($cacheKey) || ! Cache::add(self::queuedKey($cacheKey), 1, self::DEDUPE_SECONDS)) {
            return;
        }

        static::dispatch($text, $locale);
    }

    /**
     * True while a job for this string is waiting, so web requests leave it to the job.
     */
    public static function isQueued(string $text, string $locale): bool
    {
        return Cache::has(self::queuedKey(translation_cache_key($text, $locale)));
    }

    private static function queuedKey(string $translationCacheKey): string
    {
        return 'translation_queued_'.$translationCacheKey;
    }

    public function handle(): void
    {
        if (TranslationCircuitBreaker::isOpen()) {
            $this->release(300);

            return;
        }

        // Jobs run in the console, where translate() has no live-call budget.
        translate($this->text, $this->locale);
    }
}
