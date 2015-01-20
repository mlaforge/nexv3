<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

class DB
{
	// Instances
	protected static	$instances = array();

	// Current config key
	protected static	$config_key ;

	// Database config
	protected			$config ;

    /**
     * A way to construct this class in a Static manner
     * @param string $config key of query in config
     */
    public static function instance( $config_key = '_default' )
    {
		if ( isset(self::$instances[$config_key]) ) {
			return self::$instances[$config_key];
		}

        return new self($config_key);
    }

    /**
     * Constructor
     * @param string $database key of database in config
     */
    public function __construct( $config_key = '_default' )
    {
        $this->config = Nex::config('db.'.$config_key);

		// Load Driver
        $info = Nex::getClassInfo('Driver_DB_'.ucfirst($this->config['type']));
        require_once($info['path']);
		$this->driver = new $info['fullname']($this->config, $config_key);

		if( self::$config_key != $config_key )
		{
			// Initialize database
			$this->driver->connect();
		}
	}

    /**
     * Execute Sql query with givin Sql command and store results
     * @param string $sql full sql statement
     */
    public function query ( $sql )
    {
        // @todo
        if ( !empty($config['log']) ) {
            Log::instance()->toFile($this->config['type'].'.log', $sql);
        }

        $this->driver->query($sql);

		return $this ;
    }

    /**
     * Execute Sql query with givin Sql command and store results
     * @param string $sql Complete sql query
     */
    public function unbuffedQuery ( $sql )
    {
        // @todo
        if ( !empty($config['log']) ) {
            Log::instance()->toFile($this->config['type'].'.log', $sql);
        }

        $this->driver->unbuffedQuery($sql);

        return $this ;
    }

	public function count() { return $this->driver->count(); }

    public function countAffected() { return $this->driver->countAffected(); }

    public function lastInsertId() { return $this->driver->lastInsertId(); }

    public function lastQuery() { return $this->driver->lastQuery(); }

	public function fetchRows() { return $this->driver->fetchRows(); }

	public function getResult() { return $this->driver->getResult(); }

	public function freeResources() { $this->driver->freeResources(); return $this; }

	/**
	 * Cache query result for X seconds
	 * @param $seconds
     * @todo
	 */
	//public function remember($seconds = 60)  { $this->driver->remember($seconds); return $this; }
}
