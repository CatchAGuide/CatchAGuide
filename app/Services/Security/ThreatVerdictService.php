<?php

namespace App\Services\Security;

use Illuminate\Support\Str;

/**
 * Turns the aggregated facts about one flagged IP (see ThreatOverviewService::buildProfile)
 * into a verdict: is this a probable attack, an unlisted crawler, or a false positive?
 *
 * Rules run in priority order; the first match wins.
 */
class ThreatVerdictService
{
    private const EXPLOIT_TYPES = ['sqli', 'xss', 'path_traversal'];

    /**
     * @param  array<string, mixed>  $profile
     */
    public function assess(array $profile): ThreatVerdict
    {
        $types = $profile['types'] ?? [];
        $lanes = $profile['lanes'] ?? [];
        $events = (int) ($profile['events'] ?? 0);

        $exploits = array_values(array_intersect(array_keys($types), self::EXPLOIT_TYPES));
        if ($exploits !== []) {
            return $this->probableAttack($profile, $exploits, $events);
        }

        if (isset($lanes[CrawlerLane::SpoofedCrawler->value])) {
            return new ThreatVerdict('spoofed_crawler', 'high', array_values(array_filter([
                $this->line('claims_crawler', ['name' => implode(', ', $profile['crawlers'] ?? []) ?: '?']),
                $this->line('rdns_failed', ['host' => $profile['reverse_dns'] ?: '-']),
                $this->rateLimitLine($profile),
            ])));
        }

        if (isset($types['honeypot'])) {
            return new ThreatVerdict('bot_form_abuse', 'medium', [
                $this->line('honeypot_hit', ['count' => $types['honeypot']]),
            ]);
        }

        if (isset($types['rate_limit'])) {
            return $this->assessRateLimit($profile);
        }

        return new ThreatVerdict('needs_review', 'low', [
            $this->line('unknown_type', ['types' => implode(', ', array_keys($types)) ?: '-']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $profile
     * @param  list<string>  $exploits
     */
    private function probableAttack(array $profile, array $exploits, int $events): ThreatVerdict
    {
        $types = $profile['types'];
        $evidence = [];

        foreach ($exploits as $type) {
            $evidence[] = $this->line('exploit_type', ['type' => $type, 'count' => $types[$type]]);
        }

        foreach (array_slice($profile['exploit_matches'] ?? [], 0, 3) as $sample) {
            $evidence[] = $this->line('exploit_sample', ['sample' => Str::limit((string) $sample, 80)]);
        }

        if ($events >= 3) {
            $evidence[] = $this->line('repeated_probes', ['count' => $events]);
        }

        return new ThreatVerdict('probable_attack', 'high', $evidence);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function assessRateLimit(array $profile): ThreatVerdict
    {
        $evidence = array_filter([$this->rateLimitLine($profile)]);

        $crawlerEvidence = $this->crawlerEvidence($profile);
        if ($crawlerEvidence !== []) {
            return new ThreatVerdict('unlisted_crawler', 'info', array_values(array_merge($crawlerEvidence, $evidence)));
        }

        $scraperViolations = (int) config('ddos.assessment.scraper_violations', 10);
        $scoreThreshold = (int) config('ddos.threat_intelligence.threat_score_threshold', 70);
        $signs = [];

        if (($profile['max_violations'] ?? 0) >= $scraperViolations) {
            $signs[] = $this->line('many_violations', ['count' => $profile['max_violations']]);
        }
        if (($profile['automation_score'] ?? 0) >= 50) {
            $signs[] = $this->line('automation_signs', ['score' => $profile['automation_score']]);
        }
        if (($profile['max_score'] ?? 0) >= $scoreThreshold) {
            $signs[] = $this->line('high_score', ['score' => $profile['max_score']]);
        }

        if ($signs !== []) {
            return new ThreatVerdict('aggressive_scraper', 'medium', array_merge($evidence, $signs));
        }

        return new ThreatVerdict('likely_false_positive', 'low', array_values($evidence));
    }

    /**
     * Evidence that the IP is a crawler we simply have not listed yet (empty = looks like a normal client).
     *
     * @param  array<string, mixed>  $profile
     * @return list<array{key: string, params: array<string, scalar>}>
     */
    private function crawlerEvidence(array $profile): array
    {
        $evidence = [];
        $host = strtolower((string) ($profile['reverse_dns'] ?? ''));

        foreach ((array) config('ddos.assessment.crawler_rdns_suffixes', []) as $suffix) {
            if ($host !== '' && $suffix !== '' && str_ends_with($host, strtolower($suffix))) {
                $evidence[] = $this->line('crawler_rdns', ['host' => $host]);
                break;
            }
        }

        foreach ($profile['user_agents'] ?? [] as $userAgent) {
            if ($this->userAgentLooksLikeCrawler((string) $userAgent)) {
                $evidence[] = $this->line('crawler_ua', ['ua' => Str::limit((string) $userAgent, 80)]);
                break;
            }
        }

        if ($evidence === [] && ! empty($profile['is_robot'])) {
            $evidence[] = $this->line('crawler_robot_flag', []);
        }

        return $evidence;
    }

    private function userAgentLooksLikeCrawler(string $userAgent): bool
    {
        foreach ((array) config('ddos.assessment.crawler_ua_keywords', []) as $keyword) {
            if ($keyword !== '' && stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array{key: string, params: array<string, scalar>}|null
     */
    private function rateLimitLine(array $profile): ?array
    {
        $hits = (int) ($profile['types']['rate_limit'] ?? 0);

        return $hits > 0
            ? $this->line('rate_limit_hits', ['count' => $hits, 'violations' => (int) ($profile['max_violations'] ?? 0)])
            : null;
    }

    /**
     * @param  array<string, scalar>  $params
     * @return array{key: string, params: array<string, scalar>}
     */
    private function line(string $key, array $params): array
    {
        return ['key' => $key, 'params' => $params];
    }
}
