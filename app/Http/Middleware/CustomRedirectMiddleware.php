<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $urls = [
            'https://catchaguide.com/blog' => 'https://catchaguide.com/fishing-magazine',
            'https://catchaguide.com/angelmagazin' => 'https://catchaguide.com/fishing-magazine',
            'https://catchaguide.de/blog' => 'https://catchaguide.de/angelmagazin',
            'https://catchaguide.de/fishing-magazine' => 'https://catchaguide.de/angelmagazin',
        
        
            'https://catchaguide.com/fishing-magazine/angelurlaub-in-schweden-2023/de' => 'https://catchaguide.de/angelmagazin/angeln-in-schweden',
            'https://catchaguide.com/fishing-magazine/angelurlaub-in-norwegen/de' => 'https://catchaguide.de/angelmagazin/angelurlaub-in-norwegen',
            'https://catchaguide.com/blog/angelurlaub-in-schweden-2023' => 'https://catchaguide.de/angelmagazin/angeln-in-schweden',
            'https://catchaguide.com/blog/angeln-in-schweden' => 'https://catchaguide.de/angelmagazin/angeln-in-schweden',
            'https://catchaguide.com/blog/meerforelle-an-der-deutschen-ostsee-kuste' => 'https://catchaguide.de/angelmagazin/meerforelle-an-der-deutschen-ostsee-kuste',
            'https://catchaguide.com/blog/angeln-auf-hornhecht-2023' => 'https://catchaguide.de/angelmagazin/angeln-auf-hornhecht-2023',
            'https://catchaguide.com/blog/zanderangeln-im-sommer-2023' => 'https://catchaguide.de/angelmagazin/zanderangeln-im-sommer-2023',
            'https://catchaguide.com/blog/angelurlaub-in-danemark' => 'https://catchaguide.de/angelmagazin/angelurlaub-in-danemark',
            'https://catchaguide.com/blog/angeltour-in-schweden-mit-guide-2023' => 'https://catchaguide.de/angelmagazin/angeltour-in-schweden-mit-guide-2023',

            'https://catchaguide.com/blog/hecht-guiding-mit-einem-angelguide-auf-hechtfang' => 'https://catchaguide.de/angelmagazin/hecht-guiding-mit-einem-angelguide-auf-hechtfang',
            'https://catchaguide.com/blog/plattfischangeln-in-deutschland' => 'https://catchaguide.de/angelmagazin/plattfischangeln-in-deutschland',
            'https://catchaguide.com/blog/die-schwarzmundgrundel-fluch-oder-segen' => 'https://catchaguide.de/angelmagazin/die-schwarzmundgrundel-fluch-oder-segen',
            'https://catchaguide.com/blog/angelguiding-das-passende-offer-finding' => 'https://catchaguide.de/angelmagazin/angelguiding-das-passende-offer-finding',

            'https://catchaguide.com/blog/barsch-guiding' => 'https://catchaguide.de/angelmagazin/barsch-guiding',
            'https://catchaguide.com/blog/das-barschangeln' => 'https://catchaguide.de/angelmagazin/das-barschangeln',
            'https://catchaguide.com/blog/angelurlaub-in-europaischen-nachbarlandern' => 'https://catchaguide.de/angelmagazin/angelurlaub-in-europaischen-nachbarlandern',
            'https://catchaguide.com/angelmagazin/angelurlaub-in-europaischen-nachbarlandern' => 'https://catchaguide.de/angelmagazin/angelurlaub-in-europaischen-nachbarlandern',
            'https://catchaguide.com/blog/barschangeln-im-winter-vom-ufer-auf-finesse-rigs' => 'https://catchaguide.de/angelmagazin/barschangeln-im-winter-vom-ufer-auf-finesse-rigs',

            'https://catchaguide.com/fishing-magazine/raubfischangeln-in-sudschweden-ein-paradies-fur-angler/de' => 'https://catchaguide.de/angelmagazin/raubfischangeln-in-sudschweden-ein-paradies-fur-angler',
            'https://catchaguide.com/blog/angeln-mit-technologie-garmin-livescope-lawrence-live-target-humminbird-mega-live-immer-gefragter' => 'https://catchaguide.de/angelmagazin/angeln-mit-technologie-garmin-livescope-lawrence-live-target-humminbird-mega-live-immer-gefragter',

            'https://catchaguide.com/fishing-magazine/meerforelle-an-der-deutschen-ostsee-kuste/de' => 'https://catchaguide.de/angelmagazin/meerforelle-an-der-deutschen-ostsee-kuste',
            'https://catchaguide.com/fishing-magazine/angelurlaub-in-europaischen-nachbarlandern/de'=> 'https://catchaguide.de/angelmagazin/angelurlaub-in-europaischen-nachbarlandern',

            'https://catchaguide.com/blog/tripps-tricks-von-einem-welsguide' => 'https://catchaguide.de/angelmagazin/tripps-tricks-von-einem-welsguide',
            'https://catchaguide.com/angelmagazin/tripps-tricks-von-einem-welsguide' => 'https://catchaguide.de/angelmagazin/tripps-tricks-von-einem-welsguide',

        ];

        $currentUrl = $request->fullUrl();

        if (array_key_exists($currentUrl, $urls)) {
            // If the current URL is in the $urls array, perform the redirection
            return redirect($urls[$currentUrl], 301); // 301 for permanent redirection
        }

        return $next($request);
    }
}
