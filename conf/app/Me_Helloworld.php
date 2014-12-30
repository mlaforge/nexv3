<?php

$config['apps']['Me_Helloworld'] = array(
    'pool' => 'app/',
    'priority' => 999, // Higher, Highest priority
);

// segment "index" and others will call app Helloworld
//$config['routes']['index'] = 'Helloworld_App';
$config['routes']['*'] = 'Helloworld_App'; // Catch all
//$config['routes']['index'] = 'Helloworld_App';
//$config['routes']['index'] = 'Test_Controller';
