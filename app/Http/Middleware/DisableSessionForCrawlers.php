<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevent crawlers from creating database/file sessions.
 *
 * Facebook's link preview bot (and similar crawlers) hit many URLs without
 * cookies. With SESSION_DRIVER=database, each request inserts a sessions row
 * and can lock/saturate MySQL under a crawl spike.
 *
 * Switching to the array driver before StartSession keeps pages readable
 * (OG tags, etc.) without persisting a session.
 */
class DisableSessionForCrawlers
{
    /**
     * User-agent substrings that should never get a persisted session.
     *
     * @var list<string>
     */
    /**
     * Clear crawler-only agents. Do not use bare "WhatsApp" — Android
     * in-app browsers often include it and still need real sessions
     * (checkout, login, locale).
     *
     * @var list<string>
     */
    private const CRAWLER_AGENTS = [
        'facebookexternalhit',
        'Facebot',
        'meta-externalagent',
        'Twitterbot',
        'LinkedInBot',
        'Slackbot-LinkExpanding',
        'TelegramBot',
        'Discordbot',
        'Googlebot',
        'bingbot',
        'Baiduspider',
        'YandexBot',
        'DuckDuckBot',
        'Applebot',
        'PetalBot',
        'SemrushBot',
        'AhrefsBot',
        'DotBot',
        'MJ12bot',
        'Bytespider',
        'GPTBot',
        'ChatGPT-User',
        'ClaudeBot',
        'anthropic-ai',
        'CCBot',
        'ia_archiver',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isCrawler($request)) {
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }

    private function isCrawler(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';

        if ($userAgent === '') {
            return false;
        }

        // WhatsApp preview fetcher is usually a short UA without Mozilla.
        // Real WhatsApp in-app browsers are Mozilla/* and must keep sessions.
        if (
            stripos($userAgent, 'WhatsApp') !== false
            && stripos($userAgent, 'Mozilla') === false
        ) {
            return true;
        }

        foreach (self::CRAWLER_AGENTS as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }
}
