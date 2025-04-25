<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Faq;

class TranslateFAQ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:faq {--page= : Optional page to translate} {--language= : Optional language to translate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate FAQ';

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
        $page = $this->option('page');
        $language = $this->option('language');

        $faq = Faq::where('page',$page)->where('language',$language)->get();

        foreach($faq as $item){
            $opositeLanguage = $language == 'de' ? 'en' : 'de';
            $check = Faq::where('page',$page)->where('language',$opositeLanguage)->where('source_id',$item->source_id)->where('question',$item->question)->first();

            if(!$check){
                $item->question = translate($item->question,$opositeLanguage);
                $item->answer = translate($item->answer,$opositeLanguage);
                $item->save();
            }
        }
    }
}
