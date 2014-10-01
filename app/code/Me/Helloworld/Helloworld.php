<?php

namespace App\Me\Helloworld ;
use \App\Nex\Core ;

class Helloworld_App extends Core\Application
{
    protected static $routes = array(
        '*' => array(__CLASS__, 'indexAction'),
        'test1' => 'Test_Controller',
        '/^[a-z]{2}$/' => array(__CLASS__, 'regexAction'),
        'test2' => array('Test_Controller', 'test2Action'),
    );

    public function __construct()
    {
        parent::__construct('Helloworld');
    }

    public function indexAction()
    {
        $layout = new Core\Layout('myFirstLayout');
        $layout->set('title', 'Titre de l\'app Helloworld');
        $layout->render();
    }

    public function regexAction() {
        echo 'Regex !';
    }
}
