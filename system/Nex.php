<?php

/**
 * @author Mikael Laforge <mikael.laforge@gmail.com>
 * @version 1.0.0
 * @package System
 *
 * System bootstrapper
 * All system base methods are here.
 */

namespace Nex\System ;

class Nex
{
    const APP_SUFFIX = '_App' ;
	const APP_NS = 'App\\' ;
	const APP_DIR = '' ;

	const MOD_SUFFIX = '_Mod' ;

	const CTRLR_SUFFIX = '_Controller' ;
	const CTRLR_DIR = 'controller/' ;

	const LIB_SUFFIX = '_Lib' ;
	const LIB_DIR = 'lib/' ;

	const MDL_SUFFIX = '_Model' ;
	const MDL_DIR = 'model/' ;

	const CODE_DIR = 'code/';
	const I18N_DIR = 'i18n/';
	const DESIGN_DIR = 'design/';

    private static $config ;

    public static function init( )
    {
		static $run;

		// This function can only be run once
		if ($run === true)
			return;

        mb_internal_encoding('UTF-8');

		self::$config = new Config();
    }

	public static function setup( )
	{
		// Set default timezone early to avoid strict errors
        date_default_timezone_set(self::config('locale.tz'));

		// Sets autoloading function
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	public static function config($key) { return self::$config->get($key); }
	public static function configObj() { return self::$config; }

	// Namespace may or may not be given
	// but class suffix must
	public static function newObj($name)
	{
		$info = self::getClassInfo($name);

		if ( !file_exists($info['path'])) {
			trigger_error('Obj "'.$info['fullname'].'" could not be instanciated. File was not found', E_USER_ERROR);
		}

		require_once($info['path']);

		return new $info['fullname'] ;
	}

	/**
	 * Autoloading for class in app/
	 * Class must be in the form of "NS\Application\Classname"
	 */
	public static function autoload($class)
	{
		$path = self::getClassInfo($class, 'path');

		if ( ! $path || ! file_exists($path) ) {
			trigger_error('Class "'.$class.'" could not be autoloaded. File doesn\'t exist at : '.$path, E_USER_ERROR);
			return false ;
		}

        require($path);

        return true ;
	}

	public static function getAppInfo( $app, $index = null )
	{
		$info = array(
			'namespace' => '',
			'name' => '',
		);
		$boom = explode('_', $app, 2);

		if ( count($boom) === 1 ) { // Gotta guess namespace
			$info['name'] = $boom[0];
			$info['namespace'] = self::guessAppNamespace($app);
		} else {
			$info['namespace'] = $boom[0];
			$info['appname'] = $boom[1];
		}

		return $index ? $info[$index] : $info ;
	}

	public static function guessAppNamespace($app)
	{
		$app = '_'.$app ;
		$apps = self::getAppsByPriority();

		$found = $priority = null ;
		foreach ( $apps as $arr ) {
			if ( substr($arr['name'], -strlen($app)) === $app ) {
				$found = $arr['name'];
				break;
			}
		}

		if ( !$found ) {
			trigger_error('Could not guess application namespace for "'.$app.'"', E_USER_NOTICE);
			return null ;
		}

		return strstr($found, '_', true);
	}

	public static function getDirFromSuffix($suffix)
	{
		switch ( $suffix )
		{
			case self::CTRLR_SUFFIX: return self::CTRLR_DIR ;
			case self::APP_SUFFIX: return self::APP_DIR ;
			case self::MDL_SUFFIX: return self::MDL_DIR ;
			case self::LIB_SUFFIX:
			case '': return self::LIB_DIR ; break;
		}

		return '' ;
	}

	public static function getClassInfo($name, $index = null)
	{
		$info = array(
			'namespace' => '',
			'app' => '',
			'name' => '',
			'suffix' => '',
		);

		$boom = explode('\\', $name);

		if ( count($boom) == 5 ) { array_shift($boom); array_shift($boom); }
		elseif ( count($boom) == 4 ) array_shift($boom);

		// we got full name, with namespace and app
		if ( count($boom) == 3 ) {
			$info['namespace'] = array_shift($boom);
			$info['app'] = array_shift($boom);
			$boom = explode('_', $boom[0]);
			self::completeClassInfo($boom, $info);
			$dir = self::getDirFromSuffix($info['suffix']);
			$classpath = implode(DS, $boom).NEX_EXT;
			$pool = self::config('apps.'.$info['namespace'].'_'.$info['app'].'.pool') ;
		}
		// we got application and classname, no namespace given
		elseif ( count($boom) == 2 ) {
			$info['app'] = array_shift($boom);
			$boom = explode('_', $boom[0]);
			self::completeClassInfo($boom, $info);
			$classpath = implode(DS, $boom).NEX_EXT;

			$dir = self::getDirFromSuffix($info['suffix']);
			$apps = self::getAppsByPriority();
			foreach ( $apps as $arr ) {
				list($ns, $app) = explode('_', $arr['name']);

				if ( $app !== $info['app'] ) continue ;

				$pool = self::config('apps.'.$ns.'_'.$app.'.pool') ;
				if ( file_exists(DOC_ROOT.$pool.self::CODE_DIR.$ns.DS.$app.DS.$dir.$classpath) ) {
					$info['namespace'] = $ns ;
					break;
				}
			}
		}
		// We got nothing
		else {
			$boom = explode('_', $boom[0]);
			self::completeClassInfo($boom, $info);
			$classpath = implode(DS, $boom).NEX_EXT;

			$dir = self::getDirFromSuffix($info['suffix']);
			$apps = self::getAppsByPriority();
			foreach ( $apps as $arr ) {
				list($ns, $app) = explode('_', $arr['name']);
				$pool = self::config('apps.'.$ns.'_'.$app.'.pool') ;
				if ( file_exists(DOC_ROOT.$pool.self::CODE_DIR.$ns.DS.$app.DS.$dir.$classpath) ) {
					$info['namespace'] = $ns ;
					$info['app'] = $app ;
					break;
				}
			}
		}

		$info['fullname'] = '\\'.self::APP_NS.$info['namespace'].'\\'.$info['app'].'\\'.$info['name'].$info['suffix'] ;
		$info['path'] = DOC_ROOT.$pool.self::CODE_DIR.$info['namespace'].DS.$info['app'].DS.$dir.$classpath ;

		return $index ? $info[$index] : $info ;
	}

	protected static function completeClassInfo(& $boom, & $info)
	{
		static $possibleSuffix = array(self::CTRLR_SUFFIX, self::APP_SUFFIX, self::MDL_SUFFIX, self::LIB_SUFFIX);

		$suffix = end($boom);

		if ( in_array('_'.$suffix, $possibleSuffix) ) {
			array_pop($boom);
			$info['suffix'] = '_'.$suffix;
		}

		$info['name'] = implode('_', $boom);
	}

	public static function getAppsByPriority()
	{
		static $appsByPriority = array();

		if ( count($appsByPriority) ) return $appsByPriority ;

		$apps = self::config('apps');
		foreach ( $apps as $fullname => $arr ) {
			$arr['name'] = $fullname ;
			$appsByPriority[$arr['priority']] = $arr ;
		}

		krsort($appsByPriority, SORT_NUMERIC);

		return $appsByPriority ;
	}

	public static function getPools()
	{
		static $pools = array();

		if ( count($pools) ) return $pools;

		$apps = self::getAppsByPriority();
		foreach ( $apps as $arr ) {
			if ( !in_array($arr['pool'], $pools) ) {
				$pools[] = $arr['pool'];
			}
		}

		return $pools ;
	}

	public static function findDesignFile($file)
	{
		$pools = self::getPools();
		$zones = self::config('design.zones');
		foreach ( $pools as $pool ) {
			foreach ( $zones as $zone ) {
				if ( file_exists(DOC_ROOT.$pool.self::DESIGN_DIR.$zone.DS.$file) ) {
					return $pool.self::DESIGN_DIR.$zone.DS.$file ;
				}
			}
		}

		return false ;
	}
}

class NexException extends \Exception
{
	/**
	 * Make a new Core Exception with the given result.
	 * @param array $result
	 */
	public function __construct($msg, $code)
	{
		parent::__construct($msg, $code);
	}

	/**
	 * To make debugging easier.
	 * @returns string
	 */
	public function __toString()
	{
		$str = '' ;
		if ($this->code != 0) {
			$str .= $this->code . ': ';
		}
		return $str . $this->message;
	}
}
