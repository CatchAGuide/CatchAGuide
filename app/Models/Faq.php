<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'page',
        'language',
    ];
    public function limitanswer()
    {
        return substr($this->answer, 0, 50) . "...";
    }
}
