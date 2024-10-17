<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockedEventRequest;
use App\Http\Resources\EventResource;
use App\Models\BlockedEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function index(Request $request)
    {

        $blockEvents = null;
        $allblockeds = BlockedEvent::where('user_id', auth()->id())->get();

        $blockedEvents = $this->blockedEvents($allblockeds);
        return EventResource::collection($blockedEvents)->all();
    }

    public function blockedEvents($events){
        $events = $events->map(function($event) {
            $event->status = $event->getBookingStatus();

            if(empty($event->status)){
                if($event->type == 'blockiert'){
                    $event->status = $event->type;
                }
                if($event->type == 'booking'){
                    $event->status = 'Booked';
                }
            }
            return $event;
        });
        return $events;
    }

    public function store(Request $request)
    {

        // $startdate = Carbon::parse($request->get('start'));
        // $enddate = Carbon::parse($request->get('end'));
        // $dayOfWeek = $request->get('day');

        // $data = [];
        // if($dayOfWeek != 'full'){
        //     while ($startdate->lte($enddate)) {
        //         if ($startdate->dayOfWeek == $dayOfWeek) {
        //             $data[] = $startdate->toDateString();
        //         }
        //         $startdate->addDay(); // Move to the next day
        //     }
        // }

        // $blockdate = "2023-09-11";

        // $index = array_search($blockdate, $data);

        // if ($index !== false) {
        //     unset($data[$index]);
        // }


        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $dayOfWeek = $request->get('day');


        $data['user_id'] = auth()->id();
        $data['type'] = 'blockiert';
        $data['from'] = $start;
        $data['due'] = $end;


        if(is_array($dayOfWeek) && !empty($dayOfWeek)){


            foreach ($dayOfWeek as $day) {
                $currentDate = $start->copy();
            
                while ($currentDate->lte($end)) {
                    if ($currentDate->dayOfWeek == $day) {
                        BlockedEvent::create([
                            'user_id' => $data['user_id'],
                            'source' => 'global',
                            'type' => 'blockiert',
                            'from' => $currentDate->toDateString(),
                            'due' => $currentDate->toDateString(),
                        ]);
                    }
            
                    $currentDate->addDay();
                }
            }

        

            return redirect()->back()->with('success', 'Die Blockade wurde erfolgreich angelegt!');
        }else{

            if($start->timestamp < $end->timestamp) {
                $data['due'] = $end->addDay();
                BlockedEvent::create($data);
    
                return redirect()->back()->with('success', 'Die Blockade wurde erfolgreich angelegt!');
            }
        }

        return redirect()->back()->with('error', 'Start darf nicht größer als das Ende sein!');
    }

    public function getDateByDays(){

    }

    public function delete($id)
    {
        $blockedevent = BlockedEvent::where('id',$id)->where('user_id', auth()->id())->first();

        if(!$blockedevent) {
            abort(404);
        }

        $blockedevent->delete();
        return back()->with('success', 'Die Blockade wurde erfolgreich gelöscht');
    }
}
