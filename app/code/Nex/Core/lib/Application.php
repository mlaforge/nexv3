<?php

namespace App\Nex\Core ;

class Application_Lib
{
    public static function route( & $router, array $routes )
    {
        $router->setRoutes($routes);
        $router->analyseRemainingURI();
        $router->makeCall();
    }
}
