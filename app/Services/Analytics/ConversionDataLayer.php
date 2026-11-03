<?php

namespace App\Services\Analytics;

use App\Models\Booking;
use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Models\VoucherOrder;

/**
 * Data the thank-you pages hand to GTM before it loads, so the Page View conversion tags (Google Ads, GA4)
 * can read the booking's value, a de-duplication id and the hashed email for Ads enhanced conversions.
 * GTM reads it through the Data Layer Variables conversion.* and user_data.sha256_email_address.
 */
class ConversionDataLayer
{
    public function forTourBooking(Booking $booking): array
    {
        return $this->payload('tour', $booking->id, $booking->price, 'EUR', $booking->email);
    }

    public function forCampRequest(CampVacationBooking $booking): array
    {
        return $this->payload('camp', $booking->id, $booking->estimated_total, $booking->currency, $booking->email);
    }

    public function forTripRequest(TripBooking $booking): array
    {
        return $this->payload('trip', $booking->id, $booking->estimated_total, $booking->currency, $booking->email);
    }

    public function forVoucherOrder(VoucherOrder $order): array
    {
        return $this->payload('voucher', $order->id, $order->price_total, $order->currency, $order->buyer_email);
    }

    /**
     * The transaction id carries the type so a tour and a camp request with the same row id stay two
     * conversions. A missing value is left out, so the Ads conversion falls back to its default value.
     */
    private function payload(string $type, int $id, mixed $value, ?string $currency, ?string $email): array
    {
        $conversion = [
            'product_type' => $type,
            'transaction_id' => $type.'-'.$id,
            'currency' => strtoupper($currency ?: 'EUR'),
        ];

        if (is_numeric($value) && (float) $value > 0) {
            $conversion['value'] = round((float) $value, 2);
        }

        $payload = ['conversion' => $conversion];

        $hash = $this->hashEmail($email);
        if ($hash !== null) {
            $payload['user_data'] = ['sha256_email_address' => $hash];
        }

        return $payload;
    }

    /**
     * Google's normalisation for enhanced conversions: trim, lowercase, and drop dots before the @ of
     * gmail.com / googlemail.com addresses. Only the hash reaches the page, never the address.
     */
    public function hashEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);
        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $local = str_replace('.', '', $local);
        }

        return hash('sha256', $local.'@'.$domain);
    }
}
