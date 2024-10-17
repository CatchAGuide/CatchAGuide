<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\BlockedEvent;

class UpdateBlockEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateevents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $events = BlockedEvent::all();

        foreach($events as $event){
            if($event->booking){
                $event->guiding_id = $event->booking->guiding_id;
                $event->save();
            }
       
        }
        return 0;
    }
}
