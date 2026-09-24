<?php

namespace App\Console\Commands;

use App\Models\CategoryPage;
use App\Models\Method;
use App\Models\Target;
use Illuminate\Console\Command;

/**
 * Found live on production during the Sept 2026 SEO audit: CategoryPage rows can end up with a
 * `type` that is neither 'Targets' nor 'Methods' (e.g. a fish species slug like 'karpfen'), with
 * no route able to serve them. CategorySitemapContributor now excludes these from the sitemap
 * (see tests/Unit/Sitemap/CategorySitemapContributorTest.php), which stops Google being sent dead
 * URLs — but the bad rows themselves still need fixing at the data level. This command finds them
 * and, with --fix, repairs the ones it can confidently infer (source_id matches a real Target or
 * Method row) rather than guessing at the rest.
 *
 * Read-only by default. Run with --fix to apply repairs; anything it can't confidently infer is
 * left alone and listed for manual review.
 */
class AuditCategoryPageTypes extends Command
{
    protected $signature = 'category-pages:audit-types {--fix : Repair rows whose correct type can be confidently inferred from source_id}';

    protected $description = 'Find CategoryPage rows with a type other than Targets/Methods (dead sitemap/404 source) and optionally repair them';

    public function handle(): int
    {
        $badRows = CategoryPage::query()
            ->whereRaw('LOWER(type) NOT IN (?, ?)', ['methods', 'targets'])
            ->get();

        if ($badRows->isEmpty()) {
            $this->info('No CategoryPage rows with an unexpected type found.');

            return self::SUCCESS;
        }

        $this->warn("{$badRows->count()} CategoryPage row(s) with an unexpected type (no route can serve these):");

        $fix = (bool) $this->option('fix');
        $fixed = 0;
        $needsReview = [];

        foreach ($badRows as $row) {
            $inferredType = null;

            if ($row->source_id !== null) {
                // `targets` and `methods` are separate auto-increment id spaces, so a given
                // source_id can legitimately exist in both tables at once — that's not evidence
                // either way, so treat it as unresolvable rather than guessing.
                $matchesTarget = Target::whereKey($row->source_id)->exists();
                $matchesMethod = Method::whereKey($row->source_id)->exists();

                if ($matchesTarget && ! $matchesMethod) {
                    $inferredType = 'Targets';
                } elseif ($matchesMethod && ! $matchesTarget) {
                    $inferredType = 'Methods';
                }
            }

            $this->line(sprintf(
                '  #%d  type=%s  slug=%s  source_id=%s  name=%s  -> %s',
                $row->id,
                $row->type,
                $row->slug,
                $row->source_id ?? 'null',
                $row->name,
                $inferredType ? "infer: {$inferredType}" : 'cannot infer — needs manual review',
            ));

            if ($inferredType === null) {
                $needsReview[] = $row->id;

                continue;
            }

            if ($fix) {
                $row->update(['type' => $inferredType]);
                $fixed++;
            }
        }

        if ($fix) {
            $this->info("Repaired {$fixed} row(s).");
        } else {
            $this->comment('Dry run — pass --fix to apply the repairs above.');
        }

        if ($needsReview !== []) {
            $this->warn('Could not infer a type for row IDs: '.implode(', ', $needsReview).' — source_id does not match any Target or Method; review/delete manually.');
        }

        return self::SUCCESS;
    }
}
