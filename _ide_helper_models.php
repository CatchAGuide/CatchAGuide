<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\BlockedEvent
 *
 * @property int $id
 * @property string $from
 * @property string $due
 * @property string $type
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\BlockedEventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockedEvent whereUserId($value)
 */
	class BlockedEvent extends \Eloquent {}
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
 * @property-read \App\Models\BlockedEvent|null $blocked_event
 * @property-read \App\Models\Guiding|null $guiding
 * @property-read \App\Models\Rating|null $rating
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\BookingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBlockedEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCagPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCountOfUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereGuidingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereRatingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereTotalExtraPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUserId($value)
 */
	class Booking extends \Eloquent {}
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
 * App\Models\Faq
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereUpdatedAt($value)
 */
	class Faq extends \Eloquent {}
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
 * App\Models\Guiding
 *
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string $location
 * @property int $recommended_for_anfaenger
 * @property int $recommended_for_fortgeschrittene
 * @property int $recommended_for_profis
 * @property string|null $water
 * @property string|null $water_sonstiges
 * @property string|null $targets
 * @property string|null $target_fish_sonstiges
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingMethod> $methods
 * @property string|null $methods_sonstiges
 * @property int $max_guests
 * @property float $duration
 * @property string|null $required_special_license
 * @property string|null $fishing_type
 * @property string|null $fishing_from
 * @property string|null $description
 * @property string|null $required_equipment
 * @property string|null $provided_equipment
 * @property string|null $additional_information
 * @property float $price
 * @property float|null $price_two_persons
 * @property float|null $price_three_persons
 * @property float|null $price_four_persons
 * @property float|null $price_five_persons
 * @property string|null $rest_method
 * @property string|null $water_name
 * @property string|null $catering
 * @property int $status
 * @property int|null $thumbnail_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $thumbnail_path
 * @property string|null $needed_equipment
 * @property string|null $meeting_point
 * @property string|null $payment_point
 * @property int $fishing_type_id
 * @property int $fishing_from_id
 * @property int $equipment_status_id
 * @property string|null $boat_information
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \App\Models\EquipmentStatus|null $equipmentStatus
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingExtras> $extras
 * @property-read int|null $extras_count
 * @property-read \App\Models\FishingFrom|null $fishingFrom
 * @property-read \App\Models\FishingType|null $fishingTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gallery> $galleries
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingTargetFish> $target_fish
 * @property-read int|null $target_fish_count
 * @property-read \App\Models\Media|null $thumbnail
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuidingWaterType> $water_types
 * @property-read int|null $water_types_count
 * @method static \Database\Factories\GuidingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding filterByRequestValue($requestValue)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding query()
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding radius($latitude, $longitude, $radius)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereBoatInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCatering($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereEquipmentStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereFishingTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMaxGuests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMeetingPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMethods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereMethodsSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereNeededEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePaymentPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceFivePersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceFourPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceThreePersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding wherePriceTwoPersons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereProvidedEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendedForAnfaenger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendedForFortgeschrittene($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRecommendedForProfis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRequiredEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRequiredSpecialLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereRestMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTargetFishSonstiges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereThumbnailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWaterName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guiding whereWaterSonstiges($value)
 */
	class Guiding extends \Eloquent {}
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
 * @method static \Illuminate\Database\Eloquent\Builder|Newsletter whereUpdatedAt($value)
 */
	class Newsletter extends \Eloquent {}
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
 * @property string|null $created_at
 * @property string|null $updated_at
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
 * App\Models\Target
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @property-read \App\Models\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|Thread newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Thread newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Thread query()
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thread whereBody($value)
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BlockedEvent> $blocked_events
 * @property-read int|null $blocked_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatMessage> $chat_messages
 * @property-read int|null $chat_messages_count
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $given_ratings
 * @property-read int|null $given_ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guiding> $guidings
 * @property-read int|null $guidings_count
 * @property-read \App\Models\UserInformation $information
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rating> $received_ratings
 * @property-read int|null $received_ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
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
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantComplianceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMerchantVerificationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaidBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaypalAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePaypaldetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePendingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePercentagePriceIncrease($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
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
 * App\Models\UserInformation
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $birthday
 * @property string|null $address
 * @property string|null $address_number
 * @property string|null $postal
 * @property string|null $phone
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
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation wherePostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereProofOfIdentityFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereRequestAsGuide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInformation whereUpdatedAt($value)
 */
	class UserInformation extends \Eloquent {}
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

