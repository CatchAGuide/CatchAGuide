<?php

namespace App\Http\Controllers\Guide;

use App\Http\Controllers\Controller;
use App\Services\Guide\Area\GuideEarningsService;
use Illuminate\Http\Response;

/**
 * Guide Area › Invoices & commission: one of the guide's commission invoices as it was emailed
 * (today one invoice per booking; monthly PDFs follow plan decision D-1).
 */
class GuideInvoiceController extends Controller
{
    public function show(int $invoice, GuideEarningsService $earnings): Response
    {
        $item = $earnings->findInvoice(auth()->user(), $invoice);
        abort_if($item === null || ! $item->billable || ! $item->billable->guiding, 404);

        $booking = $item->billable;

        return response()
            ->view('mails.guide.guide_invoice', [
                'booking' => $booking,
                'user' => $booking->user ?? (object) ['firstname' => '', 'lastname' => ''],
                'guide' => $booking->guiding->user,
                'guiding' => $booking->guiding,
            ])
            ->header('X-Robots-Tag', 'noindex');
    }
}
