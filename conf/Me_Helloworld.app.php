<?php

$config['apps']['Me_Helloworld'] = array(
    'codePool' => 'local',
    'priority' => 999, // Higher, Highest priority
);

// segment "index" and others will call app Helloworld
//$config['router']['default']['app'] = 'Helloworld';
$config['routes']['index'] = 'Helloworld';
