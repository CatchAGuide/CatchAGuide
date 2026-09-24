<?php

use App\Support\Text\MojibakeRepair;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repairs double/triple-encoded UTF-8 ("MÃ¶glichkeiten", "â€“") in user-visible text columns. It
 * surfaced as garbled Google snippets: the magazine and guide-article meta descriptions come
 * straight from these excerpts. Only runs that convert back to valid UTF-8 are changed; anything
 * ambiguous is left as it is.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'threads' => ['title', 'excerpt', 'body'],
        'guide_threads' => ['title', 'excerpt', 'introduction', 'body'],
        'languages' => ['title', 'sub_title', 'introduction', 'content'],
        'guidings' => ['title', 'description'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)
                    // Binary match: the default collation treats "Ã" as "A".
                    ->whereRaw("CAST(`{$column}` AS BINARY) LIKE ? OR CAST(`{$column}` AS BINARY) LIKE ? OR CAST(`{$column}` AS BINARY) LIKE ?", [
                        "%\u{00C3}%", "%\u{00E2}\u{20AC}%", "%\u{00C2}%",
                    ])
                    ->orderBy('id')
                    ->select(['id', $column])
                    ->chunkById(200, function ($rows) use ($table, $column) {
                        foreach ($rows as $row) {
                            $repaired = MojibakeRepair::repair($row->{$column});
                            if ($repaired !== $row->{$column}) {
                                DB::table($table)->where('id', $row->id)->update([$column => $repaired]);
                            }
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        // Not reversible: re-breaking the encoding has no use.
    }
};
