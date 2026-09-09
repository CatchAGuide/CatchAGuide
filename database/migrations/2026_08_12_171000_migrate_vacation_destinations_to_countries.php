<?php

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Destination;
use App\Models\DestinationFaq;
use App\Models\Faq;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        $destinations = Destination::where('type', 'vacations')->get();

        $grouped = $destinations->groupBy(fn (Destination $d) => strtolower(str_replace(' ', '-', $d->name)));

        foreach ($grouped as $slug => $rows) {
            $primary = $rows->firstWhere('language', 'de') ?? $rows->first();
            $country = Country::where('slug', $slug)->orWhere('name', $primary->name)->first();

            if (! $country) {
                $country = Country::create([
                    'name' => $primary->name,
                    'slug' => $slug,
                    'countrycode' => $primary->countrycode,
                    'thumbnail_path' => $primary->thumbnail_path,
                    'filters' => is_string($primary->filters) ? json_decode($primary->filters, true) : $primary->filters,
                ]);

                foreach ($rows as $dest) {
                    CountryTranslation::firstOrCreate([
                        'country_id' => $country->id,
                        'language' => $dest->language ?? 'de',
                    ], [
                        'title' => $dest->title ?? '',
                        'sub_title' => $dest->sub_title ?? '',
                        'introduction' => $dest->introduction ?? '',
                        'content' => $dest->content ?? '',
                        'fish_avail_title' => $dest->fish_avail_title ?? '',
                        'fish_avail_intro' => $dest->fish_avail_intro ?? '',
                        'size_limit_title' => $dest->size_limit_title ?? '',
                        'size_limit_intro' => $dest->size_limit_intro ?? '',
                        'time_limit_title' => $dest->time_limit_title ?? '',
                        'time_limit_intro' => $dest->time_limit_intro ?? '',
                        'faq_title' => $dest->faq_title ?? '',
                    ]);
                }
            }

            foreach ($rows as $dest) {
                $locale = $dest->language ?? 'de';

                // Vacation destinations only feed the vacations scope.
                // Trips and camps keep separate empty scopes until edited.
                $existing = Language::where('source_id', (string) $country->id)
                    ->where('type', 'geo_country')
                    ->where('scope', 'vacations')
                    ->where('language', $locale)
                    ->first();

                if (! $existing && (filled($dest->title) || filled($dest->introduction))) {
                    Language::create([
                        'source_id' => (string) $country->id,
                        'type' => 'geo_country',
                        'scope' => 'vacations',
                        'language' => $locale,
                        'title' => $dest->title ?? '',
                        'sub_title' => $dest->sub_title ?? '',
                        'introduction' => $dest->introduction ?? '',
                        'content' => $dest->content ?? '',
                        'faq_title' => $dest->faq_title ?? '',
                    ]);
                }

                $destFaqs = DestinationFaq::where('destination_id', $dest->id)
                    ->where('language', $locale)
                    ->get();

                foreach ($destFaqs as $faq) {
                    $exists = Faq::where('source_id', $country->id)
                        ->where('page', 'geo_country')
                        ->where('scope', 'vacations')
                        ->where('language', $locale)
                        ->where('question', $faq->question)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    Faq::create([
                        'source_id' => $country->id,
                        'page' => 'geo_country',
                        'scope' => 'vacations',
                        'language' => $locale,
                        'question' => $faq->question ?? '',
                        'answer' => $faq->answer ?? '',
                    ]);
                }
            }

            Log::info("Migrated vacation destination '{$primary->name}' -> country#{$country->id}");
        }
    }

    public function down(): void
    {
        Language::where('type', 'geo_country')
            ->where('scope', 'vacations')
            ->delete();

        Faq::where('page', 'geo_country')
            ->where('scope', 'vacations')
            ->delete();
    }
};
