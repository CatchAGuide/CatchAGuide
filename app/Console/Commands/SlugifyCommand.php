<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use App\Services\Guiding\GuidingSeoService;
use Illuminate\Console\Command;

class SlugifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slugify:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Slugified alle Titel';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GuidingSeoService $seo): int
    {
        $guidings = Guiding::all();
        foreach ($guidings as $guiding) {
            $guiding->slug = $seo->generateSlug(
                (string) ($guiding->title ?: 'guiding-' . $guiding->id),
                (string) ($guiding->location ?: 'location'),
                $guiding->id
            );
            $guiding->save();
        }

        return self::SUCCESS;
    }
}
