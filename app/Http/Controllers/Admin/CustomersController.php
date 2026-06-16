<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index()
    {
        return view('admin.pages.customers.index', [
            'customers' => User::all()
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
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
        $informationData = collect($request->input('information', []))
            ->only((new UserInformation())->getFillable())
            ->all();

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

        $customer->fill([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'language' => $request->language,
            'banktransferdetails' => $request->banktransferdetails,
            'paypaldetails' => $request->paypaldetails,
            'bar_allowed' => $request->boolean('bar_allowed'),
            'banktransfer_allowed' => $request->boolean('banktransfer_allowed'),
            'paypal_allowed' => $request->boolean('paypal_allowed'),
        ]);
        $customer->tax_id = $request->input('tax_id');
        $customer->save();

        if (!$customer->information) {
            $userInfo = UserInformation::create($informationData);
            $customer->user_information_id = $userInfo->id;
            $customer->save();
        } else {
            $customer->information->update($informationData);
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
