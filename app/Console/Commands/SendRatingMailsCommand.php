<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendRatingMailsCommand extends Command
{
    protected $signature = 'send:rating-mails';

    protected $description = 'Send out the Rating Mails after 24 Hours of Booked Event.';

    public function handle()
    {

    }
}
