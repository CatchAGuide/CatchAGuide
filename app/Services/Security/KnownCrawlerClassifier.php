<?php

namespace App\Services\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KnownCrawlerClassifier
{
    /**
     * @param  Closure(string): string|null  $reverseLookup
     * @param  Closure(string): (string|list<string>)|null  $forwardLookup
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

        $socialMatch = $this->matchGroup($userAgent, config('ddos.crawlers.social', []));
        if ($socialMatch !== null) {
            return new CrawlerClassification(CrawlerLane::Social, $socialMatch, true);
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

    /**
     * @return array<string, int>|null  null when the lane is not rate limited
     */
    public function limitsFor(CrawlerClassification $classification, array $fallbackLimits): ?array
    {
        if (! $classification->lane->isRateLimited()) {
            return null;
        }

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

    /**
     * A host outside the engine's domains is a real spoof and is remembered for a
     * day. A failed lookup may just be a DNS hiccup, so it is retried soon — caching
     * it for a day used to push a real Googlebot IP into the spoofed lane until the
     * next day, where it collected violations and got blocked.
     */
    private function verifyReverseDns(string $ip, string $engineName): bool
    {
        $cacheKey = 'ddos_crawler_rdns_'.hash('sha256', $engineName.'|'.$ip);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (bool) $cached;
        }

        [$verified, $definitive] = $this->lookup($ip, $engineName);

        $ttl = $definitive
            ? (int) config('ddos.crawlers.dns_cache_seconds', 86400)
            : (int) config('ddos.crawlers.dns_failure_cache_seconds', 900);
        Cache::put($cacheKey, $verified, $ttl);

        return $verified;
    }

    /**
     * @return array{0: bool, 1: bool}  [verified, definitive]
     */
    private function lookup(string $ip, string $engineName): array
    {
        $reverse = $this->reverseLookup ?? static fn (string $value): string => @gethostbyaddr($value) ?: $value;
        $host = strtolower((string) $reverse($ip));
        if ($host === '' || $host === strtolower($ip)) {
            return [false, false];
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
            return [false, true];
        }

        $forward = $this->forwardLookup ?? fn (string $value): array => $this->resolveHost($value);
        $packedIp = @inet_pton($ip);
        foreach ((array) $forward($host) as $address) {
            if ($packedIp !== false && @inet_pton((string) $address) === $packedIp) {
                return [true, true];
            }
        }

        return [false, false];
    }

    /**
     * All A and AAAA records — gethostbyname() returns one IPv4 address only, so an
     * IPv6 crawler or a host with several records could never be confirmed.
     *
     * @return list<string>
     */
    private function resolveHost(string $host): array
    {
        $addresses = @gethostbynamel($host) ?: [];
        foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            if (isset($record['ipv6'])) {
                $addresses[] = $record['ipv6'];
            }
        }

        return $addresses;
    }

    private function isPrivateOrReservedIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
