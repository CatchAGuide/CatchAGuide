<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use Illuminate\Console\Command;

class BuildGuidingSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baut die Guidings um';

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
            if($guiding->price_two_persons > 0) {
                $guiding->price_two_persons;
            }
            if($guiding->price_three_persons > 0) {
                $guiding->price_three_persons;
            }
            if($guiding->price_four_persons > 0) {
                $guiding->price_four_persons;
            }
            if($guiding->price_five_persons > 0) {
                $guiding->price_five_persons;
            }
            $guiding->save();
        }
    }

}
