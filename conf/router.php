<?php
/**
 * Configuration for redirect/alternative routes.
 * Key are the regex and values are the replacement.
 * When a certain key match, system will boot like if value of the key was called
 * A route is something like 'admin/page/edition'
 * '/^index(\.php)?/' => 'MyApp' (regex)
 * 'startWith' => 'My' (Start with)
 * '*' => 'MyApp' (Catch all, has lowest priority)
 */

$config['router'] = array(
    'uriProtocol' => 'REQUEST_URI', // PATH_INFO | ORIG_PATH_INFO | REQUEST_URI | PHP_SELF
    'default' => array(
        'controller' => 'Error404_Controller',
        'method' => '::route',
    ),
);

$config['routes'] = array();
