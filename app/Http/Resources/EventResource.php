<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

use App\Http\Resources\BookingResource;


/** @mixin \App\Models\CalendarSchedule */
class EventResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $status = $this->getBookingStatus();
        $title = $this->getDisplayTitle();
        $color = $this->getEventColor();
        
        // For blocked tour_schedule events, we still want to return them for the dots
        // but with a special marker so the frontend knows how to handle them
        if ($this->type === 'tour_schedule' && !$title) {
            return [
                'id' => $this->id,
                'start' => $this->date,
                'end' => $this->date,
                'title' => 'Blocked Tour', // Provide a default title
                'color' => $color,
                'backgroundColor' => 'transparent', // Make background transparent for dots
                'borderColor' => $color,
                'textColor' => $color,
                'allDay' => true,
                'display' => 'dot', // Special marker for dot display
                
                // Event type and status information
                'type' => $this->type,
                'status' => $status,
                'note' => $this->note,
                
                // Extended properties for modal display
                'extendedProps' => [
                    'scheduleId' => $this->id,
                    'type' => $this->type,
                    'status' => $status,
                    'note' => $this->note ?: 'Tour blocked on this date',
                    'date' => $this->date,
                    
                    // Booking information (if available)
                    'booking' => null,
                    
                    // User information (if available)
                    'user' => null,
                    
                    // Guiding information (if available)
                    'guiding' => $this->guiding ? [
                        'id' => $this->guiding->id,
                        'title' => $this->guiding->title,
                        'location' => $this->guiding->location,
                        'meeting_point' => $this->guiding->meeting_point,
                        'duration' => $this->guiding->duration,
                        'price' => $this->guiding->price,
                        'max_guests' => $this->guiding->max_guests,
                    ] : null,
                    
                    // Vacation information (if available)  
                    'vacation' => null,
                    
                    // Permissions
                    'canEdit' => $this->user_id === auth()->id(),
                    'canDelete' => $this->user_id === auth()->id() && !in_array($this->type, ['tour_request']),
                ]
            ];
        }
        
        // Skip events with no title (but we've already handled tour_schedule above)
        if (!$title) {
            return null;
        }
        
        return [
            'id' => $this->id,
            'start' => $this->date,
            'end' => $this->date,
            'title' => $title,
            'color' => $color,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'allDay' => true,
            
            // Event type and status information
            'type' => $this->type,
            'status' => $status,
            'note' => $this->note,
            
            // Extended properties for modal display
            'extendedProps' => [
                'scheduleId' => $this->id,
                'type' => $this->type,
                'status' => $status,
                'note' => $this->note,
                'date' => $this->date,
                
                // Booking information (if available)
                'booking' => $this->booking ? [
                    'id' => $this->booking->id,
                    'status' => $this->booking->status,
                    'price' => $this->booking->price,
                    'count_of_users' => $this->booking->count_of_users,
                    'phone' => $this->booking->phone,
                    'email' => $this->booking->email,
                    'total_extra_price' => $this->booking->total_extra_price ?? 0,
                    'book_date' => $this->booking->book_date,
                    'is_guest' => $this->booking->is_guest,
                ] : null,
                
                // User information (if available)
                'user' => $this->booking && $this->booking->user ? [
                    'id' => $this->booking->user->id,
                    'firstname' => $this->booking->user->firstname,
                    'lastname' => $this->booking->user->lastname,
                    'email' => $this->booking->user->email,
                    'phone' => $this->booking->user->phone ?? $this->booking->phone,
                    'is_guest' => $this->booking->is_guest,
                ] : null,
                
                // Guiding information (if available)
                'guiding' => $this->guiding ? [
                    'id' => $this->guiding->id,
                    'title' => $this->guiding->title,
                    'location' => $this->guiding->location,
                    'meeting_point' => $this->guiding->meeting_point,
                    'duration' => $this->guiding->duration,
                    'price' => $this->guiding->price,
                    'max_guests' => $this->guiding->max_guests,
                ] : null,
                
                // Vacation information (if available)  
                'vacation' => $this->vacation ? [
                    'id' => $this->vacation->id,
                    'title' => $this->vacation->title ?? 'Vacation',
                    'description' => $this->vacation->description,
                ] : null,
                
                // Permissions
                'canEdit' => $this->user_id === auth()->id(),
                'canDelete' => $this->user_id === auth()->id() && !in_array($this->type, ['tour_request']),
            ]
        ];
    }
}
