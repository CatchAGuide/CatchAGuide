<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Cacheable;

class Faq extends Model
{
    use Cacheable;

    protected $fillable = [
        'question',
        'answer',
        'page',
        'language',
        'source_id'
    ];
    public function limitanswer()
    {
        return substr($this->answer, 0, 50) . "...";
    }
}
