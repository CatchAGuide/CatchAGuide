<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Sales\SalesDocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\SalesDocument;
use App\Models\TripBooking;
use App\Presenters\Sales\SalesDocumentRowPresenter;
use App\Services\Sales\SalesCatalog;
use App\Services\Sales\SalesDocumentCustomerActions;
use App\Services\Sales\SalesDocumentDuplicator;
use App\Services\Sales\SalesDocumentFromRequest;
use App\Services\Sales\SalesDocumentListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin › Sales › Offers: the list, the builder page (Livewire) and the row actions.
 */
class SalesDocumentsController extends Controller
{
    public function index(Request $request, SalesDocumentListQuery $query, SalesDocumentRowPresenter $rows): View
    {
        $filters = $request->only(['status', 'creator', 'from', 'to', 'q']);
        $paginator = $query->paginate($filters);

        return view('admin.pages.sales.index', [
            'filters' => $filters,
            'paginator' => $paginator,
            'documents' => collect($paginator->items())->map(fn (SalesDocument $document) => $rows->present($document)),
            'statuses' => SalesDocumentStatus::cases(),
            'creators' => Employee::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(SalesCatalog $catalog): View
    {
        return $this->builder(null, $catalog);
    }

    public function edit(SalesDocument $document, SalesCatalog $catalog): View
    {
        return $this->builder($document, $catalog);
    }

    /**
     * "Create offer" on a camp request: a draft pre-filled from the request (or its existing offer).
     */
    public function fromCampRequest(Request $request, CampVacationBooking $campVacationBooking, SalesDocumentFromRequest $converter): RedirectResponse
    {
        return redirect()->route('admin.sales.offers.edit', $converter->fromCampRequest($campVacationBooking, $request->user('employees')));
    }

    /**
     * "Create offer" on a trip request: a draft pre-filled from the request (or its existing offer).
     */
    public function fromTripRequest(Request $request, TripBooking $tripBooking, SalesDocumentFromRequest $converter): RedirectResponse
    {
        return redirect()->route('admin.sales.offers.edit', $converter->fromTripRequest($tripBooking, $request->user('employees')));
    }

    public function duplicate(Request $request, SalesDocument $document, SalesDocumentDuplicator $duplicator): RedirectResponse
    {
        $copy = $duplicator->duplicate($document, $request->user('employees'));

        return redirect()->route('admin.sales.offers.edit', $copy)->with('sales_notice', __('sales.list.duplicated', ['number' => $document->number]));
    }

    public function decline(Request $request, SalesDocument $document, SalesDocumentCustomerActions $actions): RedirectResponse
    {
        return $this->afterAction($actions->decline($document, $request->user('employees')), 'sales.list.declined', $document);
    }

    public function cancel(Request $request, SalesDocument $document, SalesDocumentCustomerActions $actions): RedirectResponse
    {
        return $this->afterAction($actions->cancel($document, $request->user('employees')), 'sales.list.cancelled', $document);
    }

    private function afterAction(bool $done, string $message, SalesDocument $document): RedirectResponse
    {
        return back()->with($done ? 'sales_notice' : 'sales_error', $done ? __($message, ['number' => $document->number]) : __('sales.list.action_not_allowed'));
    }

    private function builder(?SalesDocument $document, SalesCatalog $catalog): View
    {
        return view('admin.pages.sales.builder', [
            'document' => $document,
            'options' => [
                SalesCatalog::TOUR => $catalog->options(SalesCatalog::TOUR),
                SalesCatalog::CAMP => $catalog->options(SalesCatalog::CAMP),
                SalesCatalog::TRIP => $catalog->options(SalesCatalog::TRIP),
            ],
        ]);
    }
}
