<?php

namespace App\Http\Controllers\Admin\Category;

use App\Domain\Destination\DestinationCategoryType;
use App\Http\Controllers\Controller;
use App\Services\Destination\DestinationCategoryAdminService;
use Exception;
use Illuminate\Http\Request;

class AdminCategoryTripLocationController extends Controller
{
    private const FORM_LABEL = 'Trip location (category page)';

    public function __construct(
        private DestinationCategoryAdminService $categories
    ) {}

    public function index()
    {
        $rows = $this->categories->paginateForType(DestinationCategoryType::TRIPS);

        return view('admin.pages.category.trips-locations-index', compact('rows'));
    }

    public function create()
    {
        $data = $this->categories->formDataForCreate(
            self::FORM_LABEL,
            'admin.category.trip-location.store'
        );

        return view('admin.pages.category.vacations-form', $data);
    }

    public function store(Request $request)
    {
        try {
            $this->categories->store($request, DestinationCategoryType::TRIPS);

            return redirect()->back()->with('success', 'Trip location category added.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['message' => 'Something went wrong. Please try again.']);
        }
    }

    public function edit(int $trip_location)
    {
        $row = $this->categories->findForEdit($trip_location, DestinationCategoryType::TRIPS);

        if ($row === null) {
            return redirect()->back();
        }

        $data = $this->categories->formDataForEdit(
            $row,
            self::FORM_LABEL,
            'admin.category.trip-location.update'
        );

        return view('admin.pages.category.vacations-form', $data);
    }

    public function update(Request $request, int $trip_location)
    {
        try {
            $this->categories->update($request, $trip_location, DestinationCategoryType::TRIPS);

            return redirect()->back()->with('success', 'Trip location category updated.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['message' => 'Something went wrong. Please try again.']);
        }
    }

    public function destroy(int $trip_location)
    {
        $this->categories->destroy($trip_location, DestinationCategoryType::TRIPS);

        return redirect()->back()->with('success', 'Trip location category deleted.');
    }
}
