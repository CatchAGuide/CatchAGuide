<?php

namespace App\Services\Assistant;

use App\Models\Thread;

final class BlogIndexProvider
{
    /**
     * @return array<int, array{title: string, url: string, excerpt: string|null}>
     */
    public function search(string $query, int $limit): array
    {
        $limit = max(1, min($limit, (int) config('booking_assistant.max_tool_rows', 8)));
        $needle = mb_strtolower(trim($query));
        if ($needle === '') {
            return [];
        }

        $locale = app()->getLocale();
        $threads = Thread::query()
            ->where('language', $locale)
            ->orderByDesc('id')
            ->limit(300)
            ->get(['id', 'title', 'slug', 'excerpt']);

        $tokens = preg_split('/\s+/', $needle, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $scored = [];
        foreach ($threads as $thread) {
            $hay = mb_strtolower($thread->title . ' ' . ($thread->excerpt ?? ''));
            $score = 0;
            if (str_contains($hay, $needle)) {
                $score += 10;
            }
            foreach ($tokens as $token) {
                if (mb_strlen($token) < 2) {
                    continue;
                }
                if (str_contains($hay, $token)) {
                    $score += 3;
                }
            }
            if ($score > 0) {
                $scored[] = ['score' => $score, 'thread' => $thread];
            }
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $out = [];
        foreach (array_slice($scored, 0, $limit) as $item) {
            $thread = $item['thread'];
            $out[] = [
                'title' => (string) $thread->title,
                'url' => $this->threadUrl($thread),
                'excerpt' => $thread->excerpt ? mb_substr(strip_tags($thread->excerpt), 0, 200) : null,
            ];
        }

        return $out;
    }

    private function threadUrl(Thread $thread): string
    {
        try {
            if (app()->getLocale() === 'de') {
                return route('blogde.thread.show', ['slug' => $thread->slug]);
            }

            return route('blog.thread.show', ['slug' => $thread->slug]);
        } catch (\Throwable) {
            $prefix = app()->getLocale() === 'de' ? '/angelmagazin/' : '/fishing-magazine/';

            return url($prefix . $thread->slug);
        }
    }
}
