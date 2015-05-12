<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

class Url
{
    protected $config ;

    public function __construct()
    {
        $this->config = Nex::config('system.url') ;
    }

    public function app($url = '')
    {
        if ( !$url ) {
            return $this->app(Router::getUriFromProtocol());
        }

        $url = ((strpos($url, '://') === false && substr($url, 0, 2) !== '//') ? $this->config['base'] : '').$url;

        return $url;
    }
}