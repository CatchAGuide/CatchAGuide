<?php

namespace App\Services\Guide\Area;

use App\Enums\CertificateStatus;
use App\Enums\GuideType;
use App\Models\GuideCertificate;
use App\Models\GuideTag;
use App\Models\Guiding;
use App\Models\ICalFeed;
use App\Models\User;
use App\Support\CountryList;
use Illuminate\Support\Facades\Cache;

/**
 * Optimization score (spec 2.7): a fixed list of checks per guide with pass/fail and a link to
 * fix each one. Feeds the To-dos page, the Overview card and the sidebar badge. Payment
 * methods for guests are not part of it; drafts and deactivated tours are left out.
 *
 * Not checked yet: "Excluded services" (the tour editor has no such field).
 */
class GuideOptimizationService
{
    public const ABOUT_MIN = 150;

    public const PHOTOS_MIN = 7;

    private const CACHE_MINUTES = 5;

    /** Order the Overview shows open checks in (spec 4.2). */
    public const PRIORITY = ['payment', 'calendar', 'tours', 'profile'];

    /**
     * @return array{score: int, open: int, sections: array<string, array{key: string, score: int, checks: list<array>, tours?: list<array>}>}
     */
    public function evaluate(User $guide, bool $fresh = false): array
    {
        $key = 'guide-area:optimization:'.$guide->id;
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(self::CACHE_MINUTES), fn () => $this->compute($guide->fresh(['information'])));
    }

    public function forget(User $guide): void
    {
        Cache::forget('guide-area:optimization:'.$guide->id);
    }

    /**
     * Failed checks across sections in the Overview's priority order.
     *
     * @return list<array{section: string, context: ?string, label: string, hint: ?string, fix_url: string}>
     */
    public function openChecks(array $result, ?int $limit = null): array
    {
        $open = [];
        foreach (self::PRIORITY as $section) {
            $data = $result['sections'][$section] ?? null;
            if (! $data) {
                continue;
            }
            foreach ($data['checks'] as $check) {
                if (! $check['passed']) {
                    $open[] = ['section' => $section, 'context' => null] + $check;
                }
            }
            foreach ($data['tours'] ?? [] as $tour) {
                foreach ($tour['checks'] as $check) {
                    if (! $check['passed']) {
                        $open[] = ['section' => $section, 'context' => $tour['title']] + $check;
                    }
                }
            }
        }

        return $limit ? array_slice($open, 0, $limit) : $open;
    }

    private function compute(User $guide): array
    {
        $sections = [
            'profile' => $this->section('profile', $this->profileChecks($guide)),
            'tours' => $this->toursSection($guide),
            'payment' => $this->section('payment', $this->paymentChecks($guide)),
            'calendar' => $this->section('calendar', [
                $this->check('calendar_connected', ICalFeed::query()->where('user_id', $guide->id)->where('is_active', true)->exists(), route('guide.area.calendar').'#calendar-sync'),
            ]),
        ];

        $passed = 0;
        $total = 0;
        foreach ($sections as $section) {
            foreach ($this->allChecks($section) as $check) {
                $total++;
                $passed += $check['passed'] ? 1 : 0;
            }
        }

        return [
            'score' => $total > 0 ? (int) round($passed / $total * 100) : 100,
            'open' => $total - $passed,
            'sections' => $sections,
        ];
    }

    /** @return list<array> */
    private function profileChecks(User $guide): array
    {
        $info = $guide->information;
        $profile = route('guide.area.profile');
        $tags = GuideTag::query()->where('user_id', $guide->id)->get(['kind'])->groupBy('kind');
        $about = trim((string) ($info?->about_me ?? ''));
        $isCompany = $guide->guide_type === GuideType::COMPANY;

        $checks = [
            $this->check('photo', filled($guide->profil_image), $profile.'#ga-photo-title'),
            $this->check('display_name', filled($info?->display_name), $profile.'#ga-display-name'),
            $this->check('languages', filled($info?->languages), $profile.'#ga-fishing-title'),
            $this->check($isCompany ? 'about_us' : 'about_me', mb_strlen($about) >= self::ABOUT_MIN, $profile.'#ga-about', ['min' => self::ABOUT_MIN]),
        ];

        if ($isCompany) {
            $checks[] = $this->check('founded_crew', filled($info?->founded_year) && (int) ($info?->crew_size ?? 0) > 0, $profile.'#ga-founded');
        } else {
            $checks[] = $this->check('fish_since', filled($info?->favorite_fish) && filled($info?->fishing_start_year), $profile.'#ga-fish');
        }

        $checks[] = $this->check('specialties', $tags->has(GuideTag::KIND_SPECIALTY), $profile.'#ga-fishing-title');
        $checks[] = $this->check('good_for', $tags->has(GuideTag::KIND_GOOD_FOR), $profile.'#ga-fishing-title');
        $checks[] = $this->check('certificates', GuideCertificate::query()->where('user_id', $guide->id)->where('status', '!=', CertificateStatus::Rejected->value)->exists(), $profile.'#certificates');

        return $checks;
    }

    private function toursSection(User $guide): array
    {
        $tours = Guiding::query()->where('user_id', $guide->id)->where('status', 1)->orderBy('title')->get();

        $blocks = $tours->map(function (Guiding $tour) {
            $edit = route('guidings.edit_newguiding', $tour->id);
            $gallery = $tour->gallery_images;
            $gallery = is_string($gallery) ? (json_decode($gallery, true) ?? []) : (array) $gallery;
            $photos = count(array_filter($gallery, static fn ($v) => filled($v)));

            $checks = [];
            if ((int) $tour->is_boat === 1) {
                $checks[] = $this->check('boat_description', $this->hasItems($tour->getRawOriginal('boat_information')), $edit);
            }
            $checks[] = $this->check('photos', $photos >= self::PHOTOS_MIN, $edit, ['count' => $photos, 'min' => self::PHOTOS_MIN]);
            $checks[] = $this->check('availability', $this->hasItems($tour->getRawOriginal('months')) || $this->hasItems($tour->getRawOriginal('weekdays')), $edit);
            $checks[] = $this->check('additional_details', collect(['requirements', 'recommendations', 'other_information'])->contains(fn (string $f) => $this->hasItems($tour->{$f})), $edit);
            $checks[] = $this->check('included_services', $this->hasItems($tour->inclusions), $edit);

            $passed = count(array_filter($checks, fn (array $c) => $c['passed']));

            return [
                'id' => (int) $tour->id,
                'title' => (string) $tour->title,
                'image' => get_featured_image_link($tour) ?: null,
                'score' => count($checks) ? (int) round($passed / count($checks) * 100) : 100,
                'checks' => $checks,
            ];
        })->values()->all();

        $section = $this->section('tours', []);
        $section['tours'] = $blocks;
        $all = array_merge(...array_map(fn (array $t) => $t['checks'], $blocks ?: [['checks' => []]]));
        $section['score'] = $all === [] ? 100 : (int) round(count(array_filter($all, fn ($c) => $c['passed'])) / count($all) * 100);

        return $section;
    }

    /** @return list<array> */
    private function paymentChecks(User $guide): array
    {
        $info = $guide->information;
        $profile = route('guide.area.profile');
        $account = route('profile.settings');
        $isCompany = $guide->guide_type === GuideType::COMPANY;

        // Complete address per account type (spec 2.7), set in Account & security.
        $address = filled($info?->address) && filled($info?->address_number) && filled($info?->postal) && filled($info?->city)
            && CountryList::normalize($info?->country) !== null
            && ($isCompany ? filled($info?->company_name) && filled($info?->legal_form) : filled($info?->birthday));

        return [
            $this->check('bank_details', filled($guide->banktransfer_iban) && filled($guide->banktransfer_holder), $profile.'#ga-pay-title'),
            $this->check('billing_address', $address, $account.'#billing'),
            $this->check('vat', filled($guide->tax_id) || filled($info?->tax_number) || (bool) $info?->vat_not_registered, $account.'#billing'),
        ];
    }

    /** @param  list<array>  $checks */
    private function section(string $key, array $checks): array
    {
        $passed = count(array_filter($checks, fn (array $c) => $c['passed']));

        return ['key' => $key, 'score' => $checks === [] ? 100 : (int) round($passed / count($checks) * 100), 'checks' => $checks];
    }

    /** @return list<array> */
    private function allChecks(array $section): array
    {
        $checks = $section['checks'];
        foreach ($section['tours'] ?? [] as $tour) {
            $checks = array_merge($checks, $tour['checks']);
        }

        return $checks;
    }

    private function check(string $key, bool $passed, string $fixUrl, array $replace = []): array
    {
        return [
            'key' => $key,
            'passed' => $passed,
            'label' => __('guide_area.todos.checks.'.$key.'.label', $replace),
            'hint' => __('guide_area.todos.checks.'.$key.'.hint', $replace),
            'fix_label' => __('guide_area.todos.checks.'.$key.'.fix'),
            'fix_url' => $fixUrl,
        ];
    }

    private function hasItems(mixed $value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, static fn ($v) => filled($v))) > 0;
        }
        if (! is_string($value) || trim($value) === '') {
            return false;
        }
        $decoded = json_decode($value, true);

        return is_array($decoded) ? count(array_filter($decoded, static fn ($v) => filled($v))) > 0 : trim(strip_tags($value)) !== '';
    }
}
