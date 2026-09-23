<?php

namespace App\Services\Sitemap;

use App\Models\Language;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * lastmod values from real content timestamps. A facet page's date is the newest of its own
 * row and its scoped CMS copy (the `languages` table) — never the generation run, which Google
 * learns to ignore domain-wide when every URL shares it.
 */
class SitemapLastmod
{
    /** @var array<string, string>|null "type|scope|source_id|language" => max updated_at */
    private ?array $contentIndex = null;

    public function forContent(string $type, string $scope, int|string $sourceId, string $lang, mixed ...$others): ?string
    {
        $key = implode('|', [$type, $scope, (string) $sourceId, $lang]);

        return $this->newest($this->contentIndex()[$key] ?? null, ...$others);
    }

    /**
     * Newest of the given timestamps (Carbon/strings/null) as an ISO-8601 string, or null.
     */
    public function newest(mixed ...$timestamps): ?string
    {
        $newest = null;
        foreach ($timestamps as $timestamp) {
            if ($timestamp === null || $timestamp === '') {
                continue;
            }
            $value = $timestamp instanceof CarbonInterface ? $timestamp : Carbon::parse((string) $timestamp);
            if ($newest === null || $value->greaterThan($newest)) {
                $newest = $value;
            }
        }

        return $newest?->toAtomString();
    }

    /**
     * @return array<string, string>
     */
    private function contentIndex(): array
    {
        if ($this->contentIndex !== null) {
            return $this->contentIndex;
        }

        $this->contentIndex = [];
        Language::query()
            ->whereNotNull('scope')
            ->selectRaw('type, scope, source_id, language, MAX(updated_at) as last_updated')
            ->groupBy('type', 'scope', 'source_id', 'language')
            ->toBase()
            ->get()
            ->each(function ($row) {
                $this->contentIndex[implode('|', [$row->type, $row->scope, (string) $row->source_id, $row->language])] = $row->last_updated;
            });

        return $this->contentIndex;
    }
}
