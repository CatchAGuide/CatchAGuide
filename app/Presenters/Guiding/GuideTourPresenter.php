<?php

namespace App\Presenters\Guiding;

use App\Enums\TourStatus;
use App\Models\Guiding;

/**
 * One tour as the Guide Area shows it (My fishing tours page and /api/guide/tours): status,
 * what's still missing before it can go live, and where to edit or view it.
 */
class GuideTourPresenter
{
    /**
     * @return array{id: int, title: string, location: string, status: string, status_label: string,
     *     image: string, price: ?float, price_type: ?string, voucher_enabled: bool, gaps: list<string>,
     *     gap_labels: list<string>, can_go_live: bool, public_url: ?string, edit_url: string,
     *     duplicated_from_id: ?int, updated_at: ?string}
     */
    public function present(Guiding $guiding): array
    {
        $status = TourStatus::fromGuiding($guiding);
        $gaps = $status === TourStatus::Live ? [] : $guiding->publishingGaps();

        return [
            'id' => (int) $guiding->id,
            'title' => (string) ($guiding->title ?: __('guide_area.tours.untitled')),
            'location' => (string) ($guiding->location ?? ''),
            'status' => $status->key(),
            'status_label' => $status->label(),
            'image' => get_featured_image_link($guiding) ?: asset('images/placeholder_guide.webp'),
            'price' => $guiding->price !== null ? (float) $guiding->price : null,
            'price_type' => $guiding->price_type,
            'voucher_enabled' => (bool) $guiding->voucher_enabled,
            'gaps' => $gaps,
            'gap_labels' => array_map(fn (string $gap) => __('guide_area.tours.gaps.'.$gap), $gaps),
            'can_go_live' => $status !== TourStatus::Live && $gaps === [],
            'public_url' => $status === TourStatus::Live && filled($guiding->slug) ? $guiding->publicShowUrl() : null,
            'edit_url' => route('guidings.edit_newguiding', $guiding->id),
            'duplicated_from_id' => $guiding->duplicated_from_id ? (int) $guiding->duplicated_from_id : null,
            'updated_at' => $guiding->updated_at?->toIso8601String(),
        ];
    }
}
