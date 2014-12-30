<?php defined('BOOT_PATH') or die('No direct script access.');
/**
 * @author       Mikael Laforge <mikael.laforge@gmail.com>
 * @version      1.0.0
 * @package      Nex
 * @subpackage   core
 *
 * 08/11/2013
 * This class can be used with any Query interface (Like database, api, etc).
 * NOT FINISHED
 */

if ( !defined('QUERY_MATCH_NATURAL') )
{
    define('QUERY_MATCH_NATURAL', 4);
    define('QUERY_MATCH_BOOLEAN', 8);
	define('QUERY_HAVING', 16);
	define('QUERY_WHERE', 32);
	define('QUERY_SET', 64);
	define('QUERY_FIELD', 128);
}

class Nex_App_Query
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
        $this->config = Nex::config('query.'.$config_key);

		// Load Driver
		$classname = 'Query_'.ucfirst($this->config['type']).'_Driver' ;
		$this->driver = new $classname($this->config, $config_key);

		if( self::$config_key != $config_key )
		{
			// Initialize database
			$this->driver->connect();
		}
	}

    public function field( $fields = '*' )
    {
		if ( is_string($fields) ) {
			$fields = explode(',', $fields);
		}

		$this->driver->field($fields);

        return $this;
    }

    public function fieldRaw($field) { $this->driver->fieldRaw($field); return $this; }

    public function from( $tables, $func = 'array_push')
    {
		if( is_string($tables) ) {
            $tables = explode(',', $tables);
        }
        else{
            $tables = (array) $tables;
        }

		$this->driver->from($tables, $func);

		return $this ;
    }

    public function fromRaw($table, $func = 'array_push') { $this->driver->fromRaw($table, $func); return $this; }

	public function leftJoin($table, $onStatement, $vars = array()) { return $this->join($table, $onStatement, $vars, 'LEFT'); }
	public function rightJoin($table, $onStatement, $vars = array()) { return $this->join($table, $onStatement, $vars, 'RIGHT'); }
	public function join($table, $onStatement, $vars, $type = '')
	{
		$type = strtoupper($type);

		$this->driver->join($table, $onStatement, $vars, $type);

		return $this ;
	}

    public function joinRaw($join) { $this->driver->joinRaw($join); return $this; }

    public function set( $key, $statement, $vars = array())
    {
		$this->driver->set($key, $statement, $vars);

        return $this ;
    }

	public function setRaw( $set ) { $this->driver->setRaw($set); return $this; }

    public function where( $key , $statement , $vars = array() )
    {
		$this->driver->where($keys, $statement, $vars) ;

        return $this ;
    }

	public function whereRaw( $where ) { $this->driver->whereRaw($where); return $this; }

	public function orWhere( $key , $statement , $vars = array() )
    {
		$this->driver->where($keys, $statement, $vars, QUERY_OR) ;

        return $this ;
    }

	public function orWhereRaw( $where ) { $this->driver->whereRaw($where, QUERY_OR); return $this; }

    public function orderBy($statement, $vars = array())
    {
        $this->driver->orderBy($statement, $vars) ;

        return $this ;
    }

	public function orderByRaw( $orderBy ) { $this->driver->orderByRaw($orderBy); return $this; }

    public function groupBy($statement, $vars = array())
    {
		$this->driver->groupBy($statement, $vars);

		return $this;
    }

	public function groupByRaw( $orderBy ) { $this->driver->groupByRaw($orderBy); return $this; }

    public function limit($limit, $offset = null)
    {
		$this->driver->limit($limit, $offset);

        return $this;
    }

	public function union ( Query $query ) { $this->driver->union($query); return $this; }

    public function setIgnore($ignore = true) { $this->driver->setIgnore($ignore); return $this; }

    public function setDistinct($distinct = true) { $this->driver->setDistinct($distinct); return $this; }

    /**
     * Clear all query segments
     */
    public function clearComponents() { $this->driver->clearComponents(); }
	public function clearAlias() { $this->driver->clearAlias(); }
	public function clearFields() { $this->driver->clearFields(); }
	public function clearFrom() { $this->driver->clearFrom(); }
	public function clearWhere() { $this->driver->clearWhere(); }
	public function clearJoin() { $this->driver->clearJoin(); }
	public function clearOrderBy() { $$this->driver->clearOrderBy(); }
	public function clearGroupBy() { $this->driver->clearGroupBy(); }

################################################################################
#### Query executers
################################################################################

	public function update()
	{
		$this->query($this->driver->getUpdateQuery());

		return $this ;
	}
	public function getUpdateQuery() { return $this->driver->getUpdateQuery(); }

	public function delete()
	{
		$this->query($this->driver->getDeleteQuery());

		return $this ;
	}
	public function getDeleteQuery() { return $this->driver->getDeleteQuery(); }

	public function insert()
	{
		$this->query($this->driver->getInsertQuery());

		return $this ;
	}
	public function getInsertQuery() { return $this->driver->getInsertQuery(); }

	public function replace()
	{
		$this->query($this->driver->getReplaceQuery());

		return $this ;
	}
	public function getReplaceQuery() { return $this->driver->getReplaceQuery(); }


    public function select($unbuffed = false)
	{
		$this->query($this->driver->getSelectQuery($unbuffed));

		return $this ;
	}
	public function getSelectQuery() { return $this->driver->getSelectQuery(); }

    /**
     * Execute Sql query with givin Sql command and store results
     * @param string $sql Complete sql query
     */
    public function query ( $sql )
    {
        if ( !empty($config['log']) ) {
            Log::instance()->toFile($this->config['type'].'.log', $sql);
        }

        $this->driver->query($sql);

		return $this ;
    }

	/**
	 * Return number of affected rows
	 */
	public function nbrAffected() { return $this->driver->nbrAffected(); }

    /**
     * Return last insert id
     */
    public function lastInsertId() { return $this->driver->lastInsertId(); }


	public function getArray() { return $this->getArray(); }

	/**
	 * Return sql result
	 */
	public function getResult() { return $this->driver->getResult(); }

	/**
	 * Set parenthesis
	 * @param string $state 'open' | 'close'
	 */
    public function parenthesis($state) { $this->driver->enclose($state); return $this; }

	/**
	 * Free memory and resources used by database interface
	 */
	public function freeResources() { $this->driver->freeResources(); return $this; }

	/**
	 * Cache query result for X seconds
	 * @param $seconds
	 */
	public function remember($seconds = 60)  { $this->driver->remember($seconds); return $this; }
}
