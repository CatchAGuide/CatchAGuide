<?php

namespace App\Console\Commands;

use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Trip;
use App\Models\Vacation;
use App\Services\Translation\TranslationCircuitBreaker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Pre-translates listing titles/locations so live page renders never hit an
 * uncached translate() call — that live call (~10s per string, no cap on how
 * many run per page) is what turned the offers/guidings catalog pages into
 * full timeouts once Google Translate started rate-limiting the server.
 *
 * Safe to run repeatedly: translate() already skips anything cached, so a
 * quiet run (nothing new) costs zero API calls — only genuinely new/uncached
 * content triggers a live call, bounded by --limit per run.
 */
class WarmTranslationCache extends Command
{
    protected $signature = 'translations:warm
                            {--locales=en,de : Comma-separated locales to warm}
                            {--limit=100 : Max live Google Translate calls this run (already-cached strings don\'t count)}';

    protected $description = 'Pre-translate active listing titles/locations so live page renders never wait on Google Translate';

    private int $liveCalls = 0;

    public function handle(): int
    {
        $limit = max(0, (int) $this->option('limit'));
        $locales = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('locales')))));

        if (empty($locales) || $limit === 0) {
            $this->info('Nothing to warm (no locales or zero limit).');

            return self::SUCCESS;
        }

        if (TranslationCircuitBreaker::isOpen()) {
            $this->warn('Translation circuit breaker is open — Google Translate is currently failing. Skipping this run.');

            return self::SUCCESS;
        }

        $this->warmGuidings($locales, $limit);
        $this->warmTitleOnly(Vacation::query()->where('status', 1), $locales, $limit, 'vacations');
        $this->warmTitleOnly(Camp::query()->where('status', 'active'), $locales, $limit, 'camps');
        $this->warmTitleOnly(Trip::query()->where('status', 'active'), $locales, $limit, 'trips');

        $this->info("Warmed {$this->liveCalls} translation(s) this run.");

        return self::SUCCESS;
    }

    private function warmGuidings(array $locales, int $limit): void
    {
        if ($this->budgetExhausted($limit)) {
            return;
        }

        $this->line('Warming guidings...');

        Guiding::query()
            ->where('status', 1)
            ->select(['id', 'title', 'location'])
            ->chunkById(200, function ($guidings) use ($locales, $limit) {
                foreach ($guidings as $guiding) {
                    $this->warmField($guiding->title, $locales, $limit);
                    $this->warmField($guiding->location, $locales, $limit);

                    if ($this->budgetExhausted($limit)) {
                        return false;
                    }
                }
            });
    }

    private function warmTitleOnly($query, array $locales, int $limit, string $label): void
    {
        if ($this->budgetExhausted($limit)) {
            return;
        }

        $this->line("Warming {$label}...");

        $query->select(['id', 'title'])
            ->chunkById(200, function ($rows) use ($locales, $limit) {
                foreach ($rows as $row) {
                    $this->warmField($row->title, $locales, $limit);

                    if ($this->budgetExhausted($limit)) {
                        return false;
                    }
                }
            });
    }

    private function warmField(?string $value, array $locales, int $limit): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        foreach ($locales as $locale) {
            if ($this->budgetExhausted($limit)) {
                return;
            }

            $cacheKey = translation_cache_key($value, $locale);

            if (Cache::has($cacheKey)) {
                continue; // already warmed (or already attempted) — no API call
            }

            translate($value, $locale);
            $this->liveCalls++;

            // Courtesy delay between real calls only — mirrors the existing throttle in
            // GoogleTranslationService::batchTranslate(). Cache hits above never sleep.
            usleep(150000);
        }
    }

    private function budgetExhausted(int $limit): bool
    {
        return $this->liveCalls >= $limit || TranslationCircuitBreaker::isOpen();
    }
}
