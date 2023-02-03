<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string \App\Modules\Eghl\Eghl validatePaymentResponse(Request $request)
 * @method static bool \App\Modules\Eghl\Eghl processPaymentRequest(array $data)
 * 
 * @package App\Facades
 */
class Eghl extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eghl';
    }
}
