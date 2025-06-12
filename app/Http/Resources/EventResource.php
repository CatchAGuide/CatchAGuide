<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

use App\Http\Resources\BookingResource;


/** @mixin \App\Models\BlockedEvent */
class EventResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        if(!empty($this->status) || $this->status == 'blockiert'){
            $color = null;
            $backgroundColor = null;	
    
            switch ($this->status) {
                case 'Booked':
                    $color = '#257e4a';
                    break;
                case 'cancelled':
                    $color = 'red';
                    break;
                case 'pending':
                    $color = 'yellow';
                    break;
                default:
                    
                   break;
            };
            
            return [
                'id' => $this->id,
                'start' => $this->from,
                'end' => $this->due,
                // 'title' => $this->type,
                'title' => $this->status ? $this->status : '',
                'rendering' => 'background',
                'color' => $color,
                'eventColor' => 'yellow',
                'booking' => [
                    'data' => $this->booking,
                ],
                'guiding' => [
                    'data' => $this->booking ? $this->booking->guiding : null,    
                ],
                'user' => [
                    'data' => $this->booking ? $this->booking->user : null,  
                ]
            ];
        }

    }
}
