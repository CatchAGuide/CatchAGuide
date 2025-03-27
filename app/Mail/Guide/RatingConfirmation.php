<?php

namespace App\Mail\Guide;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RatingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $rating;

    public function __construct(Review $rating)
    {
        $this->rating = $rating;
    }

    public function build()
    {
        return $this->view('mails.guide.review_confirmation_email')
                    ->with([
                        'name' => $this->rating->guide->full_name,
                        'guiding_name' => $this->rating->guiding->title,
                        'score' => round($this->rating->grandtotal_score, 1),
                        'comment' => $this->rating->comment,
                    ])
                    ->subject(__('emails.rating_confirmation_subject'));
    }
} 