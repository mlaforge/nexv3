<?php

namespace App\Nex\Core ;

class Error404_Controller extends Application
{
    protected static $routes = array(
        '*' => array(__CLASS__, 'indexAction'),
    );

    public function __construct()
    {
        parent::__construct('Nex');
    }

    public function indexAction()
    {
        echo 'Erreur 404';
    }
}
