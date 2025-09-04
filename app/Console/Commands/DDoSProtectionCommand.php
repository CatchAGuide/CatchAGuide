<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DDoSProtectionService;
use App\Services\ThreatIntelligenceService;
use App\Services\HoneypotService;

class DDoSProtectionCommand extends Command
{
    protected $signature = 'ddos:manage {action} {context?} {identifier?} {--all : Apply to all identifiers} {--threat : Show threat intelligence} {--honeypot : Show honeypot stats}';

    protected $description = 'Manage DDoS protection settings and view statistics';

    public function handle()
    {
        $action = $this->argument('action');
        $context = $this->argument('context');
        $identifier = $this->argument('identifier');
        $all = $this->option('all');
        $showThreat = $this->option('threat');
        $showHoneypot = $this->option('honeypot');

        $protectionService = app(DDoSProtectionService::class);
        $threatService = app(ThreatIntelligenceService::class);
        $honeypotService = app(HoneypotService::class);

        switch ($action) {
            case 'stats':
                if ($all) {
                    $this->showStats($protectionService, null, null, true);
                } elseif ($context) {
                    $this->showStats($protectionService, $context, $identifier, false);
                } else {
                    $this->showStats($protectionService, null, null, true);
                }
                
                if ($showThreat) {
                    $this->showThreatIntelligence($threatService);
                }
                
                if ($showHoneypot) {
                    $this->showHoneypotStats($honeypotService);
                }

                break;
            case 'reset':
                $this->resetLimits($protectionService, $context, $identifier, $all);
                break;
            case 'block':
                $this->blockIdentifier($protectionService, $context, $identifier);
                break;
            case 'unblock':
                $this->unblockIdentifier($protectionService, $context, $identifier);
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->showHelp();
        }
    }

    private function showStats(DDoSProtectionService $service, ?string $context, ?string $identifier, bool $all)
    {
        $this->info("🛡️  DDoS Protection Statistics");
        $this->info("=============================");
        $this->newLine();

        if ($all) {
            $this->info("Showing statistics for all contexts...");
            $contexts = ['search', 'livewire', 'checkout', 'gemini', 'general'];
            
            foreach ($contexts as $ctx) {
                $this->showContextStats($service, $ctx);
            }
        } elseif ($identifier) {
            $this->showIdentifierStats($service, $context, $identifier);
        } else {
            $this->showContextStats($service, $context);
        }
    }

    private function showContextStats(DDoSProtectionService $service, ?string $context)
    {
        if ($context) {
            $this->info("📊 Context: {$context}");
            $this->line("Rate limits:");
            
            $limits = [
                'search' => ['minute' => 20, 'hour' => 200, 'day' => 1000],
                'livewire' => ['minute' => 30, 'hour' => 300, 'day' => 1500],
                'checkout' => ['minute' => 10, 'hour' => 50, 'day' => 200],
                'gemini' => ['minute' => 10, 'hour' => 100, 'day' => 500],
                'general' => ['minute' => 60, 'hour' => 1000, 'day' => 5000]
            ];

            if (isset($limits[$context])) {
                foreach ($limits[$context] as $window => $limit) {
                    $this->line("  - {$window}: {$limit} requests");
                }
            }
        }
        
        $this->newLine();
    }

    private function showIdentifierStats(DDoSProtectionService $service, string $context, string $identifier)
    {
        $this->info("👤 Identifier: {$identifier}");
        $this->info("📊 Context: {$context}");
        $this->newLine();

        $stats = $service->getUsageStats($identifier, $context);
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Minute Usage', $stats['minute_usage'] ?? 0],
                ['Hour Usage', $stats['hour_usage'] ?? 0],
                ['Day Usage', $stats['day_usage'] ?? 0],
                ['Violations', $stats['violations'] ?? 0],
                ['Is Blocked', $stats['is_blocked'] ? 'Yes' : 'No'],
                ['Retry After', $stats['retry_after'] ?? 'N/A']
            ]
        );
    }

    private function resetLimits(DDoSProtectionService $service, string $context, ?string $identifier, bool $all)
    {
        if ($all) {
            $this->info("Resetting limits for all contexts...");
            $contexts = ['search', 'livewire', 'checkout', 'gemini', 'general'];
            
            foreach ($contexts as $ctx) {
                if ($identifier) {
                    $service->resetSecurityLimits($identifier, $ctx);
                    $this->line("✅ Reset limits for {$identifier} in {$ctx}");
                } else {
                    $this->error("Cannot reset all contexts without specifying an identifier");
                    return;
                }
            }
        } elseif ($identifier) {
            $service->resetSecurityLimits($identifier, $context);
            $this->info("✅ Reset limits for {$identifier} in {$context}");
        } else {
            $this->error("Please specify an identifier or use --all flag");
        }
    }

    private function blockIdentifier(DDoSProtectionService $service, string $context, string $identifier)
    {
        if (!$identifier) {
            $this->error("Please specify an identifier to block");
            return;
        }

        // This would require extending the service to support manual blocking
        $this->info("🚫 Manual blocking not yet implemented");
        $this->line("Use the web interface or modify the service to add manual blocking functionality");
    }

    private function unblockIdentifier(DDoSProtectionService $service, string $context, string $identifier)
    {
        if (!$identifier) {
            $this->error("Please specify an identifier to unblock");
            return;
        }

        $service->resetSecurityLimits($identifier, $context);
        $this->info("✅ Unblocked {$identifier} in {$context}");
    }

    private function showHelp()
    {
        $this->newLine();
        $this->info("Available actions:");
        $this->line("  stats    - Show protection statistics");
        $this->line("  reset    - Reset rate limits for identifier");
        $this->line("  block    - Block an identifier (not implemented)");
        $this->line("  unblock  - Unblock an identifier");
        $this->newLine();
        $this->info("Available contexts:");
        $this->line("  search, livewire, checkout, gemini, general");
        $this->newLine();
        $this->info("Examples:");
        $this->line("  php artisan ddos:manage stats search");
        $this->line("  php artisan ddos:manage stats livewire ip_192.168.1.1");
        $this->line("  php artisan ddos:manage reset checkout user_123");
        $this->line("  php artisan ddos:manage stats --all");
        $this->line("  php artisan ddos:manage stats --threat --honeypot");
    }

    private function showThreatIntelligence(ThreatIntelligenceService $threatService)
    {
        $this->newLine();
        $this->info("🔍 THREAT INTELLIGENCE ANALYSIS");
        $this->line("=====================================");
        
        try {
            // Get high threat incidents from logs
            $logFile = storage_path('logs/ddos-attacks.log');
            if (file_exists($logFile)) {
                $logContent = file_get_contents($logFile);
                $highThreatCount = substr_count($logContent, 'HIGH THREAT');
                $honeypotCount = substr_count($logContent, 'HONEYPOT TRIGGERED');
                
                $this->line("High Threat Incidents: {$highThreatCount}");
                $this->line("Honeypot Triggers: {$honeypotCount}");
            }
            
            // Get recent threat data from database
            $recentThreats = \DB::table('threat_intelligence')
                ->where('created_at', '>=', now()->subHours(24))
                ->orderBy('threat_score', 'desc')
                ->limit(10)
                ->get();
            
            if ($recentThreats->count() > 0) {
                $this->newLine();
                $this->info("Top Threats (Last 24h):");
                $this->table(
                    ['IP', 'Context', 'Threat Score', 'Time'],
                    $recentThreats->map(function ($threat) {
                        return [
                            $threat->ip,
                            $threat->context,
                            $threat->threat_score,
                            $threat->created_at
                        ];
                    })
                );
            }
            
        } catch (\Exception $e) {
            $this->error("Error retrieving threat intelligence: " . $e->getMessage());
        }
    }

    private function showHoneypotStats(HoneypotService $honeypotService)
    {
        $this->newLine();
        $this->info("🍯 HONEYPOT STATISTICS");
        $this->line("======================");
        
        try {
            $stats = $honeypotService->getHoneypotStats(24);
            
            if (!empty($stats)) {
                $this->line("Total Triggers (24h): " . ($stats['total_triggers'] ?? 0));
                $this->line("Unique IPs: " . ($stats['unique_ips'] ?? 0));
                $this->newLine();
                $this->info("Trigger Types:");
                $this->line("  Hidden Field: " . ($stats['hidden_field_triggers'] ?? 0));
                $this->line("  Time Trap: " . ($stats['time_trap_triggers'] ?? 0));
                $this->line("  JS Challenge: " . ($stats['js_challenge_triggers'] ?? 0));
                $this->line("  CSS Trap: " . ($stats['css_trap_triggers'] ?? 0));
                $this->line("  Endpoint Trap: " . ($stats['endpoint_trap_triggers'] ?? 0));
            } else {
                $this->line("No honeypot data available");
            }
            
        } catch (\Exception $e) {
            $this->error("Error retrieving honeypot stats: " . $e->getMessage());
        }
    }
}
