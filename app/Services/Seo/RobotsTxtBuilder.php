<?php

namespace App\Services\Seo;

/**
 * Host-aware robots.txt. Each domain advertises only its own sitemap and
 * magazine path; crawler groups come from config/robots.php.
 */
class RobotsTxtBuilder
{
    public function build(string $host): string
    {
        $host = str_replace('www.', '', $host);
        $disallow = array_map(fn (string $path) => "Disallow: {$path}", config('robots.disallow', []));

        $lines = [
            'User-agent: *',
            'Allow: /',
            ...$disallow,
            'Allow: /guidings',
            'Allow: /vacations',
            $host === 'catchaguide.de' ? 'Allow: /angelmagazin/' : 'Allow: /fishing-magazine/',
        ];

        $slowAgents = config('robots.slow.agents', []);
        if ($slowAgents !== []) {
            $lines[] = '';
            $lines[] = '# SEO tools: welcome, but slowed down';
            foreach ($slowAgents as $agent) {
                $lines[] = "User-agent: {$agent}";
            }
            $lines[] = 'Crawl-delay: '.(int) config('robots.slow.crawl_delay', 2);
            array_push($lines, ...$disallow);
        }

        $blockedAgents = config('robots.blocked', []);
        if ($blockedAgents !== []) {
            $lines[] = '';
            $lines[] = '# AI training and data-resale crawlers: they send no visitors';
            foreach ($blockedAgents as $agent) {
                $lines[] = "User-agent: {$agent}";
            }
            $lines[] = 'Disallow: /';
        }

        $lines[] = '';
        $lines[] = "Sitemap: https://{$host}/sitemap.xml";
        $lines[] = '';

        return implode("\n", $lines);
    }
}
