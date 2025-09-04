<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DDoSConfigCommand extends Command
{
    protected $signature = 'ddos:config 
                            {action : Action to perform (show|validate|reset|backup|restore)}
                            {--context= : Specific context to show/validate}
                            {--backup-file= : Backup file path for restore}';
    
    protected $description = 'Manage DDoS protection configuration';

    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'show':
                $this->showConfig();
                break;
            case 'validate':
                $this->validateConfig();
                break;
            case 'reset':
                $this->resetConfig();
                break;
            case 'backup':
                $this->backupConfig();
                break;
            case 'restore':
                $this->restoreConfig();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->showHelp();
        }
    }

    private function showConfig()
    {
        $context = $this->option('context');
        
        if ($context) {
            $this->showContextConfig($context);
        } else {
            $this->showAllConfig();
        }
    }

    private function showAllConfig()
    {
        $this->info('🛡️ DDoS Protection Configuration');
        $this->newLine();
        
        // Show defaults
        $this->info('📋 Default Configuration:');
        $defaults = config('ddos.defaults', []);
        $this->displayConfigArray($defaults);
        
        $this->newLine();
        
        // Show contexts
        $this->info('🎯 Context-Specific Configurations:');
        $contexts = config('ddos.contexts', []);
        
        foreach ($contexts as $context => $config) {
            $this->line("  <fg=cyan>{$context}</>");
            $this->displayConfigArray($config, '    ');
        }
        
        $this->newLine();
        
        // Show other settings
        $this->showOtherSettings();
    }

    private function showContextConfig(string $context)
    {
        $contexts = config('ddos.contexts', []);
        
        if (!isset($contexts[$context])) {
            $this->error("Context '{$context}' not found!");
            $this->info('Available contexts: ' . implode(', ', array_keys($contexts)));
            return;
        }
        
        $this->info("🎯 Configuration for context: <fg=cyan>{$context}</>");
        $this->newLine();
        
        $config = $contexts[$context];
        $this->displayConfigArray($config);
        
        // Show context-specific input patterns
        $contextPatterns = config("ddos.context_input_patterns.{$context}", []);
        if (!empty($contextPatterns)) {
            $this->newLine();
            $this->info('🔍 Context-Specific Input Patterns:');
            foreach ($contextPatterns as $pattern) {
                $this->line("  <fg=red>{$pattern}</>");
            }
        }
    }

    private function showOtherSettings()
    {
        $sections = [
            'threat_intelligence' => '🕵️ Threat Intelligence',
            'honeypots' => '🍯 Honeypots',
            'notifications' => '📧 Notifications',
            'logging' => '📝 Logging',
            'cache' => '💾 Cache',
            'responses' => '📤 Responses',
            'advanced' => '⚙️ Advanced'
        ];
        
        foreach ($sections as $key => $title) {
            $config = config("ddos.{$key}", []);
            if (!empty($config)) {
                $this->info("{$title}:");
                $this->displayConfigArray($config, '  ');
                $this->newLine();
            }
        }
    }

    private function displayConfigArray(array $config, string $indent = '')
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->line("{$indent}<fg=yellow>{$key}</>:");
                $this->displayConfigArray($value, $indent . '  ');
            } else {
                $formattedValue = $this->formatValue($value);
                $this->line("{$indent}<fg=yellow>{$key}</>: <fg=white>{$formattedValue}</>");
            }
        }
    }

    private function formatValue($value)
    {
        if (is_bool($value)) {
            return $value ? '<fg=green>true</>' : '<fg=red>false</>';
        }
        
        if (is_numeric($value)) {
            // Format time values
            if ($value > 60 && $value % 60 === 0) {
                $minutes = $value / 60;
                if ($minutes > 60 && $minutes % 60 === 0) {
                    $hours = $minutes / 60;
                    return "<fg=white>{$value}</> <fg=gray>({$hours}h)</>";
                }
                return "<fg=white>{$value}</> <fg=gray>({$minutes}m)</>";
            }
        }
        
        if (is_string($value) && strlen($value) > 50) {
            return '<fg=white>' . substr($value, 0, 47) . '...</>';
        }
        
        return '<fg=white>' . $value . '</>';
    }

    private function validateConfig()
    {
        $this->info('🔍 Validating DDoS configuration...');
        $this->newLine();
        
        $errors = [];
        $warnings = [];
        
        // Validate contexts
        $contexts = config('ddos.contexts', []);
        if (empty($contexts)) {
            $errors[] = 'No contexts defined in ddos.contexts';
        }
        
        foreach ($contexts as $context => $config) {
            $this->validateContextConfig($context, $config, $errors, $warnings);
        }
        
        // Validate defaults
        $defaults = config('ddos.defaults', []);
        if (empty($defaults)) {
            $warnings[] = 'No default configuration defined';
        }
        
        // Validate input patterns
        $patterns = config('ddos.input_patterns', []);
        if (empty($patterns)) {
            $warnings[] = 'No input validation patterns defined';
        }
        
        // Display results
        if (empty($errors) && empty($warnings)) {
            $this->info('✅ Configuration is valid!');
        } else {
            if (!empty($errors)) {
                $this->error('❌ Configuration Errors:');
                foreach ($errors as $error) {
                    $this->line("  • {$error}");
                }
                $this->newLine();
            }
            
            if (!empty($warnings)) {
                $this->warn('⚠️ Configuration Warnings:');
                foreach ($warnings as $warning) {
                    $this->line("  • {$warning}");
                }
            }
        }
    }

    private function validateContextConfig(string $context, array $config, array &$errors, array &$warnings)
    {
        $required = ['limits', 'block_threshold', 'block_multiplier'];
        
        foreach ($required as $field) {
            if (!isset($config[$field])) {
                $errors[] = "Context '{$context}' missing required field: {$field}";
            }
        }
        
        // Validate limits
        if (isset($config['limits'])) {
            $limits = $config['limits'];
            $requiredLimits = ['minute', 'hour', 'day'];
            
            foreach ($requiredLimits as $limit) {
                if (!isset($limits[$limit]) || !is_numeric($limits[$limit])) {
                    $errors[] = "Context '{$context}' has invalid limit: {$limit}";
                }
            }
            
            // Check if limits make sense
            if (isset($limits['minute'], $limits['hour'], $limits['day'])) {
                if ($limits['minute'] > $limits['hour']) {
                    $warnings[] = "Context '{$context}': minute limit ({$limits['minute']}) > hour limit ({$limits['hour']})";
                }
                if ($limits['hour'] > $limits['day']) {
                    $warnings[] = "Context '{$context}': hour limit ({$limits['hour']}) > day limit ({$limits['day']})";
                }
            }
        }
        
        // Validate stubborn settings
        if (isset($config['stubborn_threshold']) && isset($config['block_threshold'])) {
            if ($config['stubborn_threshold'] <= $config['block_threshold']) {
                $warnings[] = "Context '{$context}': stubborn_threshold should be higher than block_threshold";
            }
        }
    }

    private function resetConfig()
    {
        if (!$this->confirm('Are you sure you want to reset the DDoS configuration to defaults?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        $configPath = config_path('ddos.php');
        
        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->info('✅ Configuration file deleted.');
        }
        
        $this->info('🔄 Please run: php artisan config:cache');
        $this->info('📝 You may need to recreate the configuration file.');
    }

    private function backupConfig()
    {
        $configPath = config_path('ddos.php');
        
        if (!File::exists($configPath)) {
            $this->error('Configuration file not found!');
            return;
        }
        
        $backupPath = storage_path('app/ddos_config_backup_' . date('Y-m-d_H-i-s') . '.php');
        File::copy($configPath, $backupPath);
        
        $this->info("✅ Configuration backed up to: {$backupPath}");
    }

    private function restoreConfig()
    {
        $backupFile = $this->option('backup-file');
        
        if (!$backupFile) {
            $this->error('Please specify --backup-file option');
            return;
        }
        
        if (!File::exists($backupFile)) {
            $this->error("Backup file not found: {$backupFile}");
            return;
        }
        
        if (!$this->confirm('Are you sure you want to restore the configuration from backup?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        $configPath = config_path('ddos.php');
        File::copy($backupFile, $configPath);
        
        $this->info('✅ Configuration restored from backup.');
        $this->info('🔄 Please run: php artisan config:cache');
    }

    private function showHelp()
    {
        $this->info('Available actions:');
        $this->line('  show     - Display current configuration');
        $this->line('  validate - Validate configuration for errors');
        $this->line('  reset    - Reset configuration to defaults');
        $this->line('  backup   - Create backup of current configuration');
        $this->line('  restore  - Restore configuration from backup');
        $this->newLine();
        $this->info('Examples:');
        $this->line('  php artisan ddos:config show');
        $this->line('  php artisan ddos:config show --context=search');
        $this->line('  php artisan ddos:config validate');
        $this->line('  php artisan ddos:config backup');
        $this->line('  php artisan ddos:config restore --backup-file=storage/app/backup.php');
    }
}
