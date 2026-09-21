<?php

namespace App\Services\Security;

use App\Models\ThreatIntelligence;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Read-only view over the threat_intelligence table for the admin security panel:
 * groups flagged events per IP, attaches a verdict and any active cache block.
 */
class ThreatOverviewService
{
    /** Upper bound on rows parsed per request; each row carries a ~3 KB JSON blob. */
    public const MAX_EVENTS = 1000;

    public const MAX_IPS = 100;

    private const TIMELINE_LENGTH = 10;

    public function __construct(private readonly ThreatVerdictService $verdicts) {}

    /**
     * @return array{
     *     summary: array{events: int, ips: int, attacks: int, crawlers: int, blocked: int, truncated: bool},
     *     entries: list<array{profile: array<string, mixed>, verdict: ThreatVerdict, blocks: list<array{context: string, until: Carbon}>}>
     * }
     */
    public function overview(int $hours, ?string $ipPrefix = null): array
    {
        $rows = ThreatIntelligence::query()
            ->select(['id', 'ip', 'context', 'threat_score', 'threat_data', 'created_at'])
            ->where('created_at', '>=', now()->subHours($hours))
            ->when($ipPrefix, fn ($query) => $query->where('ip', 'like', addcslashes($ipPrefix, '%_\\').'%'))
            ->orderByDesc('created_at')
            ->limit(self::MAX_EVENTS)
            ->get();

        $entries = $rows->groupBy('ip')
            ->map(function (Collection $ipRows, string $ip) {
                $profile = $this->buildProfile($ip, $ipRows);

                return [
                    'profile' => $profile,
                    'verdict' => $this->verdicts->assess($profile),
                    'blocks' => $this->activeBlocks($ip, $profile['contexts']),
                ];
            })
            ->sortBy([
                fn ($a, $b) => $b['verdict']->severityRank() <=> $a['verdict']->severityRank(),
                fn ($a, $b) => $b['profile']['last_seen'] <=> $a['profile']['last_seen'],
            ])
            ->values();

        return [
            'summary' => [
                'events' => $rows->count(),
                'ips' => $entries->count(),
                'attacks' => $entries->filter(fn ($e) => $e['verdict']->isProbableAttack())->count(),
                'crawlers' => $entries->filter(fn ($e) => $e['verdict']->key === 'unlisted_crawler')->count(),
                'blocked' => $entries->filter(fn ($e) => $e['blocks'] !== [])->count(),
                'truncated' => $rows->count() >= self::MAX_EVENTS,
            ],
            'entries' => $entries->take(self::MAX_IPS)->all(),
        ];
    }

    /**
     * @param  Collection<int, ThreatIntelligence>  $rows  newest first
     * @return array<string, mixed>
     */
    private function buildProfile(string $ip, Collection $rows): array
    {
        $types = [];
        $lanes = [];
        $crawlers = [];
        $contexts = [];
        $endpoints = [];
        $userAgents = [];
        $exploitMatches = [];
        $maxViolations = 0;
        $reverseDns = null;
        $isRobot = false;
        $automationScore = 0;

        foreach ($rows as $row) {
            $data = $row->threat_data ?? [];
            $attack = $data['attack_data'] ?? [];

            $type = (string) ($attack['type'] ?? 'unknown');
            $types[$type] = ($types[$type] ?? 0) + 1;

            if (! empty($attack['lane'])) {
                $lanes[$attack['lane']] = ($lanes[$attack['lane']] ?? 0) + 1;
            }
            if (! empty($attack['crawler'])) {
                $crawlers[$attack['crawler']] = $attack['crawler'];
            }
            if (! empty($attack['matched'])) {
                $exploitMatches[] = (string) $attack['matched'];
            }

            $contexts[$row->context] = $row->context;
            $maxViolations = max($maxViolations, (int) ($attack['violations'] ?? 0));

            $path = parse_url((string) ($attack['url'] ?? ''), PHP_URL_PATH) ?: '/';
            $endpoints[$path] = ($endpoints[$path] ?? 0) + 1;

            $userAgent = (string) ($data['behavior']['user_agent'] ?? '');
            if ($userAgent !== '') {
                $userAgents[$userAgent] = $userAgent;
            }

            $host = $data['network']['reverse_dns'] ?? null;
            if ($reverseDns === null && is_string($host) && $host !== '' && $host !== $ip) {
                $reverseDns = $host;
            }

            $isRobot = $isRobot || ! empty($data['fingerprint']['is_robot']);
            $automationScore = max($automationScore, (int) ($data['behavior']['automation_signs']['confidence_score'] ?? 0));
        }

        arsort($endpoints);

        return [
            'ip' => $ip,
            'events' => $rows->count(),
            'first_seen' => $rows->last()->created_at,
            'last_seen' => $rows->first()->created_at,
            'types' => $types,
            'lanes' => $lanes,
            'crawlers' => array_values($crawlers),
            'contexts' => array_values($contexts),
            'endpoints' => array_slice($endpoints, 0, 5, true),
            'user_agents' => array_values($userAgents),
            'exploit_matches' => array_values(array_unique($exploitMatches)),
            'max_violations' => $maxViolations,
            'max_score' => (int) $rows->max('threat_score'),
            'reverse_dns' => $reverseDns,
            'is_robot' => $isRobot,
            'automation_score' => $automationScore,
            'timeline' => $rows->take(self::TIMELINE_LENGTH)->map(fn (ThreatIntelligence $row) => [
                'at' => $row->created_at,
                'type' => (string) ($row->threat_data['attack_data']['type'] ?? 'unknown'),
                'context' => $row->context,
                'url' => Str::limit((string) ($row->threat_data['attack_data']['url'] ?? ''), 120),
            ])->all(),
        ];
    }

    /**
     * Blocks are kept in cache by DDoSProtectionService::blockIdentifier().
     *
     * @param  list<string>  $contexts
     * @return list<array{context: string, until: Carbon}>
     */
    private function activeBlocks(string $ip, array $contexts): array
    {
        $blocks = [];

        foreach ($contexts as $context) {
            $block = Cache::get("{$context}_blocked_ip_{$ip}");

            if (is_array($block) && ($block['expires_at'] ?? 0) > time()) {
                $blocks[] = ['context' => $context, 'until' => Carbon::createFromTimestamp($block['expires_at'])];
            }
        }

        return $blocks;
    }
}
