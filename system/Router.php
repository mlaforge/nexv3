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
	protected $method ;

    protected $uri ;
	protected $remainingUri ;

    protected $config ;
    protected $routes ;

    public function __construct($config, array $routes)
    {
		$this->config = $config ;
        $this->routes = $routes ;
    }

	public function setRoutes($routes) { $this->routes = $routes; $this->sortRoutes($this->routes); }

    public function setControllerPrefix( $prefix ) { $this->controllerPrefix = $prefix; }

	public function makeCall()
	{
		if ( is_object($this->controller) ) {
			call_user_func_array(array($this->controller, $this->method), $this);
		}
		elseif ( substr($this->method, 0, 2) == '::' ) {
			$fullname = Nex::getClassInfo($this->controllerPrefix.$this->controller, 'fullname');
			$method = substr($this->method, 2);
			$fullname::$method($this);
		}
		else {
			$obj = Nex::newObj($this->controllerPrefix.$this->controller);
			$obj->{$this->method}($this);
		}
	}

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

	public function analyseRemainingURI ()
	{
		$this->analyseURI($this->remainingUri);
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
		// Everything match
		if ( $route == '*' ) {
			$this->remainingUri = $uri;
			return true ;
		}
		elseif ( $route )
		{
			// Regex
			if ( substr($route, 0, 1) == '~' ) {
				$route = substr($route, 1);
				if ( preg_match($route, $uri) ) {
					$this->remainingUri = preg_replace($route, '', $uri);
					$this->remainingUri = substr($this->remainingUri, 0, 1) == '/' ? substr($this->remainingUri, 1) : $this->remainingUri ;
					return true;
				}
			}
			// Equals
			elseif ( substr($route, 0, 1) == '=' ) {
				$route = substr($route, 1);
				if ( $route == $uri ) {
					$this->remainingUri = '' ;
					return true;
				}
			}
			// Starts with
			elseif ( substr($uri, 0, strlen($route)) == $route ) {
				$this->remainingUri = substr($uri, strlen($route));
				$this->remainingUri = substr($this->remainingUri, 0, 1) == '/' ? substr($this->remainingUri, 1) : $this->remainingUri ;
				return true ;
			}
		}

		return false ;
	}

	protected function sortRoutes(& $routes)
	{
		krsort($routes);
	}

    public static function stripQuery($uri)
	{
        if( ($pos = strpos($uri, '?')) !== false ){
			$uri = substr($uri, 0, $pos);
        }

		return $uri ;
	}
}
