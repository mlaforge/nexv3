<?php

namespace Nex\System ;

require SYS_PATH.'Config'.NEX_EXT ;
require SYS_PATH.'Router'.NEX_EXT ;
require SYS_PATH.'Nex'.NEX_EXT ;

Nex::init();

// Load base config
Nex::configObj()->loadDir(CONF_PATH);
Nex::configObj()->loadDir(CONF_PATH.'app'.DS);
Nex::configObj()->loadDir(CONF_PATH.'vendor'.DS);

// Setup environnement with base config
Nex::setup();

// Create new router, passing its config and apps
$router = new Router(Nex::config('router'), Nex::config('routes'));
$router->analyseURI();
$router->makeCall();
