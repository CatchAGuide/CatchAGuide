<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormRequest;
use App\Models\Camper;
use App\Models\Equipment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Storage;

class CamperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $campers = Camper::orderByDesc('created_at')->get();
        return view('camper.index', compact('campers'));
    }

    public function adminindex()
    {
        $campers = Camper::orderByDesc('created_at')->get();
        return view('backend.pages.index', compact('campers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('backend.forms.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(FormRequest $request)
    {
        $data = $request->validated();
        $camper = Camper::create($data);

        foreach ($request->file('files') as $file) {
            $hashedName = sha1($file->getClientOriginalName() . '_' . time());
            $filename = $hashedName . '_' . $file->getClientOriginalExtension();

            $file_path = Storage::disk('public')->putFileAs('images', $file, $filename);

            $camper->images()->create(['file_path' => $file_path]);
        }

        $equipment = $camper->equipment()->create();

        foreach ($data['check'] as $key => $value) {
            $equipment->$key = true;
        }

        $equipment->save();

        return redirect('/admin/index');
    }


    /**
     * Display the specified resource.
     *
     * @param Camper $camper
     * @return Application|Factory|View|Response
     */
    public function show($id)
    {
        $campers = Camper::findOrFail($id);
        return view('camper.show', compact('campers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id)
    {
        $camper = Camper::findOrFail($id);
        return view('backend.forms.edit', compact('camper'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->except('check');

        $camper = Camper::findOrFail($id);
        $camper->update($data);

        $equipment = $camper->equipment;
        $attributes = $equipment->getAttributes();
        unset($attributes['id'], $attributes['camper_id'], $attributes['created_at'], $attributes['updated_at']);

        foreach ($attributes as $key => $value) {
            $equipment->$key = isset($request->check[$key]);
        }

        $equipment->save();

        if ($request->hasFile('files')) {
            $camper->images()->delete();

            foreach ($request->file('files') as $file) {
                $hashedName = sha1($file->getClientOriginalName() . '_' . time());
                $filename = $hashedName . '_' . $file->getClientOriginalExtension();

                $file_path = Storage::disk('public')->putFileAs('images', $file, $filename);

                $camper->images()->create(['file_path' => $file_path]);
            }

        }

        return redirect('/admin/index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Application|RedirectResponse|Redirector
     */
    public function destroy($id)
    {
        $camper = Camper::findOrFail($id);
        $camper->delete();

        return redirect('/admin/index');
    }
}
