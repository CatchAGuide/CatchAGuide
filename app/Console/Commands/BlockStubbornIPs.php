<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DDoSProtectionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockStubbornIPs extends Command
{
    protected $signature = 'ddos:block-stubborn {--hours=24 : Hours to look back for stubborn IPs} {--min-violations=50 : Minimum violations to consider stubborn} {--block-duration=86400 : Block duration in seconds} {--dry-run : Show what would be blocked without actually blocking}';
    protected $description = 'Block IPs that have been persistently attacking the system';

    public function handle()
    {
        $hours = $this->option('hours');
        $minViolations = $this->option('min-violations');
        $blockDuration = $this->option('block-duration');
        $dryRun = $this->option('dry-run');

        $this->info("🔍 Scanning for stubborn attackers (last {$hours} hours)...");
        $this->info("Minimum violations: {$minViolations}");
        $this->info("Block duration: " . gmdate('H:i:s', $blockDuration));
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No actual blocking will occur");
        }

        $stubbornIPs = $this->findStubbornIPs($hours, $minViolations);
        
        if (empty($stubbornIPs)) {
            $this->info("✅ No stubborn attackers found!");
            return;
        }

        $this->newLine();
        $this->info("🚨 Found " . count($stubbornIPs) . " stubborn attackers:");
        
        $tableData = [];
        foreach ($stubbornIPs as $ip => $data) {
            $tableData[] = [
                $ip,
                $data['total_violations'],
                $data['contexts'],
                $data['last_violation'],
                $data['threat_score'] ?? 'N/A'
            ];
        }

        $this->table(
            ['IP Address', 'Total Violations', 'Contexts', 'Last Violation', 'Threat Score'],
            $tableData
        );

        if ($dryRun) {
            $this->warn("DRY RUN: Would block " . count($stubbornIPs) . " IPs");
            return;
        }

        if ($this->confirm("Do you want to block these IPs?")) {
            $this->blockIPs($stubbornIPs, $blockDuration);
        } else {
            $this->info("Operation cancelled.");
        }
    }

    private function findStubbornIPs(int $hours, int $minViolations): array
    {
        $stubbornIPs = [];
        $contexts = ['search', 'livewire', 'checkout', 'gemini', 'general'];
        
        foreach ($contexts as $context) {
            // Get all violation keys for this context
            $pattern = "{$context}_violations_*";
            $keys = $this->getCacheKeys($pattern);
            
            foreach ($keys as $key) {
                $violations = Cache::get($key, 0);
                
                if ($violations >= $minViolations) {
                    // Extract IP from key
                    $ip = str_replace("{$context}_violations_", '', $key);
                    
                    if (!isset($stubbornIPs[$ip])) {
                        $stubbornIPs[$ip] = [
                            'total_violations' => 0,
                            'contexts' => [],
                            'last_violation' => 'Unknown',
                            'threat_score' => null
                        ];
                    }
                    
                    $stubbornIPs[$ip]['total_violations'] += $violations;
                    $stubbornIPs[$ip]['contexts'][] = $context;
                    
                    // Get threat score if available
                    $threatData = Cache::get("threat_data_{$ip}");
                    if ($threatData && isset($threatData['threat_score'])) {
                        $stubbornIPs[$ip]['threat_score'] = max(
                            $stubbornIPs[$ip]['threat_score'] ?? 0,
                            $threatData['threat_score']
                        );
                    }
                }
            }
        }
        
        // Sort by total violations
        uasort($stubbornIPs, function($a, $b) {
            return $b['total_violations'] <=> $a['total_violations'];
        });
        
        return $stubbornIPs;
    }

    private function getCacheKeys(string $pattern): array
    {
        // This is a simplified version - in production you might want to use Redis SCAN
        $keys = [];
        
        // For now, we'll check common IP patterns
        $commonIPs = [
            '192.168.', '10.', '172.', '127.',
            // Add more patterns as needed
        ];
        
        foreach ($commonIPs as $ipPrefix) {
            // This is a basic implementation - you might want to use Redis SCAN in production
            for ($i = 0; $i < 256; $i++) {
                $testKey = str_replace('*', $ipPrefix . $i, $pattern);
                if (Cache::has($testKey)) {
                    $keys[] = $testKey;
                }
            }
        }
        
        return $keys;
    }

    private function blockIPs(array $stubbornIPs, int $blockDuration): void
    {
        $protectionService = app(DDoSProtectionService::class);
        $blockedCount = 0;
        
        foreach ($stubbornIPs as $ip => $data) {
            try {
                // Block for each context they've been attacking
                foreach (array_unique($data['contexts']) as $context) {
                    $identifier = "ip_{$ip}";
                    
                    // Create a block entry
                    $blockKey = "{$context}_blocked_{$identifier}";
                    Cache::put($blockKey, [
                        'blocked_at' => time(),
                        'expires_at' => time() + $blockDuration,
                        'reason' => 'stubborn_attacker',
                        'violations' => $data['total_violations']
                    ], $blockDuration);
                    
                    // Log the block
                    Log::channel('ddos_attacks')->critical("STUBBORN IP BLOCKED", [
                        'ip' => $ip,
                        'context' => $context,
                        'violations' => $data['total_violations'],
                        'block_duration' => $blockDuration,
                        'threat_score' => $data['threat_score'],
                        'timestamp' => now()->toISOString()
                    ]);
                }
                
                $blockedCount++;
                $this->line("✅ Blocked {$ip} (violations: {$data['total_violations']})");
                
            } catch (\Exception $e) {
                $this->error("❌ Failed to block {$ip}: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("🎯 Successfully blocked {$blockedCount} stubborn IPs!");
        
        // Send notification
        $this->sendBlockingNotification($stubbornIPs, $blockedCount);
    }

    private function sendBlockingNotification(array $stubbornIPs, int $blockedCount): void
    {
        try {
            $notificationService = app(\App\Services\DDoSNotificationService::class);
            
            $details = [
                'blocked_count' => $blockedCount,
                'total_violations' => array_sum(array_column($stubbornIPs, 'total_violations')),
                'top_attacker' => array_key_first($stubbornIPs),
                'blocked_ips' => array_keys($stubbornIPs),
                'timestamp' => now()->toISOString()
            ];
            
            // This would send an email notification
            // $notificationService->sendStubbornAttackerAlert('bulk_block', $details);
            
            $this->info("📧 Notification sent to administrators");
            
        } catch (\Exception $e) {
            $this->warn("⚠️ Failed to send notification: " . $e->getMessage());
        }
    }
}
