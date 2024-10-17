<?php

namespace App\Console\Commands;

use App\Models\Guiding;
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $guidings = Guiding::all();
        foreach($guidings as $guiding) {
            $guiding->slug = slugify($guiding->title . "-in-" .$guiding->location);
            $guiding->save();
        }
    }
}
