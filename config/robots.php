<?php

/*
|--------------------------------------------------------------------------
| robots.txt
|--------------------------------------------------------------------------
|
| Built per host by App\Services\Seo\RobotsTxtBuilder. Blocking a crawler
| here is the cheapest protection there is: a bot that obeys robots.txt stops
| requesting pages, so it never reaches PHP. Keep app-level limits in
| config/ddos.php in step with these groups.
|
| A crawler that matches a named group ignores the "*" group, so each named
| group repeats the private disallows.
|
*/

return [
    'disallow' => [
        '/admin/',
        '/profile/',
        '/login',
        '/register',
        '/password/',
        '/api/catalog/',
        // Auth-only action links (add/remove from wishlist) rendered on public cards.
        '/wishlist/',
        // Query params that only make noindexed or canonicalised duplicates: geo searches
        // (one URL per place), the old per-guide filter, legacy destination links and guest
        // counts. Google knew thousands of these and kept recrawling them, while hundreds of
        // tour pages sat in "Discovered – currently not indexed". Longer than "Allow: /guidings",
        // so these win under Google's longest-match rule. Never put a param here that a sitemap
        // or canonical URL uses.
        '/*placeLat=',
        '/*guide_id=',
        '/*from_destination=',
        '/*num_guests=',
    ],

    // SEO audit tools are useful but crawl hard, and they honour Crawl-delay.
    // Not set on "*": Bing honours it too and would crawl slower.
    'slow' => [
        'crawl_delay' => 2,
        'agents' => ['AhrefsBot', 'SemrushBot', 'DataForSeoBot', 'Baiduspider', 'YandexBot'],
    ],

    // Crawlers that only collect training data or backlink data for resale.
    // They send no visitors, so they get nothing. AI search/answer bots
    // (OAI-SearchBot, ChatGPT-User, PerplexityBot, Claude-SearchBot) stay allowed
    // because they cite and link pages.
    'blocked' => [
        'GPTBot',
        'CCBot',
        'ClaudeBot',
        'anthropic-ai',
        'Bytespider',
        'meta-externalagent',
        'cohere-ai',
        'Diffbot',
        'PetalBot',
        'MJ12bot',
        'DotBot',
        'BLEXBot',
        'Barkrowler',
    ],
];
