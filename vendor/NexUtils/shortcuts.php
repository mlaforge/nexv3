<?php

use App\Nex\Core ;

function url($url = '')
{
    $obj = new Core\Url();

    return $obj->app($url);
}