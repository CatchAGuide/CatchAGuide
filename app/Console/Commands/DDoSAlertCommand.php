<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DDoSNotificationService;

class DDoSAlertCommand extends Command
{
    protected $signature = 'ddos:alert {action} {--email=}';
    protected $description = 'Manage DDoS email alerts';

    public function handle()
    {
        $action = $this->argument('action');
        $email = $this->option('email');
        
        switch ($action) {
            case 'test':
                $this->testAlert();
                break;
            case 'config':
                $this->showConfig();
                break;
            default:
                $this->error('Invalid action. Use: test, config');
                return 1;
        }
        
        return 0;
    }
    
    private function testAlert()
    {
        $this->info('📧 Sending Test DDoS Alert...');
        
        try {
            $notificationService = new DDoSNotificationService();
            $notificationService->sendTestAlert();
            
            $this->line('✅ Test alert sent successfully!');
            $this->line('📬 Check your email: ' . config('mail.admin_email'));
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test alert: ' . $e->getMessage());
        }
    }
    
    private function showConfig()
    {
        $this->info('📧 DDoS Alert Configuration');
        $this->info('===========================');
        $this->newLine();
        
        $this->line('Admin Email: ' . config('mail.admin_email'));
        $this->line('From Email: ' . config('mail.from.address'));
        $this->line('Mail Driver: ' . config('mail.default'));
        
        if (config('mail.default') === 'smtp') {
            $this->line('SMTP Host: ' . config('mail.mailers.smtp.host'));
            $this->line('SMTP Port: ' . config('mail.mailers.smtp.port'));
        }
        
        $this->newLine();
        $this->line('💡 To change admin email, add to .env:');
        $this->line('   MAIL_ADMIN_EMAIL=your-email@domain.com');
    }
}
