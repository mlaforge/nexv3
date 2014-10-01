<?php

namespace App\Me\Helloworld ;
use \App\Nex\Core ;

class Html_Head_Controller extends Helloworld_App
{
    public function index()
    {
        $view = new Core\View('head');
        $view->set('title', 'Titre du controller Head');
        return $view->render(false);
    }
}
