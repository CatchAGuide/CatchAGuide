<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
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
        // dd($customer);
        return view('admin.pages.customers.edit', [
            'customer' => $customer
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer)
    {
        $data = $request->all();
        // Handle information data separately
        $informationData = $request->input('information') ?? [];
        unset($data['information']);
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/profile_images'), $imageName);
            
            // If there's an existing image, you might want to delete it
            if ($customer->profile_image && file_exists(public_path('uploads/profile_images/' . $customer->profile_image))) {
                unlink(public_path('uploads/profile_images/' . $customer->profile_image));
            }
            
            $data['profile_image'] = $imageName;
        }
        
        // Update user data
        $customer->update($data);
        
        // Create or update user information
        if (!$customer->information) {
            $userInfo = new \App\Models\UserInformation($informationData);
            $userInfo->save();
            $customer->user_information_id = $userInfo->id;
            $customer->save();
        } else {
            $customer->information->update($informationData);
        }
        
        // Update payment methods if provided
        if (isset($data['bar_allowed'])) {
            $customer->bar_allowed = $data['bar_allowed'] ? 1 : 0;
        }
        
        if (isset($data['banktransfer_allowed'])) {
            $customer->banktransfer_allowed = $data['banktransfer_allowed'] ? 1 : 0;
        }
        
        if (isset($data['paypal_allowed'])) {
            $customer->paypal_allowed = $data['paypal_allowed'] ? 1 : 0;
        }
        
        $customer->save();

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
