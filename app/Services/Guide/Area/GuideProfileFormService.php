<?php

namespace App\Services\Guide\Area;

use App\Enums\GuideType;
use App\Models\GuideTag;
use App\Models\User;
use App\Models\UserInformation;
use App\Rules\Iban;
use Illuminate\Support\Facades\DB;

/**
 * Loads and saves the Guide Area profile form (Guide\Profile Livewire page).
 * Writes the same columns the old /profile/guide-profile page uses, so both stay consistent
 * during the beta, and keeps users.banktransferdetails in sync with the structured fields.
 */
class GuideProfileFormService
{
    public function __construct(
        private readonly GuideLanguageCatalog $languages,
        private readonly GuideTagService $tags,
    ) {}

    public function isCompany(User $user): bool
    {
        return $user->guide_type === GuideType::COMPANY;
    }

    /** @return array<string, mixed> */
    public function state(User $user): array
    {
        $info = $user->information;
        $tags = $this->tags->valuesFor($user);
        $hasStructuredBank = filled($user->banktransfer_iban);

        return [
            'displayName' => (string) ($info?->display_name ?: $this->defaultDisplayName($user)),
            'languages' => $this->languages->parse($info?->languages),
            'aboutMe' => (string) ($info?->about_me ?? ''),
            'favoriteFish' => (string) ($info?->favorite_fish ?? ''),
            'fishingSince' => $info?->fishing_start_year ? (string) $info->fishing_start_year : '',
            'crewSize' => $info?->crew_size ? (string) $info->crew_size : '',
            'foundedYear' => $info?->founded_year ? (string) $info->founded_year : '',
            'specialties' => $tags[GuideTag::KIND_SPECIALTY],
            'goodFor' => $tags[GuideTag::KIND_GOOD_FOR],
            'barAllowed' => (bool) $user->bar_allowed,
            'bankAllowed' => (bool) $user->banktransfer_allowed,
            'paypalAllowed' => (bool) $user->paypal_allowed,
            'bankHolder' => (string) ($user->banktransfer_holder ?? trim($user->firstname.' '.$user->lastname)),
            'bankIban' => $hasStructuredBank ? Iban::format($user->banktransfer_iban) : $this->ibanFromText($user->banktransferdetails),
            'bankBic' => (string) ($user->banktransfer_bic ?? ''),
            'paypalDetails' => (string) ($user->paypaldetails ?? ''),
            // Old free text, shown once as a hint until structured fields are saved.
            'legacyBankDetails' => $hasStructuredBank ? '' : (string) ($user->banktransferdetails ?? ''),
        ];
    }

    /**
     * The longest About me allowed: the configured limit, or the guide's existing text length
     * so a profile written under the old 5,000-character limit can still be saved.
     */
    public function aboutMeMax(User $user): int
    {
        $limit = (int) config('guide_area.profile.about_me_max', 1000);

        return max($limit, mb_strlen((string) $user->information?->about_me));
    }

    /** @param  array<string, mixed>  $data  validated form data */
    public function save(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $info = $user->information ?? new UserInformation;

            $info->fill([
                'display_name' => trim((string) $data['displayName']),
                'languages' => $this->languages->format((array) $data['languages']),
                'about_me' => trim((string) $data['aboutMe']),
                'favorite_fish' => trim((string) $data['favoriteFish']),
                'fishing_start_year' => (int) $data['fishingSince'],
            ]);

            if ($this->isCompany($user)) {
                $info->fill([
                    'crew_size' => filled($data['crewSize'] ?? null) ? (int) $data['crewSize'] : null,
                    'founded_year' => filled($data['foundedYear'] ?? null) ? (int) $data['foundedYear'] : null,
                ]);
            }

            $info->save();
            if (! $user->information) {
                $user->information()->associate($info);
            }

            $user->bar_allowed = (bool) $data['barAllowed'];
            $user->banktransfer_allowed = (bool) $data['bankAllowed'];
            $user->paypal_allowed = (bool) $data['paypalAllowed'];
            $user->paypaldetails = filled($data['paypalDetails'] ?? null) ? trim((string) $data['paypalDetails']) : null;

            if (filled($data['bankIban'] ?? null)) {
                $user->banktransfer_holder = trim((string) $data['bankHolder']);
                $user->banktransfer_iban = Iban::normalize($data['bankIban']);
                $user->banktransfer_bic = filled($data['bankBic'] ?? null) ? strtoupper(preg_replace('/\s+/', '', (string) $data['bankBic'])) : null;
                $user->banktransferdetails = $this->bankDetailsText($user->banktransfer_holder, $user->banktransfer_iban, $user->banktransfer_bic);
            }

            $user->save();

            $this->tags->sync($user, GuideTag::KIND_SPECIALTY, (array) ($data['specialties'] ?? []));
            $this->tags->sync($user, GuideTag::KIND_GOOD_FOR, (array) ($data['goodFor'] ?? []));
        });
    }

    /** One line, language-neutral: printed as-is in the guest booking confirmation mail. */
    public function bankDetailsText(string $holder, string $iban, ?string $bic): string
    {
        return collect([$holder, 'IBAN '.Iban::format($iban), $bic ? 'BIC '.$bic : null])
            ->filter()
            ->implode(' · ');
    }

    private function defaultDisplayName(User $user): string
    {
        if ($this->isCompany($user) && filled($user->information?->company_name)) {
            return (string) $user->information->company_name;
        }

        return (string) $user->firstname;
    }

    private function ibanFromText(?string $text): string
    {
        if (! $text || ! preg_match('/\b([A-Z]{2}\d{2}(?:\s?[A-Z0-9]{4}){2,7}(?:\s?[A-Z0-9]{1,4})?)\b/i', $text, $m)) {
            return '';
        }

        return Iban::isValid($m[1]) ? Iban::format($m[1]) : '';
    }
}
