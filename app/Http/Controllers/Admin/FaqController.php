<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{

    public function home(){
        $frequentlyAskedQuestions = Faq::where('page','=','home')->get();
      
        return view('admin.pages.faq.index', [
            'frequentlyAskedQuestions' => $frequentlyAskedQuestions,
            'page' => 'home'
        ]);
    }

    public function searchRequest(){
        $frequentlyAskedQuestions = Faq::where('page','=','search-request')->get();
       
        return view('admin.pages.faq.index', [
            'frequentlyAskedQuestions' => $frequentlyAskedQuestions,
            'page' => 'search-request'
        ]);
    }

    // public function index()
    // {
    //     return view('admin.pages.faq.index', [
    //         'faqs' => Faq::all()
    //     ]);
    // }

    public function create($page)
    {
        return view('admin.pages.faq.create',compact('page'));
    }

    public function store(StoreFaqRequest $request)
    {
        $data = $request->validated();
        
        Faq::create($data);

        if($data['page'] == 'home'){
            return redirect()->route('admin.faq.home')->with('success', 'Das FAQ wurde erfolgreich angelegt!');
        }

        if($data['page'] == 'search-request'){
            return redirect()->route('admin.faq.searchrequest')->with('success', 'Das FAQ wurde erfolgreich angelegt!');
        }
   
  
    }

    public function show(Faq $faq)
    {
        //
    }

    public function edit(Faq $faq,$page)
    {

        return view('admin.pages.faq.edit', [
            'faq' => $faq,
            'page' => $page
        ]);
    }

    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $data = $request->validated();

        $faq->update($data);

        if($data['page'] == 'home'){
            return redirect()->route('admin.faq.home')->with('success', 'Das FAQ wurde erfolgreich editiert!');
        }

        if($data['page'] == 'search-request'){
            return redirect()->route('admin.faq.searchrequest')->with('success', 'Das FAQ wurde erfolgreich editiert!');
        }
    }

    public function destroy(Faq $faq)
    {
        
        $faq->delete();

        return back()->with('success', 'Successfully Deleted');
    }
}
