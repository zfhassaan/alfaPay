<?php

namespace zfhassaan\Alfapay;

class AlfapayFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'alfapay';
    }
}
