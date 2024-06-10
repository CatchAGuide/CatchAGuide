<?php

namespace App\Traits;

use App\Models\Method;

trait MethodTraits
{
    public function getMethods()
    {
        return Method::all()->toArray();
    }


}