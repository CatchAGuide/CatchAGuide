<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use App\Models\UserInformation;
use App\Services\Admin\CustomerCreator;
use App\Services\Admin\ListingOwnerSearch;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index()
    {
        return view('admin.pages.customers.index', [
            'customers' => User::all()
        ]);
    }

    /**
     * JSON search for listing owner pickers (Admin › trips/camps/…).
     */
    public function search(Request $request, ListingOwnerSearch $search)
    {
        return response()->json([
            'results' => $search->search((string) $request->input('q', '')),
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.customers.index', ['create' => 1]);
    }

    public function store(StoreCustomerRequest $request, CustomerCreator $creator)
    {
        $customer = $creator->create(
            $request->validated(),
            $request->user('employees')?->id
        );

        return redirect()
            ->route('admin.customers.edit', $customer)
            ->with('success', __('admin.customers.created'));
    }

    public function show(User $customer)
    {
        //
    }

    public function edit(User $customer)
    {
        return view('admin.pages.customers.edit', [
            'customer' => $customer
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer)
    {
        $userFields = [
            'firstname',
            'lastname',
            'email',
            'language',
            'banktransferdetails',
            'paypaldetails',
        ];

        $userData = collect($userFields)
            ->filter(fn (string $field) => $request->has($field))
            ->mapWithKeys(fn (string $field) => [$field => $request->input($field)])
            ->all();

        foreach (['bar_allowed', 'banktransfer_allowed', 'paypal_allowed'] as $checkbox) {
            if ($request->has($checkbox)) {
                $userData[$checkbox] = $request->boolean($checkbox);
            }
        }

        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            $uploadPath = public_path('uploads/profile_images');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $image->move($uploadPath, $imageName);

            if ($customer->profil_image && file_exists(public_path('uploads/profile_images/' . $customer->profil_image))) {
                unlink(public_path('uploads/profile_images/' . $customer->profil_image));
            }

            $customer->profil_image = $imageName;
        }

        if ($userData !== []) {
            $customer->fill($userData);
        }

        if ($request->has('tax_id')) {
            $customer->tax_id = $request->input('tax_id');
        }

        if ($customer->isDirty()) {
            $customer->save();
        }

        if ($request->has('information') && is_array($request->input('information'))) {
            $informationData = collect($request->input('information'))
                ->only((new UserInformation())->getFillable())
                ->all();

            if ($informationData !== []) {
                if (!$customer->information) {
                    $userInfo = UserInformation::create($informationData);
                    $customer->user_information_id = $userInfo->id;
                    $customer->save();
                } else {
                    $customer->information->update($informationData);
                }
            }
        }

        return redirect()->route('admin.customers.index')->with('success', 'Kunde wurde erfolgreich aktualisiert');
    }


    public function destroy(User $customer)
    {
        //
    }

    public function customersdelete($customerid)
    {
        $customer = User::find($customerid);
        $customer->delete();
        return back()->with('success', "Der Kunde wurde erfolgreich gelöscht");
    }
}
