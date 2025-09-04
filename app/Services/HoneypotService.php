<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HoneypotService
{
    /**
     * Create honeypot traps to catch automated attacks
     */
    public function createHoneypotTraps(Request $request): array
    {
        $traps = [];
        
        // Hidden form field trap
        $traps['hidden_field'] = $this->createHiddenFieldTrap();
        
        // Time-based trap
        $traps['time_trap'] = $this->createTimeTrap();
        
        // JavaScript challenge trap
        $traps['js_challenge'] = $this->createJavaScriptChallenge();
        
        // CSS-based trap
        $traps['css_trap'] = $this->createCssTrap();
        
        // Honeypot endpoint trap
        $traps['endpoint_trap'] = $this->createEndpointTrap();
        
        // Session-based trap
        $traps['session_trap'] = $this->createSessionTrap($request);
        
        return $traps;
    }

    /**
     * Check if honeypot traps were triggered
     */
    public function checkHoneypotTriggers(Request $request): array
    {
        $triggers = [];
        
        // Check hidden field trap
        if ($request->has('website_url') && !empty($request->input('website_url'))) {
            $triggers['hidden_field'] = [
                'triggered' => true,
                'value' => $request->input('website_url'),
                'severity' => 'high',
                'description' => 'Bot filled hidden honeypot field'
            ];
        }
        
        // Check time-based trap
        if ($request->has('form_timestamp')) {
            $submissionTime = time() - $request->input('form_timestamp');
            if ($submissionTime < 3) { // Submitted too quickly
                $triggers['time_trap'] = [
                    'triggered' => true,
                    'submission_time' => $submissionTime,
                    'severity' => 'high',
                    'description' => 'Form submitted too quickly (likely automated)'
                ];
            }
        }
        
        // Check JavaScript challenge
        if ($request->has('js_challenge') && $request->input('js_challenge') !== $this->getExpectedJsChallenge()) {
            $triggers['js_challenge'] = [
                'triggered' => true,
                'provided' => $request->input('js_challenge'),
                'expected' => $this->getExpectedJsChallenge(),
                'severity' => 'high',
                'description' => 'Failed JavaScript challenge (likely headless browser)'
            ];
        }
        
        // Check CSS trap
        if ($request->has('css_trap') && !empty($request->input('css_trap'))) {
            $triggers['css_trap'] = [
                'triggered' => true,
                'value' => $request->input('css_trap'),
                'severity' => 'high',
                'description' => 'Bot filled CSS-hidden honeypot field'
            ];
        }
        
        // Check endpoint trap
        if ($this->wasEndpointTrapTriggered($request)) {
            $triggers['endpoint_trap'] = [
                'triggered' => true,
                'severity' => 'critical',
                'description' => 'Bot accessed honeypot endpoint'
            ];
        }
        
        // Check session trap
        if ($this->wasSessionTrapTriggered($request)) {
            $triggers['session_trap'] = [
                'triggered' => true,
                'severity' => 'medium',
                'description' => 'Suspicious session behavior detected'
            ];
        }
        
        // Log and store honeypot triggers
        if (!empty($triggers)) {
            $this->logHoneypotTriggers($request, $triggers);
            $this->storeHoneypotData($request, $triggers);
        }
        
        return $triggers;
    }

    /**
     * Create hidden form field trap
     */
    private function createHiddenFieldTrap(): array
    {
        return [
            'field_name' => 'website_url',
            'field_type' => 'text',
            'field_attributes' => [
                'style' => 'display: none !important; visibility: hidden; position: absolute; left: -9999px;',
                'tabindex' => '-1',
                'autocomplete' => 'off'
            ],
            'description' => 'Hidden field that should never be filled by humans'
        ];
    }

    /**
     * Create time-based trap
     */
    private function createTimeTrap(): array
    {
        return [
            'field_name' => 'form_timestamp',
            'field_type' => 'hidden',
            'field_value' => time(),
            'description' => 'Timestamp to detect too-fast form submissions'
        ];
    }

    /**
     * Create JavaScript challenge
     */
    private function createJavaScriptChallenge(): array
    {
        $challenge = $this->generateJsChallenge();
        Cache::put("js_challenge_{$challenge['id']}", $challenge['answer'], 300); // 5 minutes
        
        return [
            'field_name' => 'js_challenge',
            'field_type' => 'hidden',
            'challenge_id' => $challenge['id'],
            'javascript' => $challenge['javascript'],
            'description' => 'JavaScript challenge to verify browser capabilities'
        ];
    }

    /**
     * Create CSS-based trap
     */
    private function createCssTrap(): array
    {
        return [
            'field_name' => 'css_trap',
            'field_type' => 'text',
            'field_attributes' => [
                'style' => 'position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;',
                'tabindex' => '-1',
                'autocomplete' => 'off'
            ],
            'description' => 'CSS-hidden field that should be invisible to humans'
        ];
    }

    /**
     * Create honeypot endpoint trap
     */
    private function createEndpointTrap(): array
    {
        $trapUrl = '/admin/panel/' . bin2hex(random_bytes(8));
        Cache::put("honeypot_endpoint_{$trapUrl}", true, 3600); // 1 hour
        
        return [
            'url' => $trapUrl,
            'description' => 'Fake admin endpoint that should never be accessed'
        ];
    }

    /**
     * Create session-based trap
     */
    private function createSessionTrap(Request $request): array
    {
        $sessionId = $request->session()->getId();
        $trapData = [
            'session_id' => $sessionId,
            'created_at' => time(),
            'expected_behavior' => 'normal_user_interaction'
        ];
        
        Cache::put("session_trap_{$sessionId}", $trapData, 1800); // 30 minutes
        
        return [
            'session_id' => $sessionId,
            'description' => 'Session behavior monitoring trap'
        ];
    }

    /**
     * Generate JavaScript challenge
     */
    private function generateJsChallenge(): array
    {
        $id = uniqid('js_challenge_');
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $answer = $num1 + $num2;
        
        $javascript = "
            document.addEventListener('DOMContentLoaded', function() {
                var challenge = document.getElementById('js_challenge');
                if (challenge) {
                    challenge.value = '{$answer}';
                }
            });
        ";
        
        return [
            'id' => $id,
            'javascript' => $javascript,
            'answer' => $answer,
            'question' => "What is {$num1} + {$num2}?"
        ];
    }

    /**
     * Get expected JavaScript challenge answer
     */
    private function getExpectedJsChallenge(): string
    {
        // This would be retrieved from cache based on the challenge ID
        return 'expected_answer';
    }

    /**
     * Check if endpoint trap was triggered
     */
    private function wasEndpointTrapTriggered(Request $request): bool
    {
        $path = $request->path();
        return Cache::has("honeypot_endpoint_{$path}");
    }

    /**
     * Check if session trap was triggered
     */
    private function wasSessionTrapTriggered(Request $request): bool
    {
        $sessionId = $request->session()->getId();
        $trapData = Cache::get("session_trap_{$sessionId}");
        
        if (!$trapData) return false;
        
        // Check for suspicious session behavior
        $suspiciousPatterns = [
            'no_user_interaction' => !$request->has('user_interaction'),
            'rapid_requests' => $this->hasRapidRequests($sessionId),
            'missing_headers' => !$request->header('accept-language'),
        ];
        
        return count(array_filter($suspiciousPatterns)) >= 2;
    }

    /**
     * Check for rapid requests in session
     */
    private function hasRapidRequests(string $sessionId): bool
    {
        $requests = Cache::get("session_requests_{$sessionId}", []);
        $now = time();
        
        // Count requests in last 10 seconds
        $recentRequests = array_filter($requests, fn($timestamp) => $now - $timestamp < 10);
        
        return count($recentRequests) > 5;
    }

    /**
     * Log honeypot triggers
     */
    private function logHoneypotTriggers(Request $request, array $triggers): void
    {
        Log::channel('ddos_attacks')->warning('HONEYPOT TRIGGERED', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'triggers' => $triggers,
            'url' => $request->fullUrl(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Store honeypot data for analysis
     */
    private function storeHoneypotData(Request $request, array $triggers): void
    {
        try {
            DB::table('honeypot_triggers')->insert([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'triggers' => json_encode($triggers),
                'url' => $request->fullUrl(),
                'request_data' => json_encode($request->all()),
                'headers' => json_encode($request->headers->all()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store honeypot data', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
        }
    }

    /**
     * Generate honeypot HTML for forms
     */
    public function generateHoneypotHtml(): string
    {
        $traps = $this->createHoneypotTraps(request());
        $html = '';
        
        // Hidden field trap
        $hiddenField = $traps['hidden_field'];
        $html .= '<input type="' . $hiddenField['field_type'] . '" ';
        $html .= 'name="' . $hiddenField['field_name'] . '" ';
        $html .= 'value="" ';
        foreach ($hiddenField['field_attributes'] as $attr => $value) {
            $html .= $attr . '="' . $value . '" ';
        }
        $html .= '/>';
        
        // Time trap
        $timeTrap = $traps['time_trap'];
        $html .= '<input type="' . $timeTrap['field_type'] . '" ';
        $html .= 'name="' . $timeTrap['field_name'] . '" ';
        $html .= 'value="' . $timeTrap['field_value'] . '" />';
        
        // CSS trap
        $cssTrap = $traps['css_trap'];
        $html .= '<input type="' . $cssTrap['field_type'] . '" ';
        $html .= 'name="' . $cssTrap['field_name'] . '" ';
        $html .= 'value="" ';
        foreach ($cssTrap['field_attributes'] as $attr => $value) {
            $html .= $attr . '="' . $value . '" ';
        }
        $html .= '/>';
        
        // JavaScript challenge
        $jsChallenge = $traps['js_challenge'];
        $html .= '<input type="hidden" name="' . $jsChallenge['field_name'] . '" value="" />';
        $html .= '<script>' . $jsChallenge['javascript'] . '</script>';
        
        return $html;
    }

    /**
     * Get honeypot statistics
     */
    public function getHoneypotStats(int $hours = 24): array
    {
        try {
            $since = now()->subHours($hours);
            
            $stats = DB::table('honeypot_triggers')
                ->where('created_at', '>=', $since)
                ->selectRaw('
                    COUNT(*) as total_triggers,
                    COUNT(DISTINCT ip) as unique_ips,
                    COUNT(CASE WHEN JSON_EXTRACT(triggers, "$.hidden_field.triggered") = true THEN 1 END) as hidden_field_triggers,
                    COUNT(CASE WHEN JSON_EXTRACT(triggers, "$.time_trap.triggered") = true THEN 1 END) as time_trap_triggers,
                    COUNT(CASE WHEN JSON_EXTRACT(triggers, "$.js_challenge.triggered") = true THEN 1 END) as js_challenge_triggers,
                    COUNT(CASE WHEN JSON_EXTRACT(triggers, "$.css_trap.triggered") = true THEN 1 END) as css_trap_triggers,
                    COUNT(CASE WHEN JSON_EXTRACT(triggers, "$.endpoint_trap.triggered") = true THEN 1 END) as endpoint_trap_triggers
                ')
                ->first();
            
            return (array) $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get honeypot stats', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
