<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use App\Services\Guiding\GuidingSeoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDuplicateGuidingSlugsCommand extends Command
{
    protected $signature = 'guidings:fix-duplicate-slugs
                            {--dry-run : List changes without saving}';

    protected $description = 'Regenerate unique slugs for guidings that share a public slug (keeps the oldest id per slug)';

    public function handle(GuidingSeoService $seo): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $duplicateSlugs = DB::table('guidings')
            ->select('slug')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        if ($duplicateSlugs->isEmpty()) {
            $this->info('No duplicate guiding slugs found.');

            return self::SUCCESS;
        }

        $fixed = 0;

        foreach ($duplicateSlugs as $slug) {
            $guidings = Guiding::query()
                ->where('slug', $slug)
                ->orderBy('id')
                ->get();

            $keeper = $guidings->first();
            $this->line("Keeping #{$keeper->id} => {$keeper->slug}");

            foreach ($guidings->skip(1) as $guiding) {
                $newSlug = $seo->generateSlug(
                    (string) ($guiding->title ?: 'guiding-' . $guiding->id),
                    (string) ($guiding->location ?: 'location'),
                    $guiding->id
                );

                $this->warn("  #{$guiding->id} {$guiding->slug} -> {$newSlug}");

                if (! $dryRun) {
                    $guiding->slug = $newSlug;
                    $guiding->save();
                }

                $fixed++;
            }
        }

        $this->info(($dryRun ? 'Would fix' : 'Fixed') . " {$fixed} duplicate slug(s).");

        return self::SUCCESS;
    }
}
