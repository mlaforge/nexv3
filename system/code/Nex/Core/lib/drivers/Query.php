<?php defined('BOOT_PATH') or die('No direct script access.');
/**
 * @author       Mikael Laforge <mikael.laforge@gmail.com>
 * @version      1.2.5
 * @package      Nex
 * @subpackage   Core
 *
 * @update 16/06/2011 [ML] - 1.1.0 - Added methods enclose() and getEnclose()
 * @update 26/10/2011 [ML] - 1.2.0 - Added methods saveOrderCache() and orderByCache()
 * @update 17/01/2012 [ML] - 1.2.1 - Upgraded enclose system to handle nested parenthesis
 * @update 06/05/2012 [ML] - 1.2.2 - Enclose method now make sure a corresponding parenthesis was used before using closing parenthesis
 *									Added distinct() method
 * @update 20/06/2012 [ML] - 1.2.3 - Added "unbuffed_query" property
 * 									 Added field() method
 * @update (29/01/13) [ML] - 1.2.4 - Added support for IGNORE statement with ignore() method
 * @update [ML] (28/03/13) - 1.2.5 - Added registerAlias() method
 *
 * Database Drivers
 */

abstract class Itremma_Nex_App_Database_Driver
{
    // Database connexion instance
    protected static 	$connection = array() ;

	// Name of last selected database
    protected static    $last_database = null ;

	// Key of current database config
	protected  			$database = '' ;

    // Database Config
    protected 			$config = array() ;

	// Sql result of select
    protected     		$result ;
	protected			$rows = array();

	// Last executed query
	protected 			$query = null ;

    // Parts of Sql query
    protected 			$distinct   = false;
    protected           $ignore     = false;
    protected 			$from       = array();
    protected 			$group_by   = array();
    protected 			$join       = array();
    protected 			$limit      = null;
    protected 			$offset     = null;
    protected 			$order_by   = array();
    protected 			$fields     = array();
	protected 			$set        = array();
	protected 			$where      = array();
	protected			$having		= array();
    protected           $unions     = array();

	protected 			$table_alias = array();

    /**
     * Constructor
     */
    public function __construct( $config, $config_key )
    {
        $this->config = $config ;
	}

    public function field( array $fields ) { $this->fields = array_merge($this->fields, $fields); }

	public function fieldRaw($field) { $this->fields[] = $field; }

	public function fromRaw($table, $func) { $func($this->from, $table); }

	public function registerAlias( $alias ) { $this->table_alias[] = $alias; }

	public function setDistinct($distinct = true) { $this->distinct = $distinct ; }

    public function setIgnore($ignore = true) { $this->ignore = $ignore ; }

	public function getQuery() { return $this->query ; }

    public function getResult() { return $this->result; }

	public function parenthesis($state)
	{
		if ($state == 'open') {
            $this->where[] = '(' ;
		}
        elseif ($state == 'close') {
            $this->where[] = ')' ;
        }
	}

	/**
     * Clear all query segments
     */
    public function clearComponents()
	{
        $this->distinct = false;
        $this->ignore = false;
        $this->fields = array();
        $this->set = array();
        $this->join = array();
        $this->where = array();
        $this->order_by = array();
        $this->group_by = array();
        $this->having = array();
        $this->limit = null;
        $this->offset = null;
        $this->unions = array();
    }

	public function clearFields(){ $this->set = array(); $this->fields = array(); }
    public function clearAlias() { $this->table_alias = array(); }
	public function clearFrom(){ $this->from = array(); }
	public function clearWhere(){ $this->where = array(); }
	public function clearJoin() { $this->join = array(); }
	public function clearOrderBy() { $this->order_by = array(); }
	public function clearGroupBy() { $this->group_by = array(); }
}
