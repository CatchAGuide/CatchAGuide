<?php

use App\Models\Faq;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Correct accidental clone of vacation country content into trips/camps scopes.
 * Vacations, trips, and camps must remain independent category page content.
 */
return new class extends Migration
{
    public function up(): void
    {
        $vacationRows = Language::query()
            ->where('type', 'geo_country')
            ->where('scope', 'vacations')
            ->get(['source_id', 'language', 'title', 'sub_title', 'introduction', 'content', 'faq_title']);

        foreach ($vacationRows as $vacation) {
            Language::query()
                ->where('type', 'geo_country')
                ->whereIn('scope', ['trips', 'camps'])
                ->where('source_id', $vacation->source_id)
                ->where('language', $vacation->language)
                ->where('title', $vacation->title)
                ->where('sub_title', $vacation->sub_title)
                ->where('introduction', $vacation->introduction)
                ->where('content', $vacation->content)
                ->where('faq_title', $vacation->faq_title)
                ->delete();
        }

        $faqIds = DB::table('faqs as cloned')
            ->join('faqs as vacation', function ($join) {
                $join->on('cloned.source_id', '=', 'vacation.source_id')
                    ->on('cloned.language', '=', 'vacation.language')
                    ->on('cloned.question', '=', 'vacation.question')
                    ->on('cloned.answer', '=', 'vacation.answer');
            })
            ->where('cloned.page', 'geo_country')
            ->where('vacation.page', 'geo_country')
            ->whereIn('cloned.scope', ['trips', 'camps'])
            ->where('vacation.scope', 'vacations')
            ->pluck('cloned.id');

        if ($faqIds->isNotEmpty()) {
            Faq::query()->whereIn('id', $faqIds)->delete();
        }
    }

    public function down(): void
    {
        // Non-destructive: intentional removal of incorrect clones.
    }
};
