<?php

namespace App\Me\Helloworld ;

class Test_Controller extends Helloworld_App
{
    public function indexAction()
    {
        echo 'Index'.__CLASS__;
    }

    public function test2Action()
    {
        echo 'test2';
    }
}
