<?php

namespace App\Presenters\Guiding;

use App\Enums\CertificateStatus;
use App\Models\GuideCertificate;
use App\Models\GuideTag;
use App\Models\User;

/**
 * The guide block guests see on a tour page: the name the guide chose in the Guide Area,
 * their specialty / good-for tags and the documents Catch A Guide has verified. Guides who
 * never used the Guide Area fall back to their first name and show no tags or documents.
 */
class GuidePublicProfilePresenter
{
    /**
     * @return array{name: string, specialties: array<int, string>, good_for: array<int, string>, verified_documents: array<int, string>}
     */
    public function present(User $guide): array
    {
        $tags = $guide->guideTags()->get(['id', 'user_id', 'kind', 'value', 'is_custom', 'position']);

        $verified = $guide->guideCertificates()
            ->where('status', CertificateStatus::Verified->value)
            ->orderBy('type')
            ->get(['id', 'user_id', 'type', 'title', 'valid_until', 'status'])
            ->reject(fn (GuideCertificate $certificate) => $certificate->isExpired())
            ->map(fn (GuideCertificate $certificate) => $certificate->label())
            ->unique()
            ->values()
            ->all();

        return [
            'name' => $this->name($guide),
            'specialties' => $this->labels($tags, GuideTag::KIND_SPECIALTY),
            'good_for' => $this->labels($tags, GuideTag::KIND_GOOD_FOR),
            'verified_documents' => $verified,
        ];
    }

    public function name(User $guide): string
    {
        $displayName = trim((string) ($guide->information?->display_name ?? ''));

        return $displayName !== '' ? $displayName : (string) $guide->firstname;
    }

    /**
     * @param  iterable<GuideTag>  $tags
     * @return array<int, string>
     */
    private function labels(iterable $tags, string $kind): array
    {
        return collect($tags)
            ->where('kind', $kind)
            ->map(fn (GuideTag $tag) => $tag->label())
            ->values()
            ->all();
    }
}
