<?php

namespace App\Me\Helloworld ;
use \App\Nex\Core as Core ;

class Helloworld_App extends Core\Application_Lib
{
    public function __construct() {
        'Construit !';
    }

    public static function route(& $router)
    {
        parent::route($router, array(
            'index' => array(__CLASS__, 'indexAction'),
            'test1' => 'Test_Controller',
            'test2' => array('Test_Controller', 'test2Action'),
        ));
    }

    public function indexAction()
    {
        echo 'Index !';
    }
}
