<?php

namespace App\Me\Helloworld ;

class Test_Controller extends Helloworld_App
{
    protected static $routes = array(
        '*' => array(__CLASS__, 'indexAction'),
        'test2' => array(__CLASS__, 'test2Action'),
    );

    public function indexAction()
    {
        echo 'Index'.__CLASS__;
    }

    public function test2Action()
    {
        echo 'test2';
    }
}
