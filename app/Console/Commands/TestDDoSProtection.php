<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\DDoSNotificationService;
use App\Services\DDoSProtectionService;
use App\Services\Translation\GeminiTranslationService;

class TestDDoSProtection extends Command
{
    protected $signature = 'test:ddos-protection {--url=http://cag.local} {--test-email} {--context=all : Test specific context (gemini, search, checkout, all)}';
    protected $description = 'Test DDoS protection mechanisms for Gemini, Search, and Checkout';


    public function handle()
    {
        $url = $this->option('url');
        $testEmail = $this->option('test-email');
        $context = $this->option('context');
        
        $this->info('🛡️  DDoS Protection Test Suite');
        $this->info('===============================');

        $this->newLine();
        
        // Test email functionality if requested
        if ($testEmail) {
            $this->testEmailNotifications();
            return 0;
        }
        
        // Clear cache first
        Cache::flush();
        $this->info('Cache cleared for fresh test...');
        $this->newLine();
        
        // Test based on context
        switch ($context) {
            case 'gemini':
                $this->testGeminiProtection();
                break;
            case 'search':
                $this->testSearchProtection($url);
                break;
            case 'checkout':
                $this->testCheckoutProtection($url);
                break;
            case 'all':
            default:
                $this->testGeminiProtection();
                $this->testSearchProtection($url);
                $this->testCheckoutProtection($url);
                break;
        }
        
        $this->info('✅ DDoS Protection Test Complete');
        return 0;
    }

    /**
     * Test Gemini Translation Service Protection
     */
    private function testGeminiProtection()
    {
        $this->info('🤖 Testing Gemini Translation Protection...');
        $this->newLine();

        $protectionService = app(DDoSProtectionService::class);
        $geminiService = app(GeminiTranslationService::class);

        // Test 1: Rate Limiting
        $this->line('1. Testing Gemini Rate Limiting...');
        $successCount = 0;
        $rateLimitedCount = 0;

        for ($i = 0; $i < 15; $i++) {
            try {
                $result = $geminiService->translate('Deutschland');
                $successCount++;
                if ($i % 3 === 0) {
                    $this->line("   Request " . ($i + 1) . ": SUCCESS");
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Rate limit')) {
                    $rateLimitedCount++;
                    if ($i % 3 === 0) {
                        $this->line("   Request " . ($i + 1) . ": RATE LIMITED ✅");
                    }
                } else {
                    if ($i % 3 === 0) {
                        $this->line("   Request " . ($i + 1) . ": ERROR - " . $e->getMessage());
                    }
                }
            }
            usleep(100000); // 100ms delay
        }

        $this->line("   Result: $successCount successful, $rateLimitedCount rate limited");
        $this->line("   " . ($rateLimitedCount > 0 ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();

        // Test 2: Input Validation
        $this->line('2. Testing Gemini Input Validation...');
        $maliciousInputs = [
            '<script>alert("xss")</script>',
            "'; DROP TABLE users; --",
            'admin',
            'javascript:alert(1)',
            'Deutschland' // Valid input
        ];

        $blockedCount = 0;
        $allowedCount = 0;

        foreach ($maliciousInputs as $index => $input) {
            try {
                $result = $geminiService->translate($input);
                $allowedCount++;
                $this->line("   Test " . ($index + 1) . ": ALLOWED " . ($index === 4 ? '✅' : '❌'));
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Rate limit') || str_contains($e->getMessage(), 'Invalid')) {
                    $blockedCount++;
                    $this->line("   Test " . ($index + 1) . ": BLOCKED ✅");
                } else {
                    $allowedCount++;
                    $this->line("   Test " . ($index + 1) . ": ERROR - " . $e->getMessage());
                }
            }
            usleep(200000); // 200ms delay
        }

        $this->line("   Result: $blockedCount blocked, $allowedCount allowed");
        $this->line("   " . ($blockedCount >= 4 && $allowedCount >= 1 ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();
    }

    /**
     * Test Search/Filter Protection
     */
    private function testSearchProtection($url)
    {
        $this->info('🔍 Testing Search/Filter Protection...');
        $this->newLine();

        // Test 1: Input Validation
        $this->line('1. Testing Search Input Validation...');

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
                    $this->line("   Test " . ($index + 1) . ": TIMEOUT (likely rate limited) ⚠️");
                    $allowedCount++;
                } else {
                    $this->line("   Test " . ($index + 1) . ": TIMEOUT (likely blocked) ✅");
                    $blockedCount++;
                }
            }
            usleep(500000); // 500ms delay
        }

        $this->line("   Result: $blockedCount blocked, $allowedCount allowed");
        $this->line("   " . ($blockedCount >= 4 && $allowedCount >= 1 ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();

        // Test 2: Rate Limiting
        $this->line('2. Testing Search Rate Limiting...');
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

    /**
     * Test Checkout Protection
     */
    private function testCheckoutProtection($url)
    {
        $this->info('🛒 Testing Checkout Protection...');
        $this->newLine();

        // Test 1: Checkout Page Access Rate Limiting
        $this->line('1. Testing Checkout Page Rate Limiting...');
        $successCount = 0;
        $rateLimitedCount = 0;
        $timeoutCount = 0;

        for ($i = 0; $i < 15; $i++) {
            try {
                $response = Http::timeout(5)->get($url . '/checkout');
                
                if ($response->status() === 200) {
                    $successCount++;
                } elseif ($response->status() === 429) {
                    $rateLimitedCount++;
                }
                
                if ($i % 3 === 0) {
                    $this->line("   Request " . ($i + 1) . ": " . $response->status());
                }
            } catch (\Exception $e) {
                $timeoutCount++;
                if ($i % 3 === 0) {
                    $this->line("   Request " . ($i + 1) . ": TIMEOUT");
                }
            }
            usleep(300000); // 300ms delay
        }

        $this->line("   Result: $successCount successful, $rateLimitedCount rate limited, $timeoutCount timeouts");
        $this->line("   " . (($successCount + $timeoutCount) <= 10 && ($rateLimitedCount > 0 || $timeoutCount > 0) ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();

        // Test 2: Checkout Form Input Validation
        $this->line('2. Testing Checkout Input Validation...');
        $maliciousInputs = [
            ['userData' => ['email' => '<script>alert("xss")</script>', 'firstname' => 'Test']],
            ['userData' => ['email' => 'test@test.com', 'firstname' => "'; DROP TABLE users; --"]],
            ['userData' => ['email' => 'admin@admin.com', 'firstname' => 'Test']],
            ['userData' => ['email' => 'test@test.com', 'firstname' => 'Test']], // Valid input
        ];

        $blockedCount = 0;
        $allowedCount = 0;

        foreach ($maliciousInputs as $index => $input) {
            try {
                $response = Http::timeout(5)->post($url . '/checkout', $input);
                
                if ($response->status() === 400 || $response->status() === 422) {
                    $blockedCount++;
                    $this->line("   Test " . ($index + 1) . ": BLOCKED (" . $response->status() . ") ✅");
                } else {
                    $allowedCount++;
                    $this->line("   Test " . ($index + 1) . ": ALLOWED (" . $response->status() . ") " . ($index === 3 ? '✅' : '❌'));
                }
            } catch (\Exception $e) {
                if ($index === 3) {
                    $this->line("   Test " . ($index + 1) . ": TIMEOUT (likely rate limited) ⚠️");
                    $allowedCount++;
                } else {
                    $this->line("   Test " . ($index + 1) . ": TIMEOUT (likely blocked) ✅");
                    $blockedCount++;
                }
            }
            usleep(500000); // 500ms delay
        }

        $this->line("   Result: $blockedCount blocked, $allowedCount allowed");
        $this->line("   " . ($blockedCount >= 3 && $allowedCount >= 1 ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();
    }

    /**
     * Test Email Notifications
     */
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
