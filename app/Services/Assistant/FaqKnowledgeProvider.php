<?php

namespace App\Services\Assistant;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;

final class FaqKnowledgeProvider
{
    /**
     * @return array<int, array{question: string, answer: string, page: string|null}>
     */
    public function search(string $query, int $limit): array
    {
        $limit = max(1, min($limit, (int) config('booking_assistant.max_tool_rows', 8)));
        $needle = mb_strtolower(trim($query));
        if ($needle === '') {
            return [];
        }

        $locale = app()->getLocale();

        /** @var Builder $q */
        $q = Faq::query()
            ->where(function (Builder $inner) use ($locale): void {
                $inner->where('language', $locale)
                    ->orWhereNull('language')
                    ->orWhere('language', '');
            });

        $tokens = preg_split('/\s+/', $needle, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $needleLike = '%' . $this->escapeLike($needle) . '%';

        $q->where(function (Builder $inner) use ($needleLike, $tokens): void {
            $inner->whereRaw('LOWER(question) LIKE ?', [$needleLike])
                ->orWhereRaw('LOWER(answer) LIKE ?', [$needleLike]);
            foreach ($tokens as $token) {
                if (mb_strlen($token) < 2) {
                    continue;
                }
                $like = '%' . $this->escapeLike($token) . '%';
                $inner->orWhereRaw('LOWER(question) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(answer) LIKE ?', [$like]);
            }
        });

        $rows = $q->orderBy('id')->limit(50)->get();

        $scored = [];
        foreach ($rows as $faq) {
            $qText = mb_strtolower($faq->question . ' ' . $faq->answer);
            $score = 0;
            if (str_contains($qText, $needle)) {
                $score += 10;
            }
            foreach ($tokens as $token) {
                if (mb_strlen($token) < 2) {
                    continue;
                }
                if (str_contains($qText, $token)) {
                    $score += 2;
                }
            }
            if ($score > 0) {
                $scored[] = ['score' => $score, 'faq' => $faq];
            }
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $out = [];
        foreach (array_slice($scored, 0, $limit) as $item) {
            $faq = $item['faq'];
            $out[] = [
                'question' => (string) $faq->question,
                'answer' => $this->truncateAnswer((string) $faq->answer, 400),
                'page' => $faq->page ?? null,
            ];
        }

        return $out;
    }

    private function truncateAnswer(string $html, int $max): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');

        return mb_strlen($plain) <= $max ? $plain : mb_substr($plain, 0, $max) . '…';
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
