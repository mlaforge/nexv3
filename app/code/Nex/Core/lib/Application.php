<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

class Application_Lib
{
    protected static $routes = array();

    public static function route( & $router )
    {
        $router->setRoutes(static::$routes);
        $router->analyseRemainingURI();
        $router->makeCall();
    }

    public function __construct($app)
    {
        $this->loadAppConfig($app);
    }

    protected function loadAppConfig($app)
    {
        if ( is_dir(CONF_PATH.'app'.DS.$app.DS) )
            Nex::configObj()->loadDir(CONF_PATH.'app'.DS.$app.DS);
    }
}
