<?php
/**
 * @author Mikael Laforge <mikael.laforge@gmail.com>
 * @package System
 *
 * This class controls the routes.
 * It tells which Controller to call depending on URI
 */

namespace Nex\System ;

class Router
{
    protected $controllerPrefix = '' ;
	protected $controller ;
	protected $methodPrefix = '' ;
	protected $method ;

    protected $uri ;

    protected $config ;
    protected $routes ;

    public function __construct($config, array $routes)
    {
		$this->config = $config ;
        $this->routes = $routes ;
    }

    public function setControllerPrefix( $prefix ) { $this->controllerPrefix = $prefix; }
	public function setMethodPrefix( $prefix ) { $this->methodPrefix = $prefix; }

	public function getControllerToCall() { return $this->controllerPrefix.$this->controller ; }
	public function getMethodToCall() { return $this->methodPrefix.$this->method ; }

    public function analyseURI( $uri = null )
    {
		$this->controller = $this->method = '' ;
        $this->uri = $uri ?: $this->getUriFromProtocol();

        foreach ( $this->routes as $route => $info ) {
			if ( $this->matchURI($route, $this->uri) ) {
				$this->fillInfos($info);
				break ;
			}
		}

		if ( !$this->controller ) $this->controller = $this->config['default']['controller'];
		if ( !$this->method ) $this->method = $this->config['default']['method'];
	}

    public function getUriFromProtocol()
	{
        $uri = $_SERVER[$this->config['uriProtocol']] ; // PATH_INFO | REQUEST_URI | PHP_SELF

        $uri = self::stripQuery($uri);

		// Prevent multiple slashes to avoid cross site requests via the FAPI.
		$uri = '/'. ltrim($uri, '/');

		if( defined('NEX_BASE_URL') && stripos($uri, NEX_BASE_URL) === 0 ){
			$uri = substr($uri, strlen(NEX_BASE_URL));
		}
		elseif ( substr($uri, 0, 1) == '/' ) {
			$uri = substr($uri, 1);
		}

		if ( !$uri ) $uri = $this->config['default']['uri'];

		return $uri ;
	}

	protected function fillInfos($info)
	{
		if ( is_string($info) ) {
			if ( substr($info, 0, 1) < 'a' ) { // Controllers are always capitalized, not methods
				$this->controller = $info ;
			} else {
				$this->method = $info ;
			}
		}
		elseif ( is_array($info) ) {
			$this->controller = array_shift($info);
			$this->method = array_shift($info);
		}
	}

	protected function matchURI($route, $uri)
	{
		// Regex
		if ( substr($route, 0, 1) == '/' && preg_match($route, $uri) ) {
			return true;
		}
		// StartWith
		elseif ( substr($uri, 0, strlen($route)) == $route ) {
			return true ;
		}

		return false ;
	}

    public static function stripQuery($uri)
	{
        if( ($pos = strpos($uri, '?')) !== false ){
			$uri = substr($uri, 0, $pos);
        }

		return $uri ;
	}
}
