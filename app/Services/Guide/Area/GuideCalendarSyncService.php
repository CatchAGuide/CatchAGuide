<?php

namespace App\Services\Guide\Area;

use App\Models\CalendarSchedule;
use App\Models\ICalFeed;
use App\Models\User;
use App\Models\UserICalFeed;
use App\Services\ICalGeneratorService;
use App\Services\ICalService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Guide Area › Calendar › Calendar sync (spec CA-08/CA-09) on the existing iCal stack:
 * import = ical_feeds (any iCal URL, synced by ICalService, every 2 h by ical:sync-feeds),
 * export = the guide's user_ical_feeds link. Google / Outlook OAuth come later (plan Phase 4).
 */
class GuideCalendarSyncService
{
    public const EXPORT_NAME = 'Catch A Guide';

    public function __construct(
        private readonly ICalService $ical,
        private readonly ICalGeneratorService $generator,
    ) {}

    public function imports(User $guide): Collection
    {
        return ICalFeed::query()->where('user_id', $guide->id)->orderBy('name')->get();
    }

    /**
     * Adds a private calendar by its iCal URL and runs the first sync.
     *
     * @return array{ok: bool, error?: string, feed?: ICalFeed}
     */
    public function addImport(User $guide, string $name, string $url): array
    {
        if (ICalFeed::query()->where('user_id', $guide->id)->where('feed_url', $url)->exists()) {
            return ['ok' => false, 'error' => __('guide_area.calendar.sync.already_added')];
        }

        $check = $this->ical->validateFeedUrl($url);
        if (! ($check['valid'] ?? false)) {
            return ['ok' => false, 'error' => __('guide_area.calendar.sync.invalid_url')];
        }

        $feed = ICalFeed::create([
            'user_id' => $guide->id,
            'name' => mb_substr(trim($name) !== '' ? trim($name) : (parse_url($url, PHP_URL_HOST) ?: 'Calendar'), 0, 255),
            'feed_url' => $url,
            'sync_type' => 'all_events',
            'is_active' => true,
        ]);

        $this->ical->syncFeed($feed);

        return ['ok' => true, 'feed' => $feed->fresh()];
    }

    /** @return array{ok: bool, error?: string} */
    public function syncNow(User $guide, int $feedId): array
    {
        $result = $this->ical->syncFeed($this->ownFeed($guide, $feedId));

        return ($result['success'] ?? false) ? ['ok' => true] : ['ok' => false, 'error' => __('guide_area.calendar.sync.sync_failed')];
    }

    /**
     * Removes a private calendar. Imported busy days from today on are removed with the last
     * connection (rows don't record which feed they came from), otherwise the others re-sync.
     */
    public function disconnect(User $guide, int $feedId): void
    {
        $this->ownFeed($guide, $feedId)->delete();

        CalendarSchedule::query()
            ->where('user_id', $guide->id)
            ->where('source', CalendarSchedule::SOURCE_ICAL)
            ->whereDate('date', '>=', Carbon::today()->toDateString())
            ->delete();

        foreach ($this->imports($guide) as $remaining) {
            $this->ical->syncFeed($remaining, false);
        }
    }

    /** The guide's export link (created on first use). */
    public function export(User $guide): UserICalFeed
    {
        return UserICalFeed::query()
            ->where('user_id', $guide->id)
            ->where('feed_type', 'bookings_only')
            ->where('is_active', true)
            ->orderBy('id')
            ->first()
            ?? $this->generator->createUserFeed($guide, [
                'name' => self::EXPORT_NAME,
                'feed_type' => 'bookings_only',
                'feed_settings' => ['include_pending' => true, 'include_blocks' => false],
            ]);
    }

    /** @param  array{include_pending?: bool, include_blocks?: bool}  $options */
    public function setExportOptions(User $guide, array $options): UserICalFeed
    {
        $feed = $this->export($guide);
        $settings = (array) ($feed->feed_settings ?? []);
        foreach (['include_pending', 'include_blocks'] as $key) {
            if (array_key_exists($key, $options)) {
                $settings[$key] = (bool) $options[$key];
            }
        }
        $feed->update(['feed_settings' => $settings]);

        return $feed;
    }

    public function regenerateExport(User $guide): UserICalFeed
    {
        $feed = $this->export($guide);
        $this->generator->regenerateFeedToken($feed);

        return $feed->fresh();
    }

    private function ownFeed(User $guide, int $feedId): ICalFeed
    {
        $feed = ICalFeed::query()->where('user_id', $guide->id)->find($feedId);
        abort_if($feed === null, 404);

        return $feed;
    }
}
