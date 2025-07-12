<?php

namespace App\Jobs;

use App\Models\Guiding;
use App\Services\CalendarScheduleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveGuidingDraftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $guidingData;
    public $userId;
    public $isUpdate;
    public $guidingId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $guidingData, int $userId, bool $isUpdate = false, ?int $guidingId = null)
    {
        $this->guidingData = $guidingData;
        $this->userId = $userId;
        $this->isUpdate = $isUpdate;
        $this->guidingId = $guidingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            // Find or create guiding
            if ($this->isUpdate && $this->guidingId) {
                $guiding = Guiding::findOrFail($this->guidingId);
            } else {
                $guiding = Guiding::where('user_id', $this->userId)
                    ->where('status', 2)
                    ->where('title', $this->guidingData['title'] ?? '')
                    ->where('city', $this->guidingData['city'] ?? '')
                    ->where('country', $this->guidingData['country'] ?? '')
                    ->where('region', $this->guidingData['region'] ?? '')
                    ->first();

                if (!$guiding) {
                    $guiding = new Guiding(['user_id' => $this->userId]);
                }
            }

            // Fill guiding with data
            $this->fillGuidingFromData($guiding);

            // Generate slug for new guidings
            if (!$this->isUpdate) {
                $guiding->slug = slugify($guiding->title . "-in-" . $guiding->location);
            }

            $guiding->is_newguiding = 1;
            // $guiding->status = 2; // Draft status

            $guiding->save();

            // Refresh the model to ensure we have the latest data including ID
            $guiding->refresh();
            
            // Handle seasonal blocking if needed
            $this->handleSeasonalBlocking($guiding);

            DB::commit();

            Log::info('Draft saved successfully for guiding ID: ' . $guiding->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving draft: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fill guiding model with data
     */
    private function fillGuidingFromData(Guiding $guiding): void
    {
        // Basic fields
        $guiding->location = $this->guidingData['location'] ?? '';
        $guiding->title = $this->guidingData['title'] ?? '';
        $guiding->lat = $this->guidingData['latitude'] ?? '';
        $guiding->lng = $this->guidingData['longitude'] ?? '';
        $guiding->country = $this->guidingData['country'] ?? '';
        $guiding->city = $this->guidingData['city'] ?? '';
        $guiding->region = $this->guidingData['region'] ?? '';

        // Images (processed files paths)
        $guiding->gallery_images = json_encode($this->guidingData['gallery_images'] ?? []);
        $guiding->thumbnail_path = $this->guidingData['thumbnail_path'] ?? '';

        // Boat and fishing info
        $guiding->is_boat = $this->guidingData['is_boat'] ?? 0;
        $guiding->fishing_from_id = $this->guidingData['fishing_from_id'] ?? 2;
        $guiding->additional_information = $this->guidingData['other_boat_info'] ?? '';
        
        if ($guiding->is_boat) {
            $guiding->boat_type = $this->guidingData['boat_type'] ?? '';
            $guiding->boat_information = json_encode($this->guidingData['boat_information'] ?? []);
            $guiding->boat_extras = json_encode($this->guidingData['boat_extras'] ?? []);
        }

        // Target fish, methods, water types
        $guiding->target_fish = json_encode($this->guidingData['target_fish'] ?? []);
        $guiding->fishing_methods = json_encode($this->guidingData['methods'] ?? []);
        $guiding->fishing_type_id = $this->guidingData['style_of_fishing'] ?? 3;
        $guiding->water_types = json_encode($this->guidingData['water_types'] ?? []);

        // Descriptions
        $guiding->desc_course_of_action = $this->guidingData['desc_course_of_action'] ?? '';
        $guiding->desc_meeting_point = $this->guidingData['desc_meeting_point'] ?? '';
        $guiding->meeting_point = $this->guidingData['meeting_point'] ?? '';
        $guiding->desc_starting_time = $this->guidingData['desc_starting_time'] ?? '';
        $guiding->desc_departure_time = json_encode($this->guidingData['desc_departure_time'] ?? []);
        $guiding->desc_tour_unique = $this->guidingData['desc_tour_unique'] ?? '';
        $guiding->description = $this->guidingData['description'] ?? '';

        // Requirements, recommendations, other info
        $guiding->requirements = json_encode($this->guidingData['requirements'] ?? []);
        $guiding->recommendations = json_encode($this->guidingData['recommendations'] ?? []);
        $guiding->other_information = json_encode($this->guidingData['other_information'] ?? []);

        // Tour details
        $guiding->tour_type = $this->guidingData['tour_type'] ?? '';
        $guiding->duration_type = $this->guidingData['duration'] ?? '';
        $guiding->duration = $this->guidingData['duration_value'] ?? 0;
        $guiding->max_guests = $this->guidingData['no_guest'] ?? 0;
        $guiding->min_guests = $this->guidingData['min_guests'] ?? null;

        // Pricing
        $guiding->price_type = $this->guidingData['price_type'] ?? '';
        $guiding->price = $this->guidingData['price'] ?? 0;
        $guiding->prices = json_encode($this->guidingData['prices'] ?? []);
        $guiding->inclusions = json_encode($this->guidingData['inclusions'] ?? []);
        $guiding->pricing_extra = json_encode($this->guidingData['pricing_extra'] ?? []);

        // Booking settings
        $guiding->allowed_booking_advance = $this->guidingData['allowed_booking_advance'] ?? '';
        $guiding->booking_window = $this->guidingData['booking_window'] ?? '';
        $guiding->seasonal_trip = $this->guidingData['seasonal_trip'] ?? '';
        $guiding->months = json_encode($this->guidingData['months'] ?? []);
        $guiding->weekday_availability = $this->guidingData['weekday_availability'] ?? 'all_week';
        $guiding->weekdays = json_encode($this->guidingData['weekdays'] ?? []);
    }

    /**
     * Handle seasonal blocking and calendar schedule generation
     */
    private function handleSeasonalBlocking(Guiding $guiding): void
    {
        if (!isset($this->guidingData['seasonal_trip'])) {
            Log::info('SaveGuidingDraftJob: No seasonal_trip data, skipping calendar schedule generation');
            return;
        }

        $selectedMonths = $this->guidingData['months'] ?? [];
        $selectedWeekdays = $this->guidingData['weekdays'] ?? [];

        // Generate complete calendar schedule
        CalendarScheduleService::generateCompleteSchedule(
            $guiding,
            $selectedMonths,
            $selectedWeekdays,
            $this->isUpdate // shouldCleanup
        );
    }


} 