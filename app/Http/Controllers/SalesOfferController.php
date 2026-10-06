<?php

namespace App\Http\Controllers;

use App\Enums\Sales\SalesDocumentOutput;
use App\Enums\Sales\SalesDocumentStatus;
use App\Models\SalesDocument;
use App\Presenters\Sales\SalesDocumentPresenter;
use App\Services\Sales\SalesDocumentCustomerActions;
use App\Services\Sales\SalesLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

/**
 * Customer page of an offer / booking confirmation behind its unguessable link (spec §8.2),
 * and the "accept offer" action (§8.3). Always shows the latest saved state.
 */
class SalesOfferController extends Controller
{
    public function show(Request $request, string $token, SalesDocumentPresenter $presenter, SalesDocumentCustomerActions $actions, SalesLinks $links): Response
    {
        $document = $this->find($token);
        App::setLocale($document->locale());

        // Team members opening the link from the admin don't count as the customer's first view.
        if (! $request->user('employees')) {
            $actions->markViewed($document);
        }

        $output = $document->status === SalesDocumentStatus::Confirmed ? SalesDocumentOutput::Confirmation : SalesDocumentOutput::Offer;

        return response()
            ->view('sales.offer', [
                'doc' => $presenter->forDocument($document, $output),
                'preview' => false,
                'acceptAction' => route('sales-offers.accept', $document->public_token),
                'acceptError' => $request->session()->has('errors'),
                'justAccepted' => (bool) $request->session()->get('sales_offer_accepted'),
            ] + $links->shell($document->locale()))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function accept(Request $request, string $token, SalesDocumentCustomerActions $actions): RedirectResponse
    {
        $document = $this->find($token);
        App::setLocale($document->locale());

        $request->validate(['terms' => ['accepted']]);

        $accepted = $actions->accept($document, $request->ip());

        return redirect()
            ->route('sales-offers.show', $document->public_token)
            ->with('sales_offer_accepted', $accepted);
    }

    private function find(string $token): SalesDocument
    {
        return SalesDocument::query()->where('public_token', $token)->firstOrFail();
    }
}
