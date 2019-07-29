<?php

namespace AshAllenDesign\Coinverter\Facades;

use Illuminate\Support\Facades\Facade;

class CoinverterFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'coinverter';
    }
}
