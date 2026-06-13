<?php

namespace App\Console\Commands;

use App\Models\VacationInterestSignup;
use Illuminate\Console\Command;

class ExportVacationInterestSignups extends Command
{
    protected $signature = 'vacation:export-interests {--path=storage/app/vacation-interest-signups.csv}';

    protected $description = 'Export vacation interest signups for CRM import';

    public function handle(): int
    {
        $path = base_path($this->option('path'));
        $handle = fopen($path, 'w');
        fputcsv($handle, ['email', 'country', 'pillar', 'locale', 'created_at']);

        VacationInterestSignup::query()->orderBy('id')->chunk(200, function ($rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [$row->email, $row->country, $row->pillar, $row->locale, $row->created_at]);
            }
        });

        fclose($handle);
        $this->info('Exported to ' . $path);

        return self::SUCCESS;
    }
}
