<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuidingRequest;
use App\Models\Guiding;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use Illuminate\Http\Request;

class GuidingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $guidings = Guiding::all();
        return view('admin.pages.guidings.index',compact('guidings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.guidings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function show(Guiding $guiding)
    {
        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();
        return view('admin.pages.guidings.show', compact('guiding', 'waters', 'methods', 'targets'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function edit(Guiding $guiding)
    {
        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();
        return view('admin.pages.guidings.edit', compact('guiding', 'waters', 'methods', 'targets'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGuidingRequest $request, Guiding $guiding)
    {
       $data = $request->validated();

       $guiding->update($data);

       return redirect()->route('admin.guidings.index');
    }

    public function changeGuidingStatus($id)
    {
        $guiding = Guiding::find($id);
        if($guiding->status === 1) {
            $guiding->status = 0;
        } else {
            $guiding->status = 1;
        }
        $guiding->save();
        return back()->with('success', 'Der Status wurde erfolgreich geändert');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Guiding  $guiding
     * @return \Illuminate\Http\Response
     */
    public function destroy(Guiding $guiding)
    {
        //
    }
}
