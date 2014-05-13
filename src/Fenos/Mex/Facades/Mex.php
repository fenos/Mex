<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 07/05/14
 * Time: 23:39
 */

namespace Fenos\Mex\Facades;


use Illuminate\Support\Facades\Facade;

class Mex extends Facade{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'mex'; }

} 