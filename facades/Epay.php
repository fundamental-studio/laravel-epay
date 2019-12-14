<?php

namespace Fundamental\Epay\Facades;

/**
 * Class Facade
 * @package Fundamental\Epay\Facades
 * @see Fundamental\Epay\Epay
 */

use Illuminate\Support\Facades\Facade;

class Epay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Fundamental\Epay\Epay';
    }
}