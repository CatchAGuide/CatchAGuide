<?php

namespace App\Services\Security;

/**
 * Outcome of assessing one flagged IP. Text lives in resources/lang/{en,de}/admin.php
 * under admin.security.verdicts.{key} and admin.security.evidence.{evidence key}.
 */
final class ThreatVerdict
{
    private const SEVERITY_RANK = ['info' => 0, 'low' => 1, 'medium' => 2, 'high' => 3];

    /**
     * @param  'info'|'low'|'medium'|'high'  $severity
     * @param  list<array{key: string, params: array<string, scalar>}>  $evidence
     */
    public function __construct(
        public readonly string $key,
        public readonly string $severity,
        public readonly array $evidence = [],
    ) {}

    public function severityRank(): int
    {
        return self::SEVERITY_RANK[$this->severity] ?? 0;
    }

    public function isProbableAttack(): bool
    {
        return in_array($this->key, ['probable_attack', 'spoofed_crawler'], true);
    }
}
