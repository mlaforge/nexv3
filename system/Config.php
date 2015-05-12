<?php

namespace Nex\System ;

class Config
{
    protected $config ;

    public function __construct() { }

    public function get( $key )
    {
        $value = self::extractVal($this->config, $key);

		return $value;
    }

    public function loadDir($path, $recursive = false)
    {
		if ( !is_dir($path) || !is_readable($path) ) {
			throw new NexException('Could not load config directory "'.$path.'"', E_NOTICE);
			return ;
		}

        // Local conf
        $dir = opendir($path);
        while ( ($file = readdir($dir)) !== FALSE)
        {
            if ( substr($file, 0, 1) == '.' ) continue ;

            if ( is_dir($path.$file) && $recursive ) {
                $this->loadDir($path.$file.DIRECTORY_SEPARATOR, false);
            }
            else{
                $this->loadFile($path.$file);
            }
        }
        closedir($dir);
    }

    public function loadFile($path)
    {
        $ext = strtolower(substr(strrchr(basename($path), '.'),1));

        if ( $ext == 'php' ) {
			$config = & $this->config ;
            include($path);
        }
    }

    public function loadConfig ( $config )
	{
		$this->config = array_overwrite_recursive($this->config, $config);
	}

    protected function array_overwrite_recursive($arr1, $arr2)
    {
        foreach($arr2 as $key => $value)
        {
            if( array_key_exists($key, $arr1) && is_array($value) ) {
              $arr1[$key] = $this->array_overwrite_recursive($arr1[$key], $arr2[$key]);
            }
            else {
              $arr1[$key] = $value;
            }
        }

        return $arr1;
    }

    protected function extractVal($array, $path)
	{
		$path_arr = explode('.', $path);

		// Go to next node
		if ( isset($array[$path_arr[0]]) ) {
			if ( isset($path_arr[1]) ) {
				return $this->extractVal($array[array_shift($path_arr)], implode('.', $path_arr));
			}
			// We are at the end of the path, return value
			else {
				return $array[$path_arr[0]] ;
			}
		}
		else {
            trigger_error('Could not find config "'.$path.'"');
		}
	}
}
