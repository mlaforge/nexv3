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
    const		APP_SUFFIX = '_App' ;
	const		APP_NS = 'App\\' ;

	const		MOD_SUFFIX = '_Mod' ;
	const		CTRLR_SUFFIX = '_Controller' ;
    const 		HELPER_SUFFIX = '' ;
	const		LIB_SUFFIX = '_Lib' ;
	const		MDL_SUFFIX = '_Model' ;

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
	}

	public static function config($key) { return self::$config->get($key); }
	public static function configObj() { return self::$config; }

	// Namespace may or may not be given
	public static function newApplication($name)
	{
		$info = self::getAppInfo($name);

		$classname = self::APP_NS.$info['namespace'].'\\'.$info['name'].self::APP_SUFFIX ;

		return new $classname ;
	}

	public function getAppInfo( $app, $index = null )
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

	// @todo need to check extend
	public function guessAppNamespace($app)
	{
		$app = '_'.$app ;
		$apps = self::config('apps');

		$found = $priority = null ;
		foreach ( $apps as $fullname => $arr ) {
			if ( substr($fullname, -strlen($app)) === $app && $arr['priority'] > $priority ) {
				$priority = $arr['priority'];
				$found = $fullname ;
			}
		}

		if ( !$found ) {
			trigger_error('Could not guess application namespace for "'.$app.'"', E_NOTICE);
			return null ;
		}

		return strstr($found, '_', true);
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
