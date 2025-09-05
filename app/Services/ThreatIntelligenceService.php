<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class ThreatIntelligenceService
{
    private Agent $agent;
    
    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Collect comprehensive threat intelligence data
     */
    public function collectThreatData(Request $request, string $context, array $attackData = []): array
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Basic network information
        $networkData = $this->analyzeNetworkData($ip, $request);
        
        // Browser and device fingerprinting
        $fingerprint = $this->generateFingerprint($request);
        
        // Behavioral analysis
        $behavioralData = $this->analyzeBehavior($request, $context);
        
        // Geographic and timezone analysis
        $geoData = $this->analyzeGeographicData($ip, $request);
        
        // Request pattern analysis
        $patternData = $this->analyzeRequestPatterns($request, $context);
        
        // Threat intelligence data
        $threatData = [
            'timestamp' => now()->toISOString(),
            'ip' => $ip,
            'context' => $context,
            'attack_data' => $attackData,
            'network' => $networkData,
            'fingerprint' => $fingerprint,
            'behavior' => $behavioralData,
            'geographic' => $geoData,
            'patterns' => $patternData,
            'threat_score' => $this->calculateThreatScore($networkData, $behavioralData, $patternData),
            'session_id' => $request->session()->getId(),
            'referer' => $request->header('referer'),
            'accept_language' => $request->header('accept-language'),
            'accept_encoding' => $request->header('accept-encoding'),
            'connection_type' => $request->header('connection'),
            'x_forwarded_for' => $request->header('x-forwarded-for'),
            'x_real_ip' => $request->header('x-real-ip'),
            'cf_connecting_ip' => $request->header('cf-connecting-ip'),
            'cf_ray' => $request->header('cf-ray'),
            'cf_country' => $request->header('cf-ipcountry'),
            'cf_visitor' => $request->header('cf-visitor'),
        ];
        
        // Store in database for analysis
        $this->storeThreatData($threatData);
        
        // Log high-threat incidents
        if ($threatData['threat_score'] > 70) {
            $this->logHighThreatIncident($threatData);
        }
        
        return $threatData;
    }

    /**
     * Analyze network data and IP reputation
     */
    private function analyzeNetworkData(string $ip, Request $request): array
    {
        // Check if IP is from known VPN/Proxy services
        $isVpn = $this->checkVpnProxy($ip);
        
        // Analyze IP range and type
        $ipType = $this->analyzeIpType($ip);
        
        // Check for suspicious IP patterns
        $suspiciousPatterns = $this->checkSuspiciousPatterns($ip);
        
        return [
            'ip_type' => $ipType,
            'is_vpn_proxy' => $isVpn,
            'is_private' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false,
            'is_ipv6' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false,
            'suspicious_patterns' => $suspiciousPatterns,
            'reverse_dns' => gethostbyaddr($ip),
            'headers' => [
                'x_forwarded_for' => $request->header('x-forwarded-for'),
                'x_real_ip' => $request->header('x-real-ip'),
                'cf_connecting_ip' => $request->header('cf-connecting-ip'),
            ]
        ];
    }

    /**
     * Generate browser and device fingerprint
     */
    private function generateFingerprint(Request $request): array
    {
        $userAgent = $request->userAgent();
        
        return [
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
            'platform' => $this->agent->platform(),
            'platform_version' => $this->agent->version($this->agent->platform()),
            'device' => $this->agent->device(),
            'is_mobile' => $this->agent->isMobile(),
            'is_tablet' => $this->agent->isTablet(),
            'is_desktop' => $this->agent->isDesktop(),
            'is_robot' => $this->agent->isRobot(),
            'robot_name' => $this->agent->robot(),
            'user_agent_hash' => hash('sha256', $userAgent),
            'screen_resolution' => $request->input('screen_resolution'),
            'timezone_offset' => $request->input('timezone_offset'),
            'language' => $request->header('accept-language'),
            'canvas_fingerprint' => $request->input('canvas_fingerprint'),
            'webgl_fingerprint' => $request->input('webgl_fingerprint'),
        ];
    }

    /**
     * Analyze behavioral patterns
     */
    private function analyzeBehavior(Request $request, string $context): array
    {
        $ip = $request->ip();
        $sessionId = $request->session()->getId();
        
        // Get recent activity for this IP
        $recentActivity = $this->getRecentActivity($ip, 3600); // Last hour
        
        // Analyze request timing patterns
        $timingPatterns = $this->analyzeTimingPatterns($recentActivity);
        
        // Check for automation patterns
        $automationSigns = $this->detectAutomation($request, $recentActivity);
        
        // Analyze request frequency
        $frequencyAnalysis = $this->analyzeFrequency($recentActivity);
        
        return [
            'request_count_last_hour' => count($recentActivity),
            'unique_endpoints' => count(array_unique(array_column($recentActivity, 'endpoint'))),
            'timing_patterns' => $timingPatterns,
            'automation_signs' => $automationSigns,
            'frequency_analysis' => $frequencyAnalysis,
            'session_duration' => $this->getSessionDuration($sessionId),
            'page_views' => $this->getPageViews($sessionId),
            'form_interactions' => $this->getFormInteractions($sessionId),
        ];
    }

    /**
     * Analyze geographic data
     */
    private function analyzeGeographicData(string $ip, Request $request): array
    {
        // This would integrate with a GeoIP service
        // For now, we'll use basic analysis
        return [
            'country' => $request->header('cf-ipcountry'),
            'timezone' => $request->input('timezone'),
            'language' => $request->header('accept-language'),
            'is_tor_exit' => $this->checkTorExit($ip),
            'is_datacenter' => $this->checkDatacenter($ip),
        ];
    }

    /**
     * Analyze request patterns
     */
    private function analyzeRequestPatterns(Request $request, string $context): array
    {
        $ip = $request->ip();
        $recentRequests = $this->getRecentRequests($ip, 300); // Last 5 minutes
        
        return [
            'request_methods' => array_count_values(array_column($recentRequests, 'method')),
            'endpoints_hit' => array_count_values(array_column($recentRequests, 'endpoint')),
            'status_codes' => array_count_values(array_column($recentRequests, 'status_code')),
            'average_response_time' => $this->calculateAverageResponseTime($recentRequests),
            'concurrent_requests' => $this->countConcurrentRequests($recentRequests),
            'parameter_patterns' => $this->analyzeParameterPatterns($recentRequests),
            'header_patterns' => $this->analyzeHeaderPatterns($recentRequests),
        ];
    }

    /**
     * Calculate overall threat score
     */
    private function calculateThreatScore(array $networkData, array $behavioralData, array $patternData): int
    {
        $score = 0;
        
        // Check if user agent is legitimate - Apply bonus reduction
        $legitimateUserAgents = config('ddos.advanced.legitimate_user_agents', []);
        $userAgent = $behavioralData['user_agent'] ?? '';
        $isLegitimateUserAgent = false;
        
        foreach ($legitimateUserAgents as $legit) {
            if (stripos($userAgent, $legit) !== false) {
                $isLegitimateUserAgent = true;
                break;
            }
        }
        
        // Apply 20 point reduction for legitimate user agents
        $legitimateBonus = $isLegitimateUserAgent ? -20 : 0;
        
        // VPN/Proxy usage - Reduced penalty (legitimate users often use VPNs)
        if ($networkData['is_vpn_proxy']) $score += 10; // Reduced from 20 to 10
        
        // Suspicious patterns
        if (!empty($networkData['suspicious_patterns'])) $score += 15;
        
        // High request frequency - Made more lenient
        if ($behavioralData['request_count_last_hour'] > 200) $score += 15; // Increased threshold from 100 to 200, reduced score from 25 to 15
        if ($behavioralData['request_count_last_hour'] > 1000) $score += 25; // Increased threshold from 500 to 1000, reduced score from 35 to 25
        
        // Automation signs - Only penalize high confidence automation
        if ($behavioralData['automation_signs']['high_confidence']) $score += 25; // Reduced from 30 to 25
        
        // Concurrent requests - More lenient for modern web apps
        if ($patternData['concurrent_requests'] > 20) $score += 15; // Increased threshold from 10 to 20, reduced score from 20 to 15
        
        // Tor usage - Keep this high as it's genuinely suspicious
        if ($networkData['is_tor_exit'] ?? false) $score += 30; // Increased from 25 to 30
        
        // Datacenter IP - Reduced penalty (many ISPs use datacenter ranges)
        if ($networkData['is_datacenter'] ?? false) $score += 8; // Reduced from 15 to 8
        
        // Apply legitimate user agent bonus and ensure minimum score of 0
        $finalScore = max(0, $score + $legitimateBonus);
        
        return min($finalScore, 100);
    }

    /**
     * Store threat data in database
     */
    private function storeThreatData(array $threatData): void
    {
        try {
            DB::table('threat_intelligence')->insert([
                'ip' => $threatData['ip'],
                'context' => $threatData['context'],
                'threat_score' => $threatData['threat_score'],
                'threat_data' => json_encode($threatData),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store threat intelligence data', [
                'error' => $e->getMessage(),
                'ip' => $threatData['ip']
            ]);
        }
    }

    /**
     * Log high-threat incidents
     */
    private function logHighThreatIncident(array $threatData): void
    {
        Log::channel('ddos_attacks')->critical('HIGH THREAT INCIDENT DETECTED', [
            'ip' => $threatData['ip'],
            'threat_score' => $threatData['threat_score'],
            'context' => $threatData['context'],
            'fingerprint' => $threatData['fingerprint'],
            'behavior' => $threatData['behavior'],
            'network' => $threatData['network'],
            'timestamp' => $threatData['timestamp']
        ]);
    }

    // Helper methods for analysis
    private function checkVpnProxy(string $ip): bool
    {
        // This would integrate with a VPN/Proxy detection service
        // For now, return false
        return false;
    }

    private function analyzeIpType(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'IPv6';
        }
        return 'IPv4';
    }

    private function checkSuspiciousPatterns(string $ip): array
    {
        $patterns = [];
        
        // Check for sequential IPs
        if (preg_match('/\.(\d+)$/', $ip, $matches)) {
            $lastOctet = (int)$matches[1];
            if ($lastOctet > 200) {
                $patterns[] = 'high_last_octet';
            }
        }
        
        return $patterns;
    }

    private function checkTorExit(string $ip): bool
    {
        // This would check against Tor exit node lists
        return false;
    }

    private function checkDatacenter(string $ip): bool
    {
        // This would check against datacenter IP ranges
        return false;
    }

    private function getRecentActivity(string $ip, int $seconds): array
    {
        return Cache::get("threat_activity_{$ip}", []);
    }

    private function analyzeTimingPatterns(array $activity): array
    {
        if (empty($activity)) return [];
        
        $intervals = [];
        for ($i = 1; $i < count($activity); $i++) {
            $intervals[] = $activity[$i]['timestamp'] - $activity[$i-1]['timestamp'];
        }
        
        return [
            'average_interval' => count($intervals) > 0 ? array_sum($intervals) / count($intervals) : 0,
            'min_interval' => count($intervals) > 0 ? min($intervals) : 0,
            'max_interval' => count($intervals) > 0 ? max($intervals) : 0,
            'is_regular' => count($intervals) > 3 && $this->isRegularPattern($intervals),
        ];
    }

    private function detectAutomation(Request $request, array $activity): array
    {
        $signs = [];
        
        // Check for missing common headers
        if (!$request->header('accept-language')) $signs[] = 'missing_accept_language';
        if (!$request->header('accept-encoding')) $signs[] = 'missing_accept_encoding';
        
        // Check for robot user agents
        if ($this->agent->isRobot()) $signs[] = 'robot_user_agent';
        
        // Check for very regular timing
        if (count($activity) > 5) {
            $intervals = [];
            for ($i = 1; $i < count($activity); $i++) {
                $intervals[] = $activity[$i]['timestamp'] - $activity[$i-1]['timestamp'];
            }
            if ($this->isVeryRegular($intervals)) {
                $signs[] = 'very_regular_timing';
            }
        }
        
        return [
            'signs' => $signs,
            'high_confidence' => count($signs) >= 3,
            'confidence_score' => min(count($signs) * 25, 100)
        ];
    }

    private function analyzeFrequency(array $activity): array
    {
        $now = time();
        $lastMinute = array_filter($activity, fn($a) => $now - $a['timestamp'] < 60);
        $last5Minutes = array_filter($activity, fn($a) => $now - $a['timestamp'] < 300);
        
        return [
            'requests_per_minute' => count($lastMinute),
            'requests_per_5_minutes' => count($last5Minutes),
            'burst_detected' => count($lastMinute) > 20,
            'sustained_attack' => count($last5Minutes) > 100,
        ];
    }

    private function getSessionDuration(string $sessionId): int
    {
        return Cache::get("session_duration_{$sessionId}", 0);
    }

    private function getPageViews(string $sessionId): int
    {
        return Cache::get("page_views_{$sessionId}", 0);
    }

    private function getFormInteractions(string $sessionId): int
    {
        return Cache::get("form_interactions_{$sessionId}", 0);
    }

    private function getRecentRequests(string $ip, int $seconds): array
    {
        return Cache::get("recent_requests_{$ip}", []);
    }

    private function calculateAverageResponseTime(array $requests): float
    {
        if (empty($requests)) return 0;
        
        $times = array_column($requests, 'response_time');
        return array_sum($times) / count($times);
    }

    private function countConcurrentRequests(array $requests): int
    {
        if (empty($requests)) return 0;
        
        $now = time();
        $concurrent = 0;
        $maxConcurrent = 0;
        
        foreach ($requests as $request) {
            if ($now - $request['timestamp'] < 5) { // Within 5 seconds
                $concurrent++;
                $maxConcurrent = max($maxConcurrent, $concurrent);
            } else {
                $concurrent = max(0, $concurrent - 1);
            }
        }
        
        return $maxConcurrent;
    }

    private function analyzeParameterPatterns(array $requests): array
    {
        $patterns = [];
        foreach ($requests as $request) {
            if (isset($request['parameters'])) {
                $patterns[] = array_keys($request['parameters']);
            }
        }
        
        return [
            'common_parameters' => $this->getCommonElements($patterns),
            'parameter_diversity' => count(array_unique(array_merge(...$patterns))),
        ];
    }

    private function analyzeHeaderPatterns(array $requests): array
    {
        $headers = [];
        foreach ($requests as $request) {
            if (isset($request['headers'])) {
                $headers[] = array_keys($request['headers']);
            }
        }
        
        return [
            'common_headers' => $this->getCommonElements($headers),
            'header_diversity' => count(array_unique(array_merge(...$headers))),
        ];
    }

    private function isRegularPattern(array $intervals): bool
    {
        if (count($intervals) < 3) return false;
        
        $avg = array_sum($intervals) / count($intervals);
        $variance = array_sum(array_map(fn($x) => pow($x - $avg, 2), $intervals)) / count($intervals);
        
        return $variance < ($avg * 0.1); // Less than 10% variance
    }

    private function isVeryRegular(array $intervals): bool
    {
        if (count($intervals) < 3) return false;
        
        $avg = array_sum($intervals) / count($intervals);
        $variance = array_sum(array_map(fn($x) => pow($x - $avg, 2), $intervals)) / count($intervals);
        
        return $variance < ($avg * 0.05); // Less than 5% variance
    }

    private function getCommonElements(array $arrays): array
    {
        if (empty($arrays)) return [];
        
        $counts = [];
        foreach ($arrays as $array) {
            foreach ($array as $element) {
                $counts[$element] = ($counts[$element] ?? 0) + 1;
            }
        }
        
        $total = count($arrays);
        return array_keys(array_filter($counts, fn($count) => $count >= $total * 0.8));
    }
}

