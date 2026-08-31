<?php

namespace App\Services\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KnownCrawlerClassifier
{
    /**
     * @param  Closure(string): string|null  $reverseLookup
     * @param  Closure(string): string|null  $forwardLookup
     */
    public function __construct(
        private readonly ?Closure $reverseLookup = null,
        private readonly ?Closure $forwardLookup = null,
    ) {}

    public function classify(Request $request): CrawlerClassification
    {
        if (! config('ddos.crawlers.enabled', true)) {
            return new CrawlerClassification(CrawlerLane::User);
        }

        $userAgent = (string) $request->userAgent();
        if ($userAgent === '') {
            return new CrawlerClassification(CrawlerLane::User);
        }

        $seoMatch = $this->matchGroup($userAgent, config('ddos.crawlers.seo', []));
        if ($seoMatch !== null) {
            return new CrawlerClassification(CrawlerLane::SeoCrawler, $seoMatch, true);
        }

        $engineMatch = $this->matchGroup($userAgent, config('ddos.crawlers.search_engines', []));
        if ($engineMatch === null) {
            return new CrawlerClassification(CrawlerLane::User);
        }

        $ip = (string) $request->ip();
        if ($this->shouldSkipDnsVerification($ip, $engineMatch)) {
            return new CrawlerClassification(CrawlerLane::SearchEngine, $engineMatch, true);
        }

        if ($this->verifyReverseDns($ip, $engineMatch)) {
            return new CrawlerClassification(CrawlerLane::SearchEngine, $engineMatch, true);
        }

        return new CrawlerClassification(CrawlerLane::SpoofedCrawler, $engineMatch, false);
    }

    public function limitsFor(CrawlerClassification $classification, array $fallbackLimits): array
    {
        $laneLimits = config('ddos.crawlers.lanes.'.$classification->lane->value);

        return is_array($laneLimits) ? $laneLimits : $fallbackLimits;
    }

    /**
     * @param  array<string, array{ua?: list<string>, rdns_suffixes?: list<string>}>  $group
     */
    private function matchGroup(string $userAgent, array $group): ?string
    {
        foreach ($group as $name => $rules) {
            foreach ($rules['ua'] ?? [] as $needle) {
                if ($needle !== '' && stripos($userAgent, $needle) !== false) {
                    return $name;
                }
            }
        }

        return null;
    }

    private function shouldSkipDnsVerification(string $ip, string $engineName): bool
    {
        if (! config('ddos.crawlers.verify_dns', true)) {
            return true;
        }

        $suffixes = config("ddos.crawlers.search_engines.{$engineName}.rdns_suffixes", []);
        if ($suffixes === []) {
            return true;
        }

        return $this->isPrivateOrReservedIp($ip);
    }

    private function verifyReverseDns(string $ip, string $engineName): bool
    {
        $cacheKey = 'ddos_crawler_rdns_'.hash('sha256', $engineName.'|'.$ip);
        $ttl = (int) config('ddos.crawlers.dns_cache_seconds', 86400);

        return (bool) Cache::remember($cacheKey, $ttl, function () use ($ip, $engineName) {
            $reverse = $this->reverseLookup ?? static fn (string $value): string => @gethostbyaddr($value) ?: $value;
            $host = strtolower((string) $reverse($ip));
            if ($host === '' || $host === strtolower($ip)) {
                return false;
            }

            $suffixes = config("ddos.crawlers.search_engines.{$engineName}.rdns_suffixes", []);
            $matchesSuffix = false;
            foreach ($suffixes as $suffix) {
                $suffix = strtolower((string) $suffix);
                if ($suffix !== '' && str_ends_with($host, $suffix)) {
                    $matchesSuffix = true;
                    break;
                }
            }

            if (! $matchesSuffix) {
                return false;
            }

            $forward = $this->forwardLookup ?? static fn (string $value): string => @gethostbyname($value) ?: '';

            return $forward($host) === $ip;
        });
    }

    private function isPrivateOrReservedIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
