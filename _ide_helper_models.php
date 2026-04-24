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
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $thumbnail_path
 * @property array<array-key, mixed>|null $gallery_images
 * @property string $location
 * @property string $city
 * @property string $country
 * @property string $region
 * @property numeric|null $lat
 * @property numeric|null $lng
 * @property string $accommodation_type
 * @property int|null $max_occupancy
 * @property array<array-key, mixed>|null $amenities
 * @property array<array-key, mixed>|null $kitchen_equipment
 * @property array<array-key, mixed>|null $bathroom_amenities
 * @property array<int, array<string, mixed>> $accommodation_details
 * @property array<int, array<string, mixed>> $room_configurations
 * @property array<int, array<string, mixed>> $policies
 * @property array<array-key, mixed>|null $rental_conditions
 * @property array<array-key, mixed>|null $per_person_pricing
 * @property string|null $distance_to_water_m
 * @property string|null $distance_to_boat_berth_m
 * @property numeric|null $distance_to_shop_km
 * @property string|null $distance_to_parking_m
 * @property int|null $minimum_stay_nights
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $extras
 * @property array<array-key, mixed>|null $inclusives
 * @property-read \App\Models\AccommodationType|null $accommodationType
 * @property-read mixed $name
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereAccommodationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereAccommodationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereAmenities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereBathroomAmenities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereDistanceToBoatBerthM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereDistanceToParkingM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereDistanceToShopKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereDistanceToWaterM($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereInclusives($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereKitchenEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereMaxOccupancy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereMinimumStayNights($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation wherePerPersonPricing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation wherePolicies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereRentalConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereRoomConfigurations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereUserId($value)
 */
	class Accommodation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string $input_type
 * @property string|null $placeholder
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereInputType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail wherePlaceholder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationDetail whereUpdatedAt($value)
 */
	class AccommodationDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationExtra whereUpdatedAt($value)
 */
	class AccommodationExtra extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationInclusive whereUpdatedAt($value)
 */
	class AccommodationInclusive extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationPolicy whereUpdatedAt($value)
 */
	class AccommodationPolicy extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property string $input_type
 * @property string|null $placeholder
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereInputType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition wherePlaceholder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationRentalCondition whereUpdatedAt($value)
 */
	class AccommodationRentalCondition extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationType whereUpdatedAt($value)
 */
	class AccommodationType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string|null $body
 * @property string $level
 * @property string|null $link
 * @property array<array-key, mixed>|null $meta
 * @property bool $is_read
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereUpdatedAt($value)
 */
	class AdminNotification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathroomAmenity whereUpdatedAt($value)
 */
	class BathroomAmenity extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Database\Factories\BlockedEventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlockedEvent whereUserId($value)
 */
	class BlockedEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatExtras whereUpdatedAt($value)
 */
	class BoatExtras extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatRequirements newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatRequirements newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatRequirements query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatRequirements whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatRequirements whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BoatRequirements whereUpdatedAt($value)
 */
	class BoatRequirements extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $count_of_users
 * @property float|null $price
 * @property int $is_paid
 * @property string|null $guide_invoice_sent_at
 * @property int $is_guide_billed
 * @property string|null $guide_billed_at
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
 * @property string|null $admin_comment
 * @property string|null $phone
 * @property string|null $phone_country_code
 * @property string|null $language
 * @property string|null $email
 * @property int|null $last_employee_id
 * @property int|null $created_by_id
 * @property string|null $created_source
 * @property int $is_guest
 * @property int $is_reviewed
 * @property int $is_rescheduled
 * @property string|null $alternative_dates
 * @property int|null $parent_id
 * @property-read \App\Models\BlockedEvent|null $blocked_event
 * @property-read \App\Models\CalendarSchedule|null $calendar_schedule
 * @property-read \App\Models\Employee|null $createdBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\FinanceItem|null $financeItem
 * @property-read \App\Models\Guiding|null $guiding
 * @property-read \App\Models\Rating|null $rating
 * @property-read \App\Models\Review|null $review
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\BookingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereAdminComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereAlternativeDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereBlockedEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereBookDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCagPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCountOfUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuideBilledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuideInvoiceSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereIsGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereIsGuideBilled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereIsRescheduled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereIsReviewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereLastEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereRatingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereTotalExtraPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUserId($value)
 */
	class Booking extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $expire_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereValue($value)
 */
	class Cache extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $table
 * @property int $table_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList whereTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheList whereUpdatedAt($value)
 */
	class CacheList extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarSchedule whereVacationId($value)
 */
	class CalendarSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string $description_camp
 * @property string $description_area
 * @property string $description_fishing
 * @property string $location
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $country
 * @property string|null $city
 * @property string|null $region
 * @property string|null $distance_to_store
 * @property string|null $distance_to_nearest_town
 * @property string|null $distance_to_airport
 * @property string|null $distance_to_ferry_port
 * @property string|null $policies_regulations
 * @property array<array-key, mixed>|null $target_fish
 * @property array<array-key, mixed>|null $best_travel_times
 * @property string|null $travel_information
 * @property string|null $extras
 * @property string|null $thumbnail_path
 * @property array<array-key, mixed>|null $gallery_images
 * @property string $status
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CampFacility> $facilities
 * @property-read int|null $facilities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RentalBoat> $rentalBoats
 * @property-read int|null $rental_boats_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SpecialOffer> $specialOffers
 * @property-read int|null $special_offers_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereBestTravelTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDescriptionArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDescriptionCamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDescriptionFishing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDistanceToAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDistanceToFerryPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDistanceToNearestTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereDistanceToStore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp wherePoliciesRegulations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereTravelInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Camp whereUserId($value)
 */
	class Camp extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_de
 * @property string|null $name_en
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Camp> $camps
 * @property-read int|null $camps_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereNameDe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampFacility whereUpdatedAt($value)
 */
	class CampFacility extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $source_type
 * @property int $source_id
 * @property \Illuminate\Support\Carbon $preferred_date
 * @property int $number_of_persons
 * @property string $name
 * @property string $email
 * @property string $phone_country_code
 * @property string $phone
 * @property string $message
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FinanceItem|null $financeItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereNumberOfPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking wherePreferredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampVacationBooking whereUpdatedAt($value)
 */
	class CampVacationBooking extends \Eloquent {}
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
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Thread> $threads
 * @property-read int|null $threads_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereIsFavorite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryPage whereUpdatedAt($value)
 */
	class CategoryPage extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Database\Factories\ChatFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereLastMessageAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUserTwoId($value)
 */
	class Chat extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Database\Factories\ChatMessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatMessage whereUserId($value)
 */
	class ChatMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $country_id
 * @property int|null $region_id
 * @property string $name
 * @property string $slug
 * @property array<array-key, mixed>|null $filters
 * @property string|null $thumbnail_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Country|null $country
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFaq> $faqs
 * @property-read int|null $faqs_count
 * @property-read mixed $content
 * @property-read mixed $faq_title
 * @property-read mixed $fish_avail_intro
 * @property-read mixed $fish_avail_title
 * @property-read mixed $introduction
 * @property-read mixed $size_limit_intro
 * @property-read mixed $size_limit_title
 * @property-read mixed $sub_title
 * @property-read mixed $time_limit_intro
 * @property-read mixed $time_limit_title
 * @property-read mixed $title
 * @property-read \App\Models\Region|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CityTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City withoutTrashed()
 */
	class City extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $city_id
 * @property string $language
 * @property string|null $title
 * @property string|null $sub_title
 * @property string|null $introduction
 * @property string|null $content
 * @property string|null $fish_avail_title
 * @property string|null $fish_avail_intro
 * @property string|null $size_limit_title
 * @property string|null $size_limit_intro
 * @property string|null $time_limit_title
 * @property string|null $time_limit_intro
 * @property string|null $faq_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\City|null $city
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereFishAvailIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereFishAvailTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereSizeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereSizeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereTimeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereTimeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CityTranslation whereUpdatedAt($value)
 */
	class CityTranslation extends \Eloquent {}
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
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $description
 * @property string|null $admin_comment
 * @property string|null $source_type
 * @property int|null $source_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereAdminComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactSubmission whereUpdatedAt($value)
 */
	class ContactSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $countrycode
 * @property array<array-key, mixed>|null $filters
 * @property string|null $thumbnail_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFaq> $faqs
 * @property-read int|null $faqs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishChart> $fish_charts
 * @property-read int|null $fish_charts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishSizeLimit> $fish_size_limits
 * @property-read int|null $fish_size_limits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishTimeLimit> $fish_time_limits
 * @property-read int|null $fish_time_limits_count
 * @property-read mixed $content
 * @property-read mixed $faq_title
 * @property-read mixed $fish_avail_intro
 * @property-read mixed $fish_avail_title
 * @property-read mixed $introduction
 * @property-read mixed $size_limit_intro
 * @property-read mixed $size_limit_title
 * @property-read mixed $sub_title
 * @property-read mixed $time_limit_intro
 * @property-read mixed $time_limit_title
 * @property-read mixed $title
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Region> $regions
 * @property-read int|null $regions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CountryTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereCountrycode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country withoutTrashed()
 */
	class Country extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $country_id
 * @property string $language
 * @property string|null $title
 * @property string|null $sub_title
 * @property string|null $introduction
 * @property string|null $content
 * @property string|null $fish_avail_title
 * @property string|null $fish_avail_intro
 * @property string|null $size_limit_title
 * @property string|null $size_limit_intro
 * @property string|null $time_limit_title
 * @property string|null $time_limit_intro
 * @property string|null $faq_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country|null $country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereFishAvailIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereFishAvailTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereSizeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereSizeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereTimeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereTimeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CountryTranslation whereUpdatedAt($value)
 */
	class CountryTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string $recipient_type
 * @property int|null $customer_id
 * @property string $recipient_email
 * @property string|null $recipient_name
 * @property string|null $recipient_phone
 * @property array<array-key, mixed>|null $camp_ids
 * @property array<array-key, mixed>|null $accommodation_ids
 * @property array<array-key, mixed>|null $boat_ids
 * @property array<array-key, mixed>|null $guiding_ids
 * @property string|null $date_from
 * @property string|null $date_to
 * @property string|null $number_of_persons
 * @property string|null $price
 * @property string|null $additional_info
 * @property string|null $free_text
 * @property array<array-key, mixed>|null $offers
 * @property string $locale
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $customer
 * @property-read mixed $accommodations
 * @property-read mixed $guidings
 * @property-read mixed $rental_boats
 * @property-read array $resolved_offers
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereAccommodationIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereBoatIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereCampIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereDateFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereDateTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereFreeText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereGuidingIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereNumberOfPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereOffers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereRecipientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereRecipientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereRecipientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereRecipientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomCampOffer whereUpdatedAt($value)
 */
	class CustomCampOffer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int|null $country_id
 * @property int|null $region_id
 * @property string $title
 * @property string $sub_title
 * @property string|null $introduction
 * @property string|null $content
 * @property string|null $filters
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereCountrycode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereFishAvailIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereFishAvailTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereSizeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereSizeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereTimeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereTimeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Destination withoutTrashed()
 */
	class Destination extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $destination_id
 * @property string|null $destination_type
 * @property string $question
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination|null $category_country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereDestinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFaq whereUpdatedAt($value)
 */
	class DestinationFaq extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $destination_id
 * @property string|null $destination_type
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
 * @property-read \App\Models\Destination|null $category_country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereApr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereAug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereDec($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereDestinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereFeb($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereJan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereJul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereJun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereMar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereMay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereNov($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereOct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereSep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishChart whereUpdatedAt($value)
 */
	class DestinationFishChart extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $destination_id
 * @property string|null $destination_type
 * @property string $fish
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination|null $category_country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereDestinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishSizeLimit whereUpdatedAt($value)
 */
	class DestinationFishSizeLimit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $destination_id
 * @property string|null $destination_type
 * @property string $fish
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property-read \App\Models\Destination|null $category_country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereDestinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DestinationFishTimeLimit whereUpdatedAt($value)
 */
	class DestinationFishTimeLimit extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailLog whereUpdatedAt($value)
 */
	class EmailLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $password_reset_at
 * @property int|null $password_reset_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property-read Employee|null $deletedByUser
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Employee|null $passwordResetByUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\EmployeeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePasswordResetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePasswordResetBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee withoutTrashed()
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
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EquipmentStatus whereUpdatedAt($value)
 */
	class EquipmentStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtrasPrice whereUpdatedAt($value)
 */
	class ExtrasPrice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereUpdatedAt($value)
 */
	class Facility extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $page
 * @property string|null $language
 * @property string $question
 * @property string $answer
 * @property string|null $source_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereUpdatedAt($value)
 */
	class Faq extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $billable_type
 * @property int $billable_id
 * @property string $invoice_status
 * @property \Illuminate\Support\Carbon|null $invoice_sent_at
 * @property string|null $invoice_number
 * @property string $paid_status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $billable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereBillableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereBillableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereInvoiceSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereInvoiceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem wherePaidStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinanceItem whereUpdatedAt($value)
 */
	class FinanceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guiding|null $guiding
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingEquipment whereUpdatedAt($value)
 */
	class FishingEquipment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FishingFrom> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingFrom whereUpdatedAt($value)
 */
	class FishingFrom extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FishingType> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FishingType whereUpdatedAt($value)
 */
	class FishingType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $image_name
 * @property int $user_id
 * @property int $guiding_id
 * @property int|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Guiding $guiding
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereImageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gallery whereUserId($value)
 */
	class Gallery extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $language
 * @property string $title
 * @property string $slug
 * @property string|null $body
 * @property string|null $excerpt
 * @property string|null $filters
 * @property string|null $author
 * @property string|null $thumbnail_path
 * @property string|null $introduction
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideThread whereUpdatedAt($value)
 */
	class GuideThread extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @property numeric|null $price
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
 * @property string|null $additional_info
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Language> $languageTranslations
 * @property-read int|null $language_translations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Levels> $levels
 * @property-read int|null $levels_count
 * @property-read int|null $methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $ratings
 * @property-read int|null $ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read int|null $target_fish_count
 * @property-read \App\Models\Media|null $thumbnail
 * @property-read \App\Models\Language|null $translationForCurrentLocale
 * @property-read \App\Models\User $user
 * @property-read int|null $water_types_count
 * @method static \Database\Factories\GuidingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding filterByRequestValue($requestValue)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding radius($latitude, $longitude, $radius)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereAllowedBookingAdvance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereBoatExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereBoatInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereBoatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereBookingWindow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereCatering($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereCourseOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDescCourseOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDescDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDescMeetingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDescStartingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDescTourUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereDurationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereEquipmentStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereExperienceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereFishingFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereFishingFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereFishingMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereFishingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereFishingTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereGalleries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereInclusions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereIsBoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereIsNewguiding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereMaxGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereMeetingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereMethodsSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereMinGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereNeededEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereOtherInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePaymentPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePriceFivePersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePriceFourPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePriceThreePersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePriceTwoPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePrices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePricingExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereProvidedEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRecommendations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRecommendedForAnfaenger($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRecommendedForFortgeschrittene($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRecommendedForProfis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRequiredEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRequiredSpecialLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereRestMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereSeasonalTrip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereSpecialAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereStartingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereStyleOfFishing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereTargetFishSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereThumbnailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereTourType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereTourUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereWater($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereWaterName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereWaterSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereWaterTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereWeekdayAvailability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guiding whereWeekdays($value)
 */
	class Guiding extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingAdditionalInformation whereUpdatedAt($value)
 */
	class GuidingAdditionalInformation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatDescription whereUpdatedAt($value)
 */
	class GuidingBoatDescription extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingBoatType whereUpdatedAt($value)
 */
	class GuidingBoatType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $guiding_id
 * @property string|null $name
 * @property numeric $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingExtras whereUpdatedAt($value)
 */
	class GuidingExtras extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $media_id
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @property-read \App\Models\Media $media
 * @method static \Database\Factories\GuidingGalleryMediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingGalleryMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingGalleryMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingGalleryMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingGalleryMedia whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingGalleryMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingGalleryMedia whereMediaId($value)
 */
	class GuidingGalleryMedia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $guiding_id
 * @property int $inclussion_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guides
 * @property-read int|null $guides_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion whereInclussionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingInclussion whereUpdatedAt($value)
 */
	class GuidingInclussion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $guiding_id
 * @property int $levels_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guides
 * @property-read int|null $guides_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels whereLevelsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingLevels whereUpdatedAt($value)
 */
	class GuidingLevels extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @method static \Database\Factories\GuidingMethodFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingMethod whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingMethod whereName($value)
 */
	class GuidingMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRecommendations whereUpdatedAt($value)
 */
	class GuidingRecommendations extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereAccomodation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereDateOfTour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereDaysOfFishing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereDaysOfTour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereFishingDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereFishingFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereGuideType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereNumberOfGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereRentaboat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereSpecificNumberOfDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequest whereUpdatedAt($value)
 */
	class GuidingRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingRequirements whereUpdatedAt($value)
 */
	class GuidingRequirements extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @method static \Database\Factories\GuidingTargetFishFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingTargetFish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingTargetFish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingTargetFish query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingTargetFish whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingTargetFish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingTargetFish whereName($value)
 */
	class GuidingTargetFish extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $guiding_id
 * @property-read \App\Models\Guiding $guiding
 * @method static \Database\Factories\GuidingWaterTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingWaterType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingWaterType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingWaterType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingWaterType whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingWaterType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuidingWaterType whereName($value)
 */
	class GuidingWaterType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $feed_url
 * @property string $sync_type
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_sync_at
 * @property \Illuminate\Support\Carbon|null $last_successful_sync_at
 * @property int $sync_frequency_hours
 * @property array<array-key, mixed>|null $sync_settings
 * @property string|null $last_error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status_color
 * @property-read string $status_display
 * @property-read string $sync_type_display
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed needsSync()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereFeedUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereLastError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereLastSuccessfulSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereLastSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereSyncFrequencyHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereSyncSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereSyncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ICalFeed whereUserId($value)
 */
	class ICalFeed extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inclussion whereUpdatedAt($value)
 */
	class Inclussion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenEquipment whereUpdatedAt($value)
 */
	class KitchenEquipment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $source_id
 * @property string|null $type
 * @property string|null $language
 * @property string $title
 * @property string|null $sub_title
 * @property string|null $introduction
 * @property array<array-key, mixed>|null $json_data
 * @property string|null $content
 * @property string|null $faq_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryPage|null $categoryPage
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereJsonData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereUpdatedAt($value)
 */
	class Language extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Levels whereUpdatedAt($value)
 */
	class Levels extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $city
 * @property string|null $country
 * @property array<array-key, mixed>|null $translation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $region
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location searchTranslation($searchString)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereTranslation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $file_path
 * @property string $file_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingGalleryMedia> $guiding_galleries
 * @property-read int|null $guiding_galleries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Database\Factories\MediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUpdatedAt($value)
 */
	class Media extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryPage|null $categoryPage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereUpdatedAt($value)
 */
	class Method extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereChangeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereNewValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereOldValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelChangeHistory whereUserId($value)
 */
	class ModelChangeHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $model_id
 * @property string $model_type
 * @property string|null $image_name
 * @property string|null $image_size
 * @property string|null $image_url
 * @property int|null $image_exists
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereImageExists($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereImageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereImageSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelImage whereUpdatedAt($value)
 */
	class ModelImage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property string|null $language
 * @property string|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Newsletter whereUpdatedAt($value)
 */
	class Newsletter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken byType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthToken whereUpdatedAt($value)
 */
	class OAuthToken extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereMetaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute whereUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageAttribute withoutTrashed()
 */
	class PageAttribute extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property float $amount
 * @property int $is_completed
 * @property string $type
 * @property int $user_id
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PaymentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserId($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Database\Factories\RatingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereGuideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rating whereUserId($value)
 */
	class Rating extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $slug
 * @property array<array-key, mixed>|null $filters
 * @property string|null $thumbnail_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \App\Models\Country|null $country
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFaq> $faqs
 * @property-read int|null $faqs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishChart> $fish_charts
 * @property-read int|null $fish_charts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishSizeLimit> $fish_size_limits
 * @property-read int|null $fish_size_limits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DestinationFishTimeLimit> $fish_time_limits
 * @property-read int|null $fish_time_limits_count
 * @property-read mixed $content
 * @property-read mixed $faq_title
 * @property-read mixed $fish_avail_intro
 * @property-read mixed $fish_avail_title
 * @property-read mixed $introduction
 * @property-read mixed $size_limit_intro
 * @property-read mixed $size_limit_title
 * @property-read mixed $sub_title
 * @property-read mixed $time_limit_intro
 * @property-read mixed $time_limit_title
 * @property-read mixed $title
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegionTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region withoutTrashed()
 */
	class Region extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $region_id
 * @property string $language
 * @property string|null $title
 * @property string|null $sub_title
 * @property string|null $introduction
 * @property string|null $content
 * @property string|null $fish_avail_title
 * @property string|null $fish_avail_intro
 * @property string|null $size_limit_title
 * @property string|null $size_limit_intro
 * @property string|null $time_limit_title
 * @property string|null $time_limit_intro
 * @property string|null $faq_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Region|null $region
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereFaqTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereFishAvailIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereFishAvailTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereSizeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereSizeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereTimeLimitIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereTimeLimitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegionTranslation whereUpdatedAt($value)
 */
	class RegionTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $thumbnail_path
 * @property array<array-key, mixed>|null $gallery_images
 * @property string $location
 * @property string $city
 * @property string $country
 * @property string|null $region
 * @property numeric|null $lat
 * @property numeric|null $lng
 * @property string $boat_type
 * @property int|null $max_persons
 * @property string $desc_of_boat
 * @property array<array-key, mixed>|null $requirements
 * @property array<array-key, mixed>|null $boat_information
 * @property array<array-key, mixed>|null $boat_extras
 * @property string $price_type
 * @property array<array-key, mixed> $prices
 * @property array<array-key, mixed>|null $pricing_extra
 * @property array<array-key, mixed>|null $inclusions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GuidingBoatType|null $boatType
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereBoatExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereBoatInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereBoatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereDescOfBoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereInclusions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereMaxPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat wherePrices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat wherePricingExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoat whereUserId($value)
 */
	class RentalBoat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_en
 * @property string $input_type
 * @property string|null $placeholder
 * @property string|null $placeholder_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereInputType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement wherePlaceholder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement wherePlaceholderEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentalBoatRequirement whereUpdatedAt($value)
 */
	class RentalBoatRequirement extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @property bool $is_automatic
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @property-read \App\Models\User $guide
 * @property-read \App\Models\Guiding $guiding
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereGrandtotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereGuideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereGuideScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsAutomatic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereOverallScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRegionWaterScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUserId($value)
 */
	class Review extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoomConfiguration whereUpdatedAt($value)
 */
	class RoomConfiguration extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @property numeric|null $total_budget_to_spend
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereDateFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereDateTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereDaysOfBoatRental($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereDaysOfGuiding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereFishingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereIsBestFishingTimeRecommendation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereIsBoatRental($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereIsGuided($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereNumberOfGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereTotalBudgetToSpend($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchRequest whereUpdatedAt($value)
 */
	class SearchRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $location
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $country
 * @property string|null $city
 * @property string|null $region
 * @property string|null $thumbnail_path
 * @property array<array-key, mixed>|null $gallery_images
 * @property array<array-key, mixed>|null $whats_included
 * @property array<array-key, mixed>|null $pricing
 * @property string|null $price_type
 * @property string $currency
 * @property string $status
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Camp> $camps
 * @property-read int|null $camps_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RentalBoat> $rentalBoats
 * @property-read int|null $rental_boats_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer wherePricing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpecialOffer whereWhatsIncluded($value)
 */
	class SpecialOffer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryPage|null $categoryPage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereUpdatedAt($value)
 */
	class Target extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Thread whereUpdatedAt($value)
 */
	class Thread extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $location
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $country
 * @property string|null $city
 * @property string|null $region
 * @property string|null $thumbnail_path
 * @property array<array-key, mixed>|null $gallery_images
 * @property array<array-key, mixed>|null $target_species
 * @property array<array-key, mixed>|null $fishing_methods
 * @property string|null $fishing_style
 * @property array<array-key, mixed>|null $water_types
 * @property array<array-key, mixed>|null $skill_level
 * @property int|null $duration_nights
 * @property int|null $duration_days
 * @property int|null $group_size_min
 * @property int|null $group_size_max
 * @property array<array-key, mixed>|null $trip_schedule
 * @property string|null $meeting_point
 * @property string|null $best_season_from
 * @property string|null $best_season_to
 * @property array<array-key, mixed>|null $catering
 * @property string|null $best_arrival_options
 * @property string|null $arrival_day
 * @property string|null $boat_type
 * @property array<array-key, mixed>|null $boat_features
 * @property string|null $boat_information
 * @property string|null $accommodation_description
 * @property string|null $accommodation_type
 * @property array<array-key, mixed>|null $room_types
 * @property string|null $distance_to_water
 * @property string|null $nearest_airport
 * @property string|null $provider_name
 * @property string|null $provider_photo
 * @property string|null $provider_experience
 * @property string|null $provider_certifications
 * @property string|null $boat_staff
 * @property array<array-key, mixed>|null $guide_languages
 * @property string|null $description
 * @property array<array-key, mixed>|null $trip_highlights
 * @property array<array-key, mixed>|null $included
 * @property array<array-key, mixed>|null $excluded
 * @property array<array-key, mixed>|null $additional_info
 * @property string|null $cancellation_policy
 * @property numeric|null $price_per_person
 * @property numeric|null $price_single_room_addition
 * @property string|null $downpayment_policy
 * @property string|null $currency
 * @property string $status
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TripAvailabilityDate> $availabilityDates
 * @property-read int|null $availability_dates_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereAccommodationDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereAccommodationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereArrivalDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBestArrivalOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBestSeasonFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBestSeasonTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBoatFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBoatInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBoatStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereBoatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCancellationPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCatering($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDistanceToWater($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDownpaymentPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDurationDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDurationNights($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereExcluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereFishingMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereFishingStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereGalleryImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereGroupSizeMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereGroupSizeMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereGuideLanguages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereIncluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereMeetingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereNearestAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip wherePricePerPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip wherePriceSingleRoomAddition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereProviderCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereProviderExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereProviderPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereRoomTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereSkillLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereTargetSpecies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereTripHighlights($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereTripSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereWaterTypes($value)
 */
	class Trip extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $trip_id
 * @property \Illuminate\Support\Carbon $departure_date
 * @property int $spots_available
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Trip $trip
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereDepartureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereSpotsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripAvailabilityDate whereUpdatedAt($value)
 */
	class TripAvailabilityDate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $source_type
 * @property int $source_id
 * @property \Illuminate\Support\Carbon $preferred_date
 * @property int $number_of_persons
 * @property string $name
 * @property string $email
 * @property string $phone_country_code
 * @property string $phone
 * @property string $message
 * @property string|null $admin_comment
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FinanceItem|null $financeItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereAdminComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereNumberOfPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking wherePreferredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TripBooking whereUpdatedAt($value)
 */
	class TripBooking extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBanktransferAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBanktransferdetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBarAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsGuide($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsTempPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMerchantBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMerchantComplianceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMerchantStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMerchantVerificationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNumberOfGuides($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePaidBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePaypalAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePaypaldetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePendingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePercentagePriceIncrease($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserInformationId($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest wherePostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereSalutation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserGuest whereUpdatedAt($value)
 */
	class UserGuest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $feed_token
 * @property string $otp_secret
 * @property string $feed_type
 * @property array<array-key, mixed>|null $feed_settings
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_accessed_at
 * @property int $access_count
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $feed_type_display
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed byUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereAccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereFeedSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereFeedToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereFeedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereLastAccessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereOtpSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserICalFeed whereUserId($value)
 */
	class UserICalFeed extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereAboutMe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereAddressNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereFavoriteFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereFishingPermitFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereFishingStartYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereLanguages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation wherePostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereProofOfIdentityFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereRequestAsGuide($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInformation whereUpdatedAt($value)
 */
	class UserInformation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $location
 * @property string|null $city
 * @property string $country
 * @property string $latitude
 * @property string $longitude
 * @property string|null $region
 * @property array<array-key, mixed>|null $gallery
 * @property array<array-key, mixed> $best_travel_times
 * @property string $surroundings_description
 * @property array<array-key, mixed> $target_fish
 * @property string|null $airport_distance
 * @property string|null $water_distance
 * @property string|null $shopping_distance
 * @property string|null $travel_included
 * @property array<array-key, mixed>|null $travel_options
 * @property bool|null $pets_allowed
 * @property bool|null $smoking_allowed
 * @property bool|null $disability_friendly
 * @property bool $has_boat
 * @property bool $has_guiding
 * @property array<array-key, mixed>|null $additional_services
 * @property array<array-key, mixed>|null $included_services
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation radius($latitude, $longitude, $radius)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereAdditionalServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereAirportDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereBestTravelTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereContentUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereDisabilityFriendly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereGallery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereHasBoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereHasGuiding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereIncludedServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation wherePetsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereShoppingDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereSmokingAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereSurroundingsDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereTargetFish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereTravelIncluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereTravelOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vacation whereWaterDistance($value)
 */
	class Vacation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array<array-key, mixed>|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation|null $vacation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationAccommodation whereVacationId($value)
 */
	class VacationAccommodation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array<array-key, mixed>|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation|null $vacation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBoat whereVacationId($value)
 */
	class VacationBoat extends \Eloquent {}
}

namespace App\Models{
/**
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
 * @property array<array-key, mixed>|null $extra_offers
 * @property numeric $total_price
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\VacationAccommodation|null $accommodation
 * @property-read \App\Models\VacationBoat|null $boat
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VacationExtra> $extras
 * @property-read int|null $extras_count
 * @property-read \App\Models\VacationGuiding|null $guiding
 * @property-read \App\Models\VacationPackage|null $package
 * @property-read \App\Models\Vacation|null $vacation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereAccommodationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereBoatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereBookingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereExtraOffers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereHasPets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereNumberOfPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking wherePhoneCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking wherePostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationBooking whereVacationId($value)
 */
	class VacationBooking extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vacation_id
 * @property string $type
 * @property string $description
 * @property numeric $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation|null $vacation
 * @property-read \App\Models\VacationBooking|null $vacationBooking
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationExtra whereVacationId($value)
 */
	class VacationExtra extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array<array-key, mixed>|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation|null $vacation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationGuiding whereVacationId($value)
 */
	class VacationGuiding extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vacation_id
 * @property string|null $title
 * @property string $description
 * @property int $capacity
 * @property array<array-key, mixed>|null $dynamic_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vacation|null $vacation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereDynamicFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VacationPackage whereVacationId($value)
 */
	class VacationPackage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Water whereUpdatedAt($value)
 */
	class Water extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $guiding_id
 * @property-read \App\Models\Guiding|null $guiding
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereUserId($value)
 */
	class WishlistItem extends \Eloquent {}
}

