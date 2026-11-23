<?php

namespace Database\Seeders;

use App\Enums\GuideStatus;
use App\Models\Booking;
use App\Models\CalendarSchedule;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\VoucherOrder;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

/**
 * LOCAL ONLY. A non-guide test customer with bookings of every kind, to try the customer pages
 * of /profile/* (Guide Area design) by hand:
 *
 *   php artisan db:seed --class=CustomerAreaDemoSeeder
 *
 * Sign in on a local host (http://127.0.0.1:8000 via `php artisan serve`) with CUSTOMER_EMAIL /
 * CUSTOMER_PASSWORD below (or GUIDE_EMAIL / GUIDE_PASSWORD for the demo guide). Rerunning first removes everything it created before; to only remove
 * it: php artisan tinker --execute="(new Database\Seeders\CustomerAreaDemoSeeder)->clean()".
 *
 * It creates its own demo guide and tour, so no real guide's calendar is touched. Rows are
 * written with model events off and mail/notifications faked: the local .env sends real mail.
 */
class CustomerAreaDemoSeeder extends Seeder
{
    public const CUSTOMER_EMAIL = 'customer-area-demo@example.test';
    public const CUSTOMER_PASSWORD = 'Demo-Customer-2026!';
    public const GUIDE_EMAIL = 'customer-area-demo-guide@example.test';
    // The demo guide can sign in too, to try the guides' Account & security page.
    public const GUIDE_PASSWORD = 'Demo-Guide-2026!';
    private const TOKEN_PREFIX = 'cust-demo-';

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('CustomerAreaDemoSeeder runs on local only.');
        }

        Mail::fake();
        Notification::fake();

        Model::withoutEvents(function () {
            DB::transaction(function () {
                $this->clean();

                $customer = $this->user(self::CUSTOMER_EMAIL, [
                    'firstname' => 'Mia', 'lastname' => 'Demo', 'is_guide' => 0, 'guide_status' => null,
                    'password' => Hash::make(self::CUSTOMER_PASSWORD), 'phone' => '1705551234', 'phone_country_code' => '+49',
                ]);
                $guide = $this->user(self::GUIDE_EMAIL, [
                    'firstname' => 'Lars', 'lastname' => 'Demoguide', 'is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED,
                    'password' => Hash::make(self::GUIDE_PASSWORD), 'phone' => '1709998877', 'phone_country_code' => '+49',
                    'guide_type' => 'private', 'account_verification_status' => 'verified', 'account_verified_at' => now(),
                ]);

                $tour = $this->tour($guide, 'Demo: Pike fishing on the Elbe', 'Hamburg');
                $second = $this->tour($guide, 'Demo: Zander evening tour', 'Lübeck');

                $today = Carbon::today();
                $this->booking($tour, $customer, 'pending', $today->copy()->addDays(9), ['expires_at' => now()->addDays(2)]);
                $this->booking($tour, $customer, 'accepted', $today->copy()->addDays(21), ['count_of_users' => 3]);
                $this->booking($second, $customer, 'accepted', $today->copy()->subDays(12));
                $this->booking($second, $customer, 'rejected', $today->copy()->addDays(5), [
                    'alternative_dates' => json_encode([$today->copy()->addDays(12)->toDateString(), $today->copy()->addDays(14)->toDateString()]),
                    'additional_information' => 'Sorry, my boat is in the yard that week. Either of these dates works for me.',
                ]);
                $this->booking($tour, $customer, 'cancelled', $today->copy()->addDays(30), [
                    'cancel_reason' => 'weather', 'cancelled_by' => 'guide', 'cancelled_at' => now(),
                ]);

                $contact = ['name' => 'Mia Demo', 'first_name' => 'Mia', 'last_name' => 'Demo', 'email' => self::CUSTOMER_EMAIL,
                    'phone' => '1705551234', 'phone_country_code' => '+49', 'user_id' => $customer->id, 'language' => 'en'];

                if ($camp = Camp::query()->whereNotNull('slug')->first()) {
                    CampVacationBooking::create($contact + [
                        'source_type' => 'camp', 'source_id' => $camp->id, 'preferred_date' => $today->copy()->addMonths(2),
                        'nights' => 7, 'number_of_persons' => 4, 'estimated_total' => 1890, 'currency' => 'EUR',
                        'message' => 'We are four anglers, mainly interested in pike.', 'status' => 'open',
                    ]);
                }
                if ($trip = Trip::query()->whereNotNull('slug')->first()) {
                    TripBooking::create($contact + [
                        'source_type' => 'trip', 'source_id' => $trip->id, 'preferred_date' => $today->copy()->addMonths(3),
                        'preferred_date_to' => $today->copy()->addMonths(3)->addDays(6), 'number_of_persons' => 2,
                        'estimated_total' => 2400, 'currency' => 'EUR', 'message' => 'Any week in this window works.', 'status' => 'in_process',
                    ]);
                }

                $voucher = new VoucherOrder;
                $voucher->forceFill([
                    'voucher_code' => strtoupper(self::TOKEN_PREFIX).str()->random(6), 'kind' => 'order', 'status' => 'open',
                    'guiding_id' => $tour->id, 'guide_id' => $guide->id, 'user_id' => $customer->id,
                    'tour_title_snapshot' => $tour->title, 'number_of_guests' => 2, 'price_total' => 380, 'currency' => 'EUR',
                    'recipient_names' => 'Max & Anna', 'buyer_first_name' => 'Mia', 'buyer_last_name' => 'Demo',
                    'buyer_email' => self::CUSTOMER_EMAIL, 'buyer_phone' => '1705551234', 'buyer_phone_country_code' => '+49',
                    'language' => 'en', 'valid_until' => $today->copy()->addYear(),
                ])->save();

                WishlistItem::create(['user_id' => $customer->id, 'guiding_id' => $tour->id]);
                WishlistItem::create(['user_id' => $customer->id, 'guiding_id' => $second->id]);
            });
        });

        $this->command?->info('Customer area demo ready: '.self::CUSTOMER_EMAIL.' (password in CustomerAreaDemoSeeder).');
    }

    /** Removes everything this seeder created. */
    public function clean(): void
    {
        $users = User::query()->whereIn('email', [self::CUSTOMER_EMAIL, self::GUIDE_EMAIL])->get();
        if ($users->isEmpty()) {
            return;
        }

        $ids = $users->pluck('id');
        $tourIds = Guiding::query()->whereIn('user_id', $ids)->pluck('id');

        Booking::query()->where('token', 'like', self::TOKEN_PREFIX.'%')->delete();
        CalendarSchedule::query()->whereIn('guiding_id', $tourIds)->delete();
        CampVacationBooking::query()->whereIn('user_id', $ids)->delete();
        TripBooking::query()->whereIn('user_id', $ids)->delete();
        VoucherOrder::query()->whereIn('user_id', $ids)->orWhereIn('guide_id', $ids)->delete();
        WishlistItem::query()->whereIn('user_id', $ids)->delete();
        Guiding::query()->whereIn('id', $tourIds)->delete();
        DB::table('sessions')->whereIn('user_id', $ids)->delete();
        UserInformation::query()->whereIn('id', $users->pluck('user_information_id')->filter())->delete();
        User::query()->whereIn('id', $ids)->delete();
    }

    private function user(string $email, array $attributes): User
    {
        $info = UserInformation::create(['city' => 'Hamburg']);
        $user = new User;
        $user->forceFill($attributes + ['email' => $email, 'language' => 'en', 'user_information_id' => $info->id, 'email_verified_at' => now()])->save();

        return $user;
    }

    private function tour(User $guide, string $title, string $location): Guiding
    {
        $guiding = new Guiding;
        $guiding->forceFill([
            'title' => $title, 'slug' => str($title)->slug().'-'.$guide->id, 'location' => $location, 'city' => $location,
            'status' => 1, 'max_guests' => 4, 'duration' => 6, 'price' => 380, 'price_type' => 'per_tour',
            'meeting_point' => 'Harbour gate 3', 'fishing_type_id' => FishingType::query()->value('id'), 'user_id' => $guide->id,
        ])->save();

        return $guiding;
    }

    private function booking(Guiding $tour, User $customer, string $status, Carbon $date, array $extra = []): Booking
    {
        $schedule = CalendarSchedule::create(['type' => 'tour_request', 'date' => $date->toDateString(), 'guiding_id' => $tour->id, 'user_id' => $tour->user_id]);
        $booking = new Booking;
        $booking->forceFill(array_merge([
            'guiding_id' => $tour->id, 'blocked_event_id' => $schedule->id, 'status' => $status,
            'token' => self::TOKEN_PREFIX.str()->random(24), 'is_guest' => false, 'user_id' => $customer->id,
            'email' => self::CUSTOMER_EMAIL, 'phone' => '1705551234', 'phone_country_code' => '+49', 'count_of_users' => 2,
            'price' => 380, 'cag_percent' => 38, 'book_date' => $date->toDateString(),
            'created_at' => now()->subDays(3), 'updated_at' => now(),
        ], $extra))->save();

        return $booking;
    }
}
