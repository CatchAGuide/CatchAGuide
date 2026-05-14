<?php

namespace App\Console\Commands;

use App\Mail\Guide\GuideInvoiceMail;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\FinanceItemEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FinanceAutoSendGuideInvoices extends Command
{
    protected $signature = 'finance:auto-send-guide-invoices {--dry-run}';
    protected $description = 'Automatically send guide commission invoices after tour date (3/7/10 day retries).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now = now();

        $bookings = Booking::query()
            ->where('status', 'accepted')
            ->with(['guiding.user', 'financeItem', 'calendar_schedule', 'blocked_event'])
            ->get()
            ->filter(fn (Booking $b) => $b->isBookingOver());

        $sent = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            $guide = $booking->guiding?->user;
            if (!$guide || !$guide->email) {
                $skipped++;
                continue;
            }

            $bookingDate = $booking->getBookingDate();
            if (!$bookingDate instanceof Carbon) {
                $skipped++;
                continue;
            }

            $daysSince = $bookingDate->endOfDay()->diffInDays($now, false);
            if ($daysSince < 3) {
                $skipped++;
                continue;
            }

            $targetStep = $daysSince >= 10 ? 3 : ($daysSince >= 7 ? 2 : 1);

            $finance = $booking->financeItem()->firstOrCreate([], [
                'invoice_status' => 'not_sent',
                'paid_status' => 'unpaid',
            ]);

            if ($finance->invoice_status === 'sent') {
                $skipped++;
                continue;
            }

            if ((int) ($finance->reminder_step ?? 0) >= $targetStep) {
                $skipped++;
                continue;
            }

            $this->info("Booking #{$booking->id}: attempting invoice send (step {$targetStep}, {$daysSince} days since).");

            if ($dryRun) {
                $finance->reminder_step = $targetStep;
                $finance->next_reminder_at = null;
                $finance->save();
                $this->log($finance->id, 'invoice_auto_send_dry_run', [
                    'booking_id' => $booking->id,
                    'step' => $targetStep,
                    'days_since' => $daysSince,
                ]);
                $sent++;
                continue;
            }

            try {
                Mail::to($guide->email)
                    ->locale($guide->language ?? app()->getLocale())
                    ->send(new GuideInvoiceMail($booking));

                $finance->invoice_status = 'sent';
                $finance->invoice_sent_at = $finance->invoice_sent_at ?? now();
                $finance->invoice_due_at = $finance->invoice_due_at ?? now()->addDays((int) config('finance.invoice_due_days', 10));
                $finance->reminder_step = $targetStep;
                $finance->last_reminder_sent_at = now();
                $finance->next_reminder_at = null;
                $finance->save();

                // Legacy field for existing UI flows
                $booking->guide_invoice_sent_at = $booking->guide_invoice_sent_at ?? now();
                $booking->is_guide_billed = true;
                $booking->guide_billed_at = $booking->guide_billed_at ?? now();
                $booking->save();

                $this->log($finance->id, 'invoice_auto_sent', [
                    'booking_id' => $booking->id,
                    'step' => $targetStep,
                    'days_since' => $daysSince,
                    'email' => $guide->email,
                ]);

                $sent++;
            } catch (\Throwable $e) {
                $finance->reminder_step = $targetStep;
                $finance->next_reminder_at = null;
                $finance->save();

                $this->error("Failed sending invoice for booking #{$booking->id}: {$e->getMessage()}");
                $this->log($finance->id, 'invoice_auto_send_failed', [
                    'booking_id' => $booking->id,
                    'step' => $targetStep,
                    'days_since' => $daysSince,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. Sent/updated: {$sent}. Skipped: {$skipped}.");
        return self::SUCCESS;
    }

    private function log(int $financeItemId, string $eventType, array $payload): void
    {
        FinanceItemEvent::create([
            'finance_item_id' => $financeItemId,
            'event_type' => $eventType,
            'payload' => $payload,
            'actor_type' => Employee::class,
            'actor_id' => null,
        ]);
    }
}

