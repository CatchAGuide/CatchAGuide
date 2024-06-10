<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Models\Levels;
use App\Models\Inclussion;
use App\Models\FishingType;
use App\Models\FishingFrom;
use Illuminate\Http\Request;

class GuidingsSettingController extends Controller
{

    public function targetIndex(){
        
        $targets = Target::all();
        return view('admin.pages.setting.targets.index', compact('targets'));
    }

    public function methodIndex(){
        
        $methods = Method::all();
        return view('admin.pages.setting.methods.index', compact('methods'));
    }

    public function waterIndex(){
        
        $waters = Water::all();
        return view('admin.pages.setting.waters.index', compact('waters'));
    }

    public function inclussionIndex(){
        $inclussions = Inclussion::all();
        return view('admin.pages.setting.inclussions.index', compact('inclussions'));
    }

    public function fishingfromIndex(){
        $fishingfroms = FishingFrom::all();
        return view('admin.pages.setting.fishingfrom.index', compact('fishingfroms'));
    }

    public function fishingtypeIndex(){
        $fishingtypes = FishingType::all();
        return view('admin.pages.setting.fishingtype.index', compact('fishingtypes'));
    }

    public function levelIndex(){
        $levels = Levels::all();
        return view('admin.pages.setting.levels.index', compact('levels'));
    }



    public function index()
    {
        $methods = Method::all();
        $targets = Target::all();
        $waters = Water::all();
        $levels  = Levels::all();
        $fishingtype  = FishingType::all();
        $fishingfrom  = FishingFrom::all();

        return view('admin.pages.setting.index', compact('methods', 'targets', 'waters'));
    }

    public function storetarget(Request $request)
    {
        $target = new Target();
        $target->name = $request->name;
        $target->name_en = $request->name_en;
        $target->save();
        return back()->with('success', 'Der Zielfisch wurde erfolgreich angelegt');
    }

    public function storewater(Request $request)
    {
        $water = new Water();
        $water->name = $request->name;
        $water->name_en = $request->name_en;
        $water->save();
        return back()->with('success', 'Das Gewässer wurde erfolgreich angelegt');
    }

    public function storemethod(Request $request)
    {
        $method = new Method();
        $method->name = $request->name;
        $method->name_en = $request->name_en;
        $method->save();
        return back()->with('success', 'Die Methode wurde erfolgreich angelegt');
    }

    public function updatetarget(Request $request, $id)
    {
        $target = Target::find($id);
        $target->name = $request->name;
        $target->name_en = $request->name_en;
        $target->save();
        return back()->with('success', 'Der Zielfisch wurde erfolgreich geupdatet');
    }

    public function updatewater(Request $request, $id)
    {
        $water = Water::find($id);
        $water->name = $request->name;
        $water->name_en = $request->name_en;
        $water->save();
        return back()->with('success', 'Das Gewässer wurde erfolgreich geupdatet');
    }

    public function updatemethod(Request $request, $id)
    {
        $method = Method::find($id);
        $method->name = $request->name;
        $method->name_en = $request->name_en;
        $method->save();
        return back()->with('success', 'Die Methode wurde erfolgreich geupdatet');
    }

    public function deletetarget($id)
    {
        $target = Target::find($id);
        $target->delete();
        return back()->with('success', 'Der Zielfisch wurde erfolgreich gelöscht');
    }

    public function deletewater($id)
    {
        $water = Water::find($id);
        $water->delete();
        return back()->with('success', 'Das Gewässer wurde erfolgreich gelöscht');
    }

    public function deletemethod($id)
    {
        $method = Method::find($id);
        $method->delete();
        return back()->with('success', 'Die Methode wurde erfolgreich gelöscht');
    }

    //inclussion

    
    public function storeinclussion(Request $request)
    {
        $inclussion = new Inclussion();
        $inclussion->name = $request->name;
        $inclussion->name_en = $request->name_en;
        $inclussion->save();
        return back()->with('success', 'Das '.$inclussion->name.' wurde erfolgreich angelegt');
    }

    public function updateinclussion(Request $request, $id)
    {
        $inclussion = Inclussion::find($id);
        $inclussion->name = $request->name;
        $inclussion->name_en = $request->name_en;
        $inclussion->save();
        return back()->with('success', 'Das '.$inclussion->name.' wurde erfolgreich geupdatet');
    }


    public function deleteinclussion($id)
    {
        $inclussion = Inclussion::find($id);
        $inclussion->delete();
        return back()->with('success', 'Die inbegriffen wurde erfolgreich gelöscht');
    }

    //fishing from

    public function storefishingfrom(Request $request)
    {
        $fishingfrom = new FishingFrom();
        $fishingfrom->name = $request->name;
        $fishingfrom->name_en = $request->name_en;
        $fishingfrom->save();
        return back()->with('success', 'Das '.$fishingfrom->name.' wurde erfolgreich angelegt');
    }

    public function updatefishingfrom(Request $request, $id)
    {
        $fishingfrom = FishingFrom::find($id);
        $fishingfrom->name = $request->name;
        $fishingfrom->name_en = $request->name_en;
        $fishingfrom->save();
        return back()->with('success', 'Das '.$fishingfrom->name.' wurde erfolgreich geupdatet');
    }


    public function deletefishingfrom($id)
    {
        $fishingfrom = FishingFrom::find($id);
        $fishingfrom->delete();
        return back()->with('success', 'Die Angeln von wurde erfolgreich gelöscht');
    }

    //fishing type

    public function storefishingtype(Request $request)
    {
        $fishingtype = new FishingType();
        $fishingtype->name = $request->name;
        $fishingtype->name_en = $request->name_en;
        $fishingtype->save();
        return back()->with('success', 'Das '.$fishingtype->name.' wurde erfolgreich angelegt');
    }

    public function updatefishingtype(Request $request, $id)
    {
        $fishingtype = FishingType::find($id);
        $fishingtype->name = $request->name;
        $fishingtype->name_en = $request->name_en;
        $fishingtype->save();
        return back()->with('success', 'Das '.$fishingtype->name.' wurde erfolgreich geupdatet');
    }


    public function deletefishingtype($id)
    {
        $fishingtype = FishingType::find($id);
        $fishingtype->delete();
        return back()->with('success', 'Die Angel-Art wurde erfolgreich gelöscht');
    }

    //fishing level
    
        
    public function storelevel(Request $request)
    {
        $level = new Levels();
        $level->name = $request->name;
        $level->name_en = $request->name_en;
        $level->save();
        return back()->with('success', 'Das '.$level->name.' wurde erfolgreich angelegt');
    }

    public function updatelevel(Request $request, $id)
    {
        $level = Levels::find($id);
        $level->name = $request->name;
        $level->name_en = $request->name_en;
        $level->save();
        return back()->with('success', 'Das '.$level->name.' wurde erfolgreich geupdatet');
    }


    public function deletelevel($id)
    {
        $level = Levels::find($id);
        $level->delete();
        return back()->with('success', 'Die Ausgelegt für wurde erfolgreich gelöscht');
    }

    


}
