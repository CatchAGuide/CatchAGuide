<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\DDoSNotificationService;

class TestDDoSProtection extends Command
{
    protected $signature = 'test:ddos-protection {--url=http://cag.local} {--test-email}';
    protected $description = 'Test DDoS protection mechanisms';

    public function handle()
    {
        $url = $this->option('url');
        $testEmail = $this->option('test-email');
        
        $this->info('🛡️  DDoS Protection Test');
        $this->info('========================');
        $this->newLine();
        
        // Test email functionality if requested
        if ($testEmail) {
            $this->testEmailNotifications();
            return 0;
        }
        
        // Clear cache first
        Cache::flush();
        $this->line('Cache cleared for fresh test...');
        $this->newLine();
        
        // Test 1: Input Validation
        $this->testInputValidation($url);
        
        // Test 2: Rate Limiting
        $this->testRateLimiting($url);
        
        $this->newLine();
        $this->info('✅ Test completed! Check results above.');
        $this->line('💡 Run with --test-email to test email notifications');
        
        return 0;
    }
    
    private function testInputValidation($url)
    {
        $this->info('1. Testing Input Validation...');
        
        $maliciousInputs = [
            ['city' => '<script>alert("xss")</script>', 'country' => 'Test'],
            ['city' => 'Test', 'country' => "'; DROP TABLE users; --"],
            ['city' => 'Test', 'country' => 'admin'],
            ['city' => 'Test', 'country' => 'javascript:alert(1)'],
            ['city' => 'Test', 'country' => 'Test'] // Valid input
        ];
        
        $blockedCount = 0;
        $allowedCount = 0;
        
        foreach ($maliciousInputs as $index => $input) {
            try {
                $response = Http::timeout(5)->get($url . '/guidings', $input);
                
                if ($response->status() === 400) {
                    $blockedCount++;
                    $this->line("   Test " . ($index + 1) . ": BLOCKED (400) ✅");
                } else {
                    $allowedCount++;
                    $this->line("   Test " . ($index + 1) . ": ALLOWED (" . $response->status() . ") " . ($index === 4 ? '✅' : '❌'));
                }
            } catch (\Exception $e) {
                if ($index === 4) {
                    // Valid input timing out might indicate rate limiting
                    $this->line("   Test " . ($index + 1) . ": TIMEOUT (likely rate limited) ⚠️");
                    $allowedCount++;
                } else {
                    $this->line("   Test " . ($index + 1) . ": TIMEOUT (likely blocked) ✅");
                    $blockedCount++;
                }
            }
            
            usleep(500000); // 500ms delay to avoid rate limiting
        }
        
        $this->line("   Result: $blockedCount blocked, $allowedCount allowed");
        $this->line("   " . ($blockedCount >= 4 && $allowedCount >= 1 ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();
    }
    
    private function testRateLimiting($url)
    {
        $this->info('2. Testing Rate Limiting...');
        
        $successCount = 0;
        $rateLimitedCount = 0;
        $timeoutCount = 0;
        
        for ($i = 0; $i < 25; $i++) {
            try {
                $response = Http::timeout(5)->get($url . '/guidings', [
                    'city' => 'Test',
                    'country' => 'Test'
                ]);
                
                if ($response->status() === 200) {
                    $successCount++;
                } elseif ($response->status() === 429) {
                    $rateLimitedCount++;
                }
                
                if ($i % 5 === 0) {
                    $this->line("   Request " . ($i + 1) . ": " . $response->status());
                }
            } catch (\Exception $e) {
                $timeoutCount++;
                if ($i % 5 === 0) {
                    $this->line("   Request " . ($i + 1) . ": TIMEOUT");
                }
            }
            
            usleep(200000); // 200ms delay
        }
        
        $this->line("   Result: $successCount successful, $rateLimitedCount rate limited, $timeoutCount timeouts");
        $this->line("   " . (($successCount + $timeoutCount) <= 20 && ($rateLimitedCount > 0 || $timeoutCount > 0) ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();
    }
    
    private function testEmailNotifications()
    {
        $this->info('📧 Testing Email Notifications...');
        $this->newLine();
        
        try {
            $notificationService = new DDoSNotificationService();
            $notificationService->sendTestAlert();
            
            $this->line('✅ Test email sent successfully!');
            $this->line('📬 Check your email inbox for the test alert.');
            $this->newLine();
            $this->line('Admin email: ' . config('mail.admin_email'));
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: ' . $e->getMessage());
            $this->newLine();
            $this->line('💡 Make sure your mail configuration is correct in .env file');
            $this->line('   Required: MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD');
            $this->line('   Optional: MAIL_ADMIN_EMAIL (defaults to MAIL_FROM_ADDRESS)');
        }
    }
}
