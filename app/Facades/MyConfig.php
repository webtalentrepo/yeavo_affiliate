<?php

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class MyConfig extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'MyConfig';
    }
}
