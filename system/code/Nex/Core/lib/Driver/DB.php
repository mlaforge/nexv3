<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

abstract class Driver_DB
{
    // Database connexion instance
    protected static 	$connection = array() ;

	// Key of current database config
	protected  			$database = '' ;

    // Name of last selected database
    protected           $lastDB = null ;

    // Database Config
    protected 			$config = array() ;

	// Result resource
    protected     		$result ;

    // Rows
    protected           $rows = array();

	// Last executed query
	protected 			$lastQuery = null ;

    /**
     * Constructor
     */
    public function __construct( $config, $database )
    {
        $this->config = $config ;
        $this->database = $database ;
	}

    abstract public function runQuery($sql);

    abstract public function runUnbuffedQuery($sql);

    public function getResult() { return $this->result; }

    public function lastQuery() { return $this->lastQuery; }

    public function query($sql)
    {
        $this->lastQuery = $sql ;

        $this->runQuery($sql) ;
    }

    public function unbuffedQuery($sql)
    {
        $this->lastQuery = $sql ;

        $this->runUnbuffedQuery($sql) ;
    }
}
