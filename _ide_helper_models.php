<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Accommodation
 *
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $thumbnail_path
 * @property array|null $gallery_images
 * @property string $location
 * @property string $city
 * @property string $country
 * @property string $region
 * @property string|null $lat
 * @property string|null $lng
 * @property string|null $description
 * @property string $accommodation_type
 * @property string|null $condition_or_style
 * @property int|null $living_area_sqm
 * @property string|null $floor_layout
 * @property int|null $max_occupancy
 * @property int|null $number_of_bedrooms
 * @property array|null $bed_types
 * @property array|null $amenities
 * @property array|null $kitchen_equipment
 * @property array|null $bathroom_amenities
 * @property array|null $policies
 * @property array|null $rental_conditions
 * @property array|null $per_person_pricing
 * @property string|null $price_type
 * @property string|null $kitchen_type
 * @property int|null $bathroom
 * @property string|null $location_description
 * @property int|null $distance_to_water_m
 * @property int|null $distance_to_boat_berth_m
 * @property string|null $distance_to_shop_km
 * @property int|null $distance_to_parking_m
 * @property string|null $distance_to_nearest_town_km
 * @property string|null $distance_to_airport_km
 * @property string|null $distance_to_ferry_port_km
 * @property string|null $changeover_day
 * @property int|null $minimum_stay_nights
 * @property string|null $price_per_night
 * @property string|null $price_per_week
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereAccommodationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereAmenities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereBathroom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereBathroomAmenities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereBedTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereChangeoverDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereConditionOrStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToAirportKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToBoatBerthM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToFerryPortKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToNearestTownKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToParkingM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToShopKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereDistanceToWaterM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereFloorLayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereKitchenEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereKitchenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereLivingAreaSqm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereLocationDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereMaxOccupancy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereMinimumStayNights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereNumberOfBedrooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation wherePerPersonPricing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation wherePolicies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation wherePricePerNight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation wherePricePerWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereRentalConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Accommodation whereUserId($value)
 */
	class Accommodation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BlockedEvent
 *
 * @property int $id
 * @property string $from
 * @property string $due
 * @property string $type
 * @property int $user_id
 * @property int|null $guiding_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\BlockedEventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereUserId($value)
 */
	class BlockedEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BoatExtras
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras query()
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoatExtras whereUpdatedAt($value)
 */
	class BoatExtras extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BoatRequirements
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $name
 * @method static \Illuminate\Database\Eloquent\Builder|BoatRequirements newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoatRequirements newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoatRequirements query()
 * @method static \Illuminate\Database\Eloquent\Builder|BoatRequirements whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoatRequirements whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoatRequirements whereUpdatedAt($value)
 */
	class BoatRequirements extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Booking
 *
 * @property int $id
 * @property int $count_of_users
 * @property float|null $price
 * @property int $is_paid
 * @property int|null $user_id
 * @property int|null $guiding_id
 * @property int|null $blocked_event_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $rating_id
 * @property float|null $cag_percent
 * @property string|null $status
 * @property string|null $transaction_id
 * @property string|null $extras
 * @property float|null $total_extra_price
 * @property string|null $expires_at
 * @property string|null $token
 * @property string|null $book_date
 * @property string|null $additional_information
 * @property string|null $phone
 * @property string|null $phone_country_code
 * @property string|null $language
 * @property string|null $email
 * @property int|null $last_employee_id
 * @property int $is_guest
 * @property int $is_reviewed
 * @property int $is_rescheduled
 * @property string|null $alternative_dates
 * @property int|null $parent_id
 * @property-read \App\Models\BlockedEvent|null $blocked_event
 * @property-read \App\Models\CalendarSchedule|null $calendar_schedule
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Guiding|null $guiding
 * @property-read \App\Models\Rating|null $rating
 * @property-read \App\Models\Review|null $review
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\BookingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereAlternativeDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBlockedEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCagPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCountOfUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsRescheduled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsReviewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereLastEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereRatingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereTotalExtraPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUserId($value)
 */
	class Booking extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Cache
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $expire_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Cache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cache query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cache whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cache whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cache whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cache whereValue($value)
 */
	class Cache extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CacheList
 *
 * @property int $id
 * @property string $table
 * @property int $table_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList query()
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList whereTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CacheList whereUpdatedAt($value)
 */
	class CacheList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CalendarSchedule
 *
 * @property int $id
 * @property string $type
 * @property string $date
 * @property string|null $note
 * @property int|null $guiding_id
 * @property int|null $vacation_id
 * @property int|null $user_id
 * @property int|null $booking_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read mixed $due
 * @property-read mixed $from
 * @property-read \App\Models\Guiding|null $guiding
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Vacation|null $vacation
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSchedule whereVacationId($value)
 */
	class CalendarSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Camper
 *
 * @property int $id
 * @property string $name
 * @property string $manufacturer
 * @property string $model
 * @property string $description
 * @property int $max_person
 * @property int $seats
 * @property string $price
 * @property int $mileage
 * @property int $power
 * @property string $fuel_type
 * @property string $gearbox
 * @property int $emission_class
 * @property int $eco_badge
 * @property \Illuminate\Support\Carbon $first_registration
 * @property int $vehicle_owners
 * @property int $total_weight
 * @property \Illuminate\Support\Carbon $main_exam
 * @property int $sleeping_places
 * @property int $fixed_bed
 * @property int $bunk_bed
 * @property int $bed_alcove
 * @property int $rear_sleeping_places
 * @property int $dinette_sleeping_places
 * @property int $lift_bed
 * @property string $heating
 * @property string $fresh_water_tank
 * @property string $waste_water_tank
 * @property string $rear_garage
 * @property int $length
 * @property int $width
 * @property int $heigth
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContactRequest[] $contact_requests
 * @property-read int|null $contact_requests_count
 * @property-read \App\Models\Equipment|null $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CamperImage[] $images
 * @property-read int|null $images_count
 * @method static \Database\Factories\CamperFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camper newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camper query()
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereBedAlcove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereBunkBed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereDinetteSleepingPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereEcoBadge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereEmissionClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFirstRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFixedBed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFreshWaterTank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereFuelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereGearbox($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereHeating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereHeigth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereLiftBed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereMainExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereMaxPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper wherePower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereRearGarage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereRearSleepingPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereSeats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereSleepingPlaces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereTotalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereVehicleOwners($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereWasteWaterTank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camper whereWidth($value)
 * @mixin \Eloquent
 */
	class Camper extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CamperImage
 *
 * @property int $id
 * @property string $file_path
 * @property int $camper_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Camper $camper
 * @method static \Database\Factories\CamperImageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereCamperId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CamperImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class CamperImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Thread> $threads
 * @property-read int|null $threads_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CategoryPage
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $thumbnail_path
 * @property string|null $source_id
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_favorite
 * @property-read mixed $source
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereIsFavorite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryPage whereUpdatedAt($value)
 */
	class CategoryPage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Chat
 *
 * @property int $id
 * @property int $user_id
 * @property int $user_two_id
 * @property \Illuminate\Support\Carbon|null $last_message_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatMessage> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User $user
 * @property-read \App\Models\User $user_two
 * @method static \Database\Factories\ChatFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chat whereLastMessageAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chat whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chat whereUserTwoId($value)
 */
	class Chat extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChatMessage
 *
 * @property int $id
 * @property string $message
 * @property int $chat_id
 * @property int $user_id
 * @property int $is_read
 * @property string|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Chat $chat
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ChatMessageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUserId($value)
 */
	class ChatMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContactRequest
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $message
 * @property int|null $camper_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Camper|null $camper
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereCamperId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ContactRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContactSubmission
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $description
 * @property string|null $source_type
 * @property int|null $source_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactSubmission whereUpdatedAt($value)
 */
	class ContactSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Destination
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int|null $country_id
 * @property int|null $region_id
 * @property string $title
 * @property string $sub_title
 * @property string|null $introduction
 * @property string|null $content
 * @property mixed|null $filters
 * @property string|null $thumbnail_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $fish_avail_title
 * @property string|null $fish_avail_intro
 * @property string|null $size_limit_title
 * @property string|null $size_limit_intro
 * @property string|null $time_limit_title
 * @property string|null $time_limit_intro
 * @property string|null $faq_title
 * @property string|null $slug
 * @property string|null $language
 * @property string|null $countrycode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFaq> $faq
 * @property-read int|null $faq_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishChart> $fish_chart
 * @property-read int|null $fish_chart_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishSizeLimit> $fish_size_limit
 * @property-read int|null $fish_size_limit_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishTimeLimit> $fish_time_limit
 * @property-read int|null $fish_time_limit_count
 * @property-read mixed $country_name
 * @property-read mixed $country_slug
 * @property-read mixed $region_name
 * @property-read mixed $region_slug
 * @method static \Illuminate\Database\Eloquent\Builder|Destination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Destination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Destination onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Destination query()
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereCountrycode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereFishAvailIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereFishAvailTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereSizeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereSizeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereTimeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereTimeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Destination withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Destination withoutTrashed()
 */
	class Destination extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DestinationFaq
 *
 * @property int $id
 * @property int $destination_id
 * @property string $question
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination $category_country
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq query()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFaq whereUpdatedAt($value)
 */
	class DestinationFaq extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DestinationFishChart
 *
 * @property int $id
 * @property int $destination_id
 * @property string $fish
 * @property int $jan
 * @property int $feb
 * @property int $mar
 * @property int $apr
 * @property int $may
 * @property int $jun
 * @property int $jul
 * @property int $aug
 * @property int $sep
 * @property int $oct
 * @property int $nov
 * @property int $dec
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination $category_country
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart query()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereApr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereAug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereDec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereFeb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereJan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereJul($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereJun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereMar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereMay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereNov($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereOct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereSep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishChart whereUpdatedAt($value)
 */
	class DestinationFishChart extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DestinationFishSizeLimit
 *
 * @property int $id
 * @property int $destination_id
 * @property string $fish
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination $category_country
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishSizeLimit whereUpdatedAt($value)
 */
	class DestinationFishSizeLimit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DestinationFishTimeLimit
 *
 * @property int $id
 * @property int $destination_id
 * @property string $fish
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination $category_country
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DestinationFishTimeLimit whereUpdatedAt($value)
 */
	class DestinationFishTimeLimit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmailLog
 *
 * @property int $id
 * @property string $email
 * @property string $language
 * @property string $subject
 * @property string $type
 * @property int $status
 * @property string|null $target
 * @property string|null $additional_info
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereUpdatedAt($value)
 */
	class EmailLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\EmployeeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedAt($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Equipment
 *
 * @property int $id
 * @property int $esp
 * @property int $central_locking
 * @property int $abs
 * @property int $wc
 * @property int $radio_with_cd
 * @property int $radio_without_cd
 * @property int $navigation
 * @property int $cruise_control
 * @property int $power_steering
 * @property int $seperate_shower
 * @property int $checkbook_maintained
 * @property int $awning
 * @property int $air_condition
 * @property int $parking_assist
 * @property int $camper_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Camper $equipment
 * @method static \Database\Factories\EquipmentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAirCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereAwning($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCamperId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCentralLocking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCheckbookMaintained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereCruiseControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereEsp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereNavigation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereParkingAssist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment wherePowerSteering($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereRadioWithCd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereRadioWithoutCd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereSeperateShower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Equipment whereWc($value)
 * @mixin \Eloquent
 */
	class Equipment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EquipmentStatus
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EquipmentStatus whereUpdatedAt($value)
 */
	class EquipmentStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ExtrasPrice
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtrasPrice whereUpdatedAt($value)
 */
	class ExtrasPrice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Faq
 *
 * @property int $id
 * @property string|null $page
 * @property string|null $language
 * @property string $question
 * @property string $answer
 * @property string|null $source_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereUpdatedAt($value)
 */
	class Faq extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FishingEquipment
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guiding $guiding
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingEquipment whereUpdatedAt($value)
 */
	class FishingEquipment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FishingFrom
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FishingFrom> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom query()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingFrom whereUpdatedAt($value)
 */
	class FishingFrom extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FishingType
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FishingType> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType query()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingType whereUpdatedAt($value)
 */
	class FishingType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Gallery
 *
 * @property int $id
 * @property string $image_name
 * @property int $user_id
 * @property int $guiding_id
 * @property int|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guiding $guiding
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery query()
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereImageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereUserId($value)
 */
	class Gallery extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuideThread
 *
 * @property int $id
 * @property string|null $language
 * @property string $title
 * @property string $slug
 * @property string|null $body
 * @property string|null $excerpt
 * @property mixed|null $filters
 * @property string|null $author
 * @property string|null $thumbnail_path
 * @property string|null $introduction
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuideThread whereUpdatedAt($value)
 */
	class GuideThread extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Guiding
 *
 * @property string|null $target_fish
 * @property string|null $water_types
 * // ... add other dynamic properties as needed
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string|null $language
 * @property string $location
 * @property string|null $city
 * @property string|null $country
 * @property string|null $region
 * @property string|null $type
 * @property int|null $recommended_for_anfaenger
 * @property int|null $recommended_for_fortgeschrittene
 * @property int|null $recommended_for_profis
 * @property string|null $water
 * @property string|null $water_sonstiges
 * @property string|null $targets
 * @property string|null $target_fish_sonstiges
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingMethod> $methods
 * @property string|null $methods_sonstiges
 * @property int $max_guests
 * @property int|null $min_guests
 * @property float $duration
 * @property string|null $required_special_license
 * @property string|null $fishing_type
 * @property string|null $fishing_from
 * @property string|null $description
 * @property string|null $required_equipment
 * @property string|null $provided_equipment
 * @property string|null $additional_information
 * @property string|null $price
 * @property float|null $price_two_persons
 * @property float|null $price_three_persons
 * @property float|null $price_four_persons
 * @property float|null $price_five_persons
 * @property string|null $rest_method
 * @property string|null $water_name
 * @property string|null $catering
 * @property int $status
 * @property int $is_boat
 * @property int|null $thumbnail_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $thumbnail_path
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gallery> $galleries
 * @property string|null $needed_equipment
 * @property string|null $meeting_point
 * @property string|null $payment_point
 * @property int $fishing_type_id
 * @property int|null $fishing_from_id
 * @property int|null $equipment_status_id
 * @property string|null $boat_information
 * @property string|null $style_of_fishing
 * @property string|null $course_of_action
 * @property string|null $special_about
 * @property string|null $tour_unique
 * @property string|null $starting_time
 * @property string|null $private
 * @property string|null $allowed_booking_advance
 * @property string|null $booking_window
 * @property string|null $seasonal_trip
 * @property string|null $boat_type
 * @property mixed|null $additional_info
 * @property int $is_newguiding
 * @property string|null $boat_extras
 * @property string|null $fishing_methods
 * @property string|null $experience_level
 * @property string|null $inclusions
 * @property \Illuminate\Support\Collection $requirements
 * @property string|null $recommendations
 * @property string|null $other_information
 * @property string|null $duration_type
 * @property string|null $price_type
 * @property string|null $prices
 * @property string|null $pricing_extra
 * @property string|null $tour_type
 * @property string|null $months
 * @property string|null $weekday_availability
 * @property string|null $weekdays
 * @property string|null $gallery_images
 * @property string|null $desc_course_of_action
 * @property string|null $desc_meeting_point
 * @property string|null $desc_starting_time
 * @property string|null $desc_departure_time
 * @property string|null $desc_tour_unique
 * @property-read \App\Models\GuidingBoatType|null $boatType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \App\Models\EquipmentStatus|null $equipmentStatus
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingExtras> $extras
 * @property-read int|null $extras_count
 * @property-read \App\Models\FishingFrom|null $fishingFrom
 * @property-read \App\Models\FishingType|null $fishingTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FishingEquipment> $fishing_equipment
 * @property-read int|null $fishing_equipment_count
 * @property-read int|null $galleries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingGalleryMedia> $gallery_media
 * @property-read int|null $gallery_media_count
 * @property-read mixed $columns_with_value_count
 * @property-read mixed $excerpt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Method> $guidingMethods
 * @property-read int|null $guiding_methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Target> $guidingTargets
 * @property-read int|null $guiding_targets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Water> $guidingWaters
 * @property-read int|null $guiding_waters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ModelImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inclussion> $inclussions
 * @property-read int|null $inclussions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Levels> $levels
 * @property-read int|null $levels_count
 * @property-read int|null $methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $ratings
 * @property-read int|null $ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read int|null $target_fish_count
 * @property-read \App\Models\Media|null $thumbnail
 * @property-read \App\Models\User $user
 * @property-read int|null $water_types_count
 * @method static \Database\Factories\GuidingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding filterByRequestValue($requestValue)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding query()
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding radius($latitude, $longitude, $radius)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereAllowedBookingAdvance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereBoatExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereBoatInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereBoatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereBookingWindow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCatering($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCourseOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescCourseOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescMeetingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescStartingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescTourUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDurationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereEquipmentStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereExperienceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereGalleries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereInclusions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereIsBoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereIsNewguiding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMaxGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMeetingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMethodsSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMinGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereNeededEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereOtherInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePaymentPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceFivePersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceFourPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceThreePersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceTwoPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePrices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePricingExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereProvidedEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendedForAnfaenger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendedForFortgeschrittene($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendedForProfis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRequiredEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRequiredSpecialLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRestMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereSeasonalTrip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereSpecialAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereStartingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereStyleOfFishing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTargetFishSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereThumbnailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTourType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTourUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWaterName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWaterSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWaterTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWeekdayAvailability($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWeekdays($value)
 */
	class Guiding extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingAdditionalInformation
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingAdditionalInformation whereUpdatedAt($value)
 */
	class GuidingAdditionalInformation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingBoatDescription
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatDescription whereUpdatedAt($value)
 */
	class GuidingBoatDescription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingBoatType
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingBoatType whereUpdatedAt($value)
 */
	class GuidingBoatType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingExtras
 *
 * @property int $id
 * @property int $guiding_id
 * @property string|null $name
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingExtras whereUpdatedAt($value)
 */
	class GuidingExtras extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingGalleryMedia
 *
 * @property int $id
 * @property int $media_id
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @property-read \App\Models\Media $media
 * @method static \Database\Factories\GuidingGalleryMediaFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingGalleryMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingGalleryMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingGalleryMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingGalleryMedia whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingGalleryMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingGalleryMedia whereMediaId($value)
 */
	class GuidingGalleryMedia extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingInclussion
 *
 * @property int $id
 * @property int $guiding_id
 * @property int $inclussion_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guides
 * @property-read int|null $guides_count
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion whereInclussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingInclussion whereUpdatedAt($value)
 */
	class GuidingInclussion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingLevels
 *
 * @property int $id
 * @property int $guiding_id
 * @property int $levels_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guides
 * @property-read int|null $guides_count
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels whereLevelsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingLevels whereUpdatedAt($value)
 */
	class GuidingLevels extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingMethod
 *
 * @property int $id
 * @property string $name
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @method static \Database\Factories\GuidingMethodFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingMethod whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingMethod whereName($value)
 */
	class GuidingMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingRecommendations
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRecommendations whereUpdatedAt($value)
 */
	class GuidingRecommendations extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingRequest
 *
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string $country
 * @property string $city
 * @property string|null $days_of_tour
 * @property int|null $specific_number_of_days
 * @property string|null $accomodation
 * @property string $targets
 * @property string $methods
 * @property string $fishing_from
 * @property int $number_of_guest
 * @property string|null $date_of_tour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $guide_type
 * @property string|null $fishing_duration
 * @property string|null $days_of_fishing
 * @property string|null $from_date
 * @property string|null $to_date
 * @property string|null $rentaboat
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereAccomodation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereDateOfTour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereDaysOfFishing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereDaysOfTour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereFishingDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereFishingFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereGuideType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereNumberOfGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereRentaboat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereSpecificNumberOfDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequest whereUpdatedAt($value)
 */
	class GuidingRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingRequirements
 *
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingRequirements whereUpdatedAt($value)
 */
	class GuidingRequirements extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingTargetFish
 *
 * @property int $id
 * @property string $name
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @method static \Database\Factories\GuidingTargetFishFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingTargetFish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingTargetFish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingTargetFish query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingTargetFish whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingTargetFish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingTargetFish whereName($value)
 */
	class GuidingTargetFish extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GuidingWaterType
 *
 * @property int $id
 * @property string $name
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @method static \Database\Factories\GuidingWaterTypeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingWaterType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingWaterType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingWaterType query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingWaterType whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingWaterType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuidingWaterType whereName($value)
 */
	class GuidingWaterType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ICalFeed
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $feed_url
 * @property string $sync_type
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_sync_at
 * @property \Illuminate\Support\Carbon|null $last_successful_sync_at
 * @property int $sync_frequency_hours
 * @property array|null $sync_settings
 * @property string|null $last_error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status_color
 * @property-read string $status_display
 * @property-read string $sync_type_display
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed active()
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed needsSync()
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed query()
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereFeedUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereLastError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereLastSuccessfulSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereLastSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereSyncFrequencyHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereSyncSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereSyncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ICalFeed whereUserId($value)
 */
	class ICalFeed extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Inclussion
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inclussion whereUpdatedAt($value)
 */
	class Inclussion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Language
 *
 * @property int $id
 * @property string $source_id
 * @property string|null $type
 * @property string|null $language
 * @property string $title
 * @property string|null $sub_title
 * @property string|null $introduction
 * @property array|null $json_data
 * @property string|null $content
 * @property string|null $faq_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryPage|null $categoryPage
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereJsonData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereUpdatedAt($value)
 */
	class Language extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Levels
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Levels newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Levels newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Levels query()
 * @method static \Illuminate\Database\Eloquent\Builder|Levels whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Levels whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Levels whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Levels whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Levels whereUpdatedAt($value)
 */
	class Levels extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Location
 *
 * @property int $id
 * @property string|null $city
 * @property string|null $country
 * @property array|null $translation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $region
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location searchTranslation($searchString)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereTranslation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Media
 *
 * @property int $id
 * @property string $file_path
 * @property string $file_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingGalleryMedia> $guiding_galleries
 * @property-read int|null $guiding_galleries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Database\Factories\MediaFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereUpdatedAt($value)
 */
	class Media extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Method
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryPage|null $categoryPage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Method newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Method newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Method query()
 * @method static \Illuminate\Database\Eloquent\Builder|Method whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Method whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Method whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Method whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Method whereUpdatedAt($value)
 */
	class Method extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ModelChangeHistory
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $field_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string $change_type
 * @property \Illuminate\Support\Carbon $changed_at
 * @property int|null $user_id
 * @property string $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereChangeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereNewValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereOldValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelChangeHistory whereUserId($value)
 */
	class ModelChangeHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ModelImage
 *
 * @property int $id
 * @property int $model_id
 * @property string $model_type
 * @property string|null $image_name
 * @property string|null $image_size
 * @property string|null $image_url
 * @property int|null $image_exists
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereImageExists($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereImageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereImageSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelImage whereUpdatedAt($value)
 */
	class ModelImage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Newsletter
 *
 * @property int $id
 * @property string $email
 * @property string|null $language
 * @property string|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter query()
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereUpdatedAt($value)
 */
	class Newsletter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OAuthToken
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken active()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthToken whereUpdatedAt($value)
 */
	class OAuthToken extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PageAttribute
 *
 * @property int $id
 * @property string $page
 * @property string $meta_type
 * @property string $domain
 * @property string $uri
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $deleted_at_format
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereMetaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute whereUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PageAttribute withoutTrashed()
 */
	class PageAttribute extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Payment
 *
 * @property int $id
 * @property float $amount
 * @property int $is_completed
 * @property string $type
 * @property int $user_id
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PaymentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUserId($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Rating
 *
 * @property int $id
 * @property string $description
 * @property float $rating
 * @property int $user_id
 * @property int $guide_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\User $guide
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\RatingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rating query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereGuideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rating whereUserId($value)
 */
	class Rating extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RentalBoat
 *
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $thumbnail_path
 * @property array|null $gallery_images
 * @property string $location
 * @property string $city
 * @property string $country
 * @property string|null $region
 * @property string|null $lat
 * @property string|null $lng
 * @property string $boat_type
 * @property string $desc_of_boat
 * @property string|null $requirements
 * @property array|null $boat_information
 * @property array|null $boat_extras
 * @property string $price_type
 * @property array $prices
 * @property array|null $pricing_extra
 * @property array|null $inclusions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GuidingBoatType|null $boatType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat query()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereBoatExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereBoatInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereBoatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereDescOfBoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereInclusions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat wherePrices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat wherePricingExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RentalBoat whereUserId($value)
 */
	class RentalBoat extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Review
 *
 * @property int $id
 * @property string|null $comment
 * @property float $overall_score
 * @property float $guide_score
 * @property float $region_water_score
 * @property float $grandtotal_score
 * @property int $user_id
 * @property int $guide_id
 * @property int $booking_id
 * @property int $guiding_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @property-read \App\Models\User $guide
 * @property-read \App\Models\Guiding $guiding
 * @method static \Illuminate\Database\Eloquent\Builder|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereGrandtotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereGuideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereGuideScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereOverallScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereRegionWaterScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUserId($value)
 */
	class Review extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SearchRequest
 *
 * @property int $id
 * @property string $fishing_type
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string|null $country
 * @property string|null $region
 * @property string $target_fish
 * @property int $number_of_guest
 * @property int $is_best_fishing_time_recommendation
 * @property string|null $date_from
 * @property string|null $date_to
 * @property int $is_guided
 * @property string|null $days_of_guiding
 * @property int $is_boat_rental
 * @property string|null $days_of_boat_rental
 * @property string|null $total_budget_to_spend
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereDateFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereDateTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereDaysOfBoatRental($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereDaysOfGuiding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereFishingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereIsBestFishingTimeRecommendation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereIsBoatRental($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereIsGuided($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereNumberOfGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereTotalBudgetToSpend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchRequest whereUpdatedAt($value)
 */
	class SearchRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Target
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryPage|null $categoryPage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Target newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Target newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Target query()
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereUpdatedAt($value)
 */
	class Target extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Thread
 *
 * @property int $id
 * @property string|null $language
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $body
 * @property string $author
 * @property string $thumbnail_path
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $cache
 * @property-read \App\Models\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|Thread newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Thread newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Thread query()
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereUpdatedAt($value)
 */
	class Thread extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string|null $phone
 * @property string|null $phone_country_code
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property float $pending_balance
 * @property float $paid_balance
 * @property int $is_active
 * @property int|null $is_guide
 * @property string|null $profil_image
 * @property int $user_information_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property string|null $trial_ends_at
 * @property float|null $percentage_price_increase
 * @property int|null $bar_allowed
 * @property int|null $banktransfer_allowed
 * @property int|null $paypal_allowed
 * @property string|null $banktransferdetails
 * @property string|null $paypaldetails
 * @property string|null $merchant_id
 * @property string|null $merchant_status
 * @property string|null $merchant_compliance_status
 * @property string|null $merchant_bank
 * @property string|null $merchant_verification_url
 * @property string|null $tax_id
 * @property string|null $language
 * @property int|null $number_of_guides
 * @property int $is_temp_password
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BlockedEvent> $blocked_events
 * @property-read int|null $blocked_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarSchedule> $calendar_schedules
 * @property-read int|null $calendar_schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatMessage> $chat_messages
 * @property-read int|null $chat_messages_count
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $given_ratings
 * @property-read int|null $given_ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ICalFeed> $icalFeeds
 * @property-read int|null $ical_feeds_count
 * @property-read \App\Models\UserInformation $information
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $received_ratings
 * @property-read int|null $received_ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserICalFeed> $userIcalFeeds
 * @property-read int|null $user_ical_feeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WishlistItem> $wishlist_items
 * @property-read int|null $wishlist_items_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBanktransferAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBanktransferdetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBarAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsGuide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsTempPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantComplianceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantVerificationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNumberOfGuides($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaidBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaypalAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaypaldetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePendingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePercentagePriceIncrease($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfilImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserInformationId($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserGuest
 *
 * @property int $id
 * @property string|null $salutation
 * @property string|null $title
 * @property string $firstname
 * @property string $lastname
 * @property string $address
 * @property string $postal
 * @property string $city
 * @property string $country
 * @property string $phone
 * @property string|null $phone_country_code
 * @property string $email
 * @property string|null $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest wherePostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereSalutation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGuest whereUpdatedAt($value)
 */
	class UserGuest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserICalFeed
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $feed_token
 * @property string $otp_secret
 * @property string $feed_type
 * @property array|null $feed_settings
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_accessed_at
 * @property int $access_count
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $feed_type_display
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed active()
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereAccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereFeedSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereFeedToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereFeedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereLastAccessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereOtpSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserICalFeed whereUserId($value)
 */
	class UserICalFeed extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserInformation
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $birthday
 * @property string|null $address
 * @property string|null $address_number
 * @property string|null $postal
 * @property string|null $phone
 * @property string|null $phone_country_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $about_me
 * @property string|null $languages
 * @property string|null $favorite_fish
 * @property int|null $fishing_start_year
 * @property string|null $proof_of_identity_file_path
 * @property string|null $fishing_permit_file_path
 * @property int $request_as_guide
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereAboutMe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereAddressNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereFavoriteFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereFishingPermitFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereFishingStartYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereLanguages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation wherePostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereProofOfIdentityFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereRequestAsGuide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereUpdatedAt($value)
 */
	class UserInformation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Vacation
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $location
 * @property string|null $city
 * @property string $country
 * @property string $latitude
 * @property string $longitude
 * @property string|null $region
 * @property array|null $gallery
 * @property array $best_travel_times
 * @property string $surroundings_description
 * @property array $target_fish
 * @property string|null $airport_distance
 * @property string|null $water_distance
 * @property string|null $shopping_distance
 * @property string|null $travel_included
 * @property array|null $travel_options
 * @property bool|null $pets_allowed
 * @property bool|null $smoking_allowed
 * @property bool|null $disability_friendly
 * @property bool $has_boat
 * @property bool $has_guiding
 * @property array|null $additional_services
 * @property array|null $included_services
 * @property int $status
 * @property string $language Original language of the vacation data
 * @property \Illuminate\Support\Carbon|null $content_updated_at When the content was last significantly updated
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationAccommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationBoat> $boats
 * @property-read int|null $boats_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationBooking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationExtra> $extras
 * @property-read int|null $extras_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationGuiding> $guidings
 * @property-read int|null $guidings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ModelImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationPackage> $packages
 * @property-read int|null $packages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Language> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation radius($latitude, $longitude, $radius)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereAdditionalServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereAirportDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereBestTravelTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereContentUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereDisabilityFriendly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereGallery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereHasBoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereHasGuiding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereIncludedServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation wherePetsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereShoppingDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereSmokingAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereSurroundingsDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereTravelIncluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereTravelOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vacation whereWaterDistance($value)
 */
	class Vacation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VacationAccommodation
 *
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation $vacation
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation query()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationAccommodation whereVacationId($value)
 */
	class VacationAccommodation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VacationBoat
 *
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation $vacation
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat query()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBoat whereVacationId($value)
 */
	class VacationBoat extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VacationBooking
 *
 * @property int $id
 * @property int $vacation_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property int $duration
 * @property int $number_of_persons
 * @property string $booking_type
 * @property int|null $package_id
 * @property int|null $accommodation_id
 * @property int|null $boat_id
 * @property int|null $guiding_id
 * @property string $title
 * @property string $name
 * @property string $surname
 * @property string $street
 * @property string $post_code
 * @property string $city
 * @property string $country
 * @property string $phone_country_code
 * @property string $phone
 * @property string $email
 * @property string|null $comments
 * @property bool $has_pets
 * @property array|null $extra_offers
 * @property string $total_price
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\VacationAccommodation|null $accommodation
 * @property-read \App\Models\VacationBoat|null $boat
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationExtra> $extras
 * @property-read int|null $extras_count
 * @property-read \App\Models\VacationGuiding|null $guiding
 * @property-read \App\Models\VacationPackage|null $package
 * @property-read \App\Models\Vacation $vacation
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereAccommodationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereBoatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereBookingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereExtraOffers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereHasPets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereNumberOfPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking wherePostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationBooking whereVacationId($value)
 */
	class VacationBooking extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VacationExtra
 *
 * @property int $id
 * @property int $vacation_id
 * @property string $type
 * @property string $description
 * @property string $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation $vacation
 * @property-read \App\Models\VacationBooking|null $vacationBooking
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationExtra whereVacationId($value)
 */
	class VacationExtra extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VacationGuiding
 *
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation $vacation
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding query()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationGuiding whereVacationId($value)
 */
	class VacationGuiding extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VacationPackage
 *
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation $vacation
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage query()
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VacationPackage whereVacationId($value)
 */
	class VacationPackage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Water
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Water newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Water newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Water query()
 * @method static \Illuminate\Database\Eloquent\Builder|Water whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Water whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Water whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Water whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Water whereUpdatedAt($value)
 */
	class Water extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\WishlistItem
 *
 * @property int $id
 * @property int $user_id
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem whereUserId($value)
 */
	class WishlistItem extends \Eloquent {}
}

