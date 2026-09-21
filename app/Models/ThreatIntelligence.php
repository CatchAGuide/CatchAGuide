<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One flagged request recorded by DDoSProtectionService (rate limit, exploit probe, honeypot).
 * Rows are purged by `threat-intelligence:cleanup` after ddos.threat_intelligence.retention_days.
 */
class ThreatIntelligence extends Model
{
    protected $table = 'threat_intelligence';

    protected $guarded = [];

    protected $casts = [
        'threat_data' => 'array',
        'threat_score' => 'integer',
    ];
}
