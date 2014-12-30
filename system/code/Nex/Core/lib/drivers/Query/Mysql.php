<?php defined('BOOT_PATH') or die('No direct script access.');
/**
 * @author       Mikael Laforge <mikael.laforge@twikiconcept.com>
 * @version      1.0.0
 * @package      Nex
 * @subpackage   core
 * Query MySql Drivers
 */

class Itremma_Nex_App_Query_Mysql_Driver extends Query_Driver
{
    public function from( array $tables, $func )
    {
		foreach ($table_keys as $table_key)
		{
			$end = '';
			$table_key = explode('->', str_replace(' ', '', $table_key));
			if( isset($table_key[1]) ){
                $this->registerAlias($table_key[1]);
				$end = " AS $alias";
			}

            $func($this->from, $this->table($table_key[0]).$end);
		}

		return $this;
    }

	public function join($table, $fields, $type = '')
	{
		if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))){
			$type = '';
		}

		$table_key = explode('->',  str_replace(' ', '', $table_key));
        $end = '';
		if( isset($table_key[1]) ){
            $this->registerAlias($table_key[1]);
            $end = " AS $alias";
		}

        // Build condition
		$fields = array();
		foreach ( $fields as $arr )
        {
            if ( is_string($arr) ) {
                $fields[] = $arr ;
            }
            elseif ( count($arr) === 2 ) {
                $fields[] = (count($fields) ? 'AND ' : '').$arr[0].' = '.$arr[1] ;
            }
            elseif ( count($arr) === 3 ) {
                $fields[] = (count($fields) ? 'AND ' : '').$arr[0].' '.$arr[1].' '.$arr[2] ;
            }
            else {
                $fields[] = $arr[0].' '.$arr[1].' '.$arr[2].' '.$arr[3] ;
            }
		}

		$this->join[] = (empty($type) ? 'JOIN ' : $type.' JOIN ').$this->table($table).$end." ON (".implode(' ', $fields).')';

		return $this;
	}

    public function set( array $set )
    {
        if ( isset($set[0]) && is_array($set[0]) ) {
            foreach ( $set as $key => $arr ) {
                foreach ( $arr as $k => $v ) {
                    $arr[$k] = self::escape($v, QUERY_SET);
                }
                $this->set[] = $arr ;
            }
        }
        else {
            foreach ( $set as $k => $v ) {
                $this->set[$k] = self::escape($v, QUERY_SET);
            }
        }

        return $this;
    }

    public function setRaw( array $set )
    {
        if ( isset($set[0]) && is_array($set[0]) ) {
            foreach ( $set as $key => $arr ) {
                foreach ( $arr as $k => $v ) {
                    $arr[$k] = $v;
                }
                $this->set[] = $arr ;
            }
        }
        else {
            foreach ( $set as $k => $v ) {
                $this->set[$k] = $v;
            }
        }

        return $this;
    }

	public function where( array $pairs , $operator = '=', $andor = 'AND', $type = DB_WHERE )
    {
		if ( $type == DB_HAVING ) $w = & $this->having ;
		else $w = & $this->where ;

		$count = count($w);

		if ( !$count ) {
			$strwhere = $andor.' ';
		}
		elseif ( $count ) {
			$strwhere = $andor.' ';
			while ( $count && $w[$count-1] == '(' ) {
				$strwhere .= array_pop($w);
				$count--;
			}
		}

        foreach($pairs as $field => $value)
		{
			$strwhere .= $field.' '.$operator.' '.self::escape($value, QUERY_WHERE);

			$strwhere .= 'AND ';
        }

		// Remove last AND and add last Parenthesis
        $strwhere = substr($strwhere, 0, -4 );

        $w[] = $strwhere ;

        return $this;
    }

	public function like( array $fields, $andor = 'AND', $type = DB_WHERE )
	{
		if ( $type == DB_HAVING ) $w = & $this->having ;
		else $w = & $this->where ;

		if ( !$count ) {
			$strwhere = $andor.' ';
		}
		elseif ( $count ) {
			$strwhere = $andor.' ';
			while ( $count && $w[$count-1] == '(' ) {
				$strwhere .= array_pop($w);
				$count--;
			}
		}

        foreach($fields as $field => $value)
		{
			$strwhere .= $field.' LIKE '.self::escape($value, QUERY_WHERE);

			$strwhere .= 'AND ';
        }
		$strwhere = substr($strwhere,0,-4).' ';

        $w[] = $strwhere ;

		return $this;
	}

	// @todo HERE

	/**
	 * Build WHERE statement
	 * MATCH is used.
	 * @param array $fields field => array
	 * @return $this
     */
    public function match( array $fields, $operator = 'AND', $mode = DB_MATCH_BOOLEAN, $type = DB_WHERE )
    {
		if ( $fields === array() ) return $this ;

		if ( $type == DB_HAVING ) $w = & $this->having ;
		else $w = & $this->where ;

		$mode_str = '' ;
		switch ( $mode )
		{
			case DB_MATCH_BOOLEAN : $mode_str = ' IN BOOLEAN MODE';
			case DB_MATCH_NATURAL : $mode_str = ' IN NATURAL LANGUAGE MODE';
		}

		$enclose = $this->getEnclose('open');
        $strwhere = (!empty($w)) ? $operator.' '.$enclose : $enclose ;
        foreach($fields as $field => $value){
            $strwhere .= "MATCH(".self::escapeField($field, $this->config['prefix'], $this->table_alias).') AGAINST ("'.self::escape($value, null, NEX_NO_QUOTES).'"'.$mode_str.') AND ';
        }

		$strwhere = substr($strwhere,0,-4).' ';

        $w[] = $strwhere ;

        return $this;
    }

	/**
	 * Build WHERE statement
	 * IN is used.
	 * @param array $fields field => array
	 * @return $this
     */
    public function in( array $fields, $operator = 'AND', $in = true, $type = DB_WHERE )
    {
		if ( $fields === array() ) return $this ;

		if ( $type == DB_HAVING ) $w = & $this->having ;
		else $w = & $this->where ;

        $enclose = $this->getEnclose('open');
        $strwhere = (!empty($w)) ? $operator.' '.$enclose : $enclose ;
        foreach($fields as $field => $value)
		{
			if ( is_array($value) ) {
				foreach ( $value as $k => $v ) {
					$value[$k] = self::escape($v, null);
				}
				$value = implode(',', $value);
			}

            $strwhere .= self::escapeField($field, $this->config['prefix'], $this->table_alias).(!$in ? ' NOT ' : '')."IN (".$value.") AND ";
        }

		$strwhere = substr($strwhere,0,-4).' ';

        $w[] = $strwhere ;

        return $this;
    }

    /**
     * Set the sql ORDER statement array
     * @param array $pairs field => order
     * @param bool $cache keep order in cache
     * @return $this
     */
    public function orderBy( array $pairs, $cache = false )
    {
        // Add to instance's ORDERBY array
        foreach($pairs as $field => $type)
		{
            if ( in_array($field, $this->order_by_used) ) continue ;

            $this->order_by_used[] = $field ;

			$type = strtoupper($type);

			if( $type != 'ASC' && $type != 'DESC' ) {
				$type = 'ASC';
			}

            // Keep that in cache
            if ( $cache ) {
                $this->order_by_cache[$field] = $type ;
            }

			if (($field = trim($field)) === '')     continue;
            $this->order_by[] = $field.' '.$type ;
        }

        // Return itself
        return $this;
    }

    /**
     * Build sql GROUP BY statement array
     * @param array $fields string or array of field's names.
     * @return $this
     */
    public function groupBy( array $fields)
    {
        // Add fields to instance's GROUP BY array
		foreach ($fields as $field){
            // Skip empty vars
			if (($field = trim($field)) === '')     continue;

			$this->group_by[] = self::escapeField($field, $this->config['prefix'], $this->table_alias);
		}

		return $this;
    }

    /**
     * Set the sql LIMIT statement variables
     * @param int $limit - Number of maximum row returned by query
     * @param int $offset - Offset of returned rows
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        if(func_num_args() == 1){
            $limit = (array) $limit;
            $this->limit = array_shift($limit);
            $this->offset = array_shift($limit);
        }
        else{
            $params = func_get_args();
            $this->limit = array_shift($params);
            $this->offset = array_shift($params);
        }

        return $this;
    }

################################################################################
#### Query executers
################################################################################

	/**
	 * Compiles an update string and runs the query.
	 * @param string $table_key String of table's key name.
	 * @return bool
	 */
	public function update($table_key = null)
	{
		if (empty($this->set)) {
			throw new Nex_Exception('SET statement is empty for UPDATE query.', NEX_E_DATABASE_QUERY_COMPILE);
			return false;
		}

		if ($table_key === null)
		{
			if ( empty($this->from[0]) ) {
				throw new Nex_Exception('No table was specified for UPDATE query.', NEX_E_DATABASE_QUERY_COMPILE);
				return false;
			}

			$table = $this->from[0];
		}
		else{
			$table = $this->table($table_key);
		}

		$sql = 'UPDATE '.($this->ignore ? 'IGNORE ' : '').$table.' SET '.implode(', ',$this->set).' ';
		$sql .= (!empty($this->where)) ? 'WHERE '.implode('',$this->where).' ' : '' ;

		return (bool) $this->query($sql);
	}

    /**
	 * Compiles a Delete string and runs the query.
	 * @param string $table_key String of table's key name.
	 * @return bool
	 */
	public function delete($table_key = null)
	{
		if ($table_key === null)
		{
			if ( empty($this->from[0]) ) {
				throw new Nex_Exception('No table was specified for DELETE query.', NEX_E_DATABASE_QUERY_COMPILE);
				return false;
			}

			$table = $this->from[0];
		}
		else{
			$table = $this->table($table_key);
		}

		$sql = 'DELETE '.($this->ignore ? 'IGNORE ' : '').'FROM '.$table.' ';
		$sql .= (!empty($this->where)) ? 'WHERE '.implode('', $this->where).' ' : '' ;
		$sql .= (!empty($this->limit)) ? 'LIMIT '.$this->limit.' ' : '' ;

		return (bool) $this->query($sql);
	}

    /**
     * Compile an Insert sql string and runs Query
	 * @param string $table_key String of table's key name.
	 * @return bool
	 */
	public function insert($table_key = null)
	{
		if ($table_key === null)
		{
			if ( empty($this->from[0]) ) {
				throw new Nex_Exception('No table was specified for INSERT query.', NEX_E_DATABASE_QUERY_COMPILE);
				return false;
			}

			$table = $this->from[0];
		}
		else{
			$table = $this->table($table_key);
		}

		$sql = 'INSERT '.($this->ignore ? 'IGNORE ' : '').'INTO '.$table.' ';
		$sql .= (!empty($this->set)) ? "SET ".implode(', ',$this->set).' ' : "VALUES () " ;

		return $this->query($sql);
	}

	/**
	 * Compile an Insert sql string for multiple rows
	 * @param string $table_key
	 * @param array $sets multidimensional array
	 */
	public function insert_all ( $table_key, $sets = array() )
	{
		if ( count($sets) == 0  ) return false ;

		$table = $this->table($table_key);

		$keys = array_keys($sets[0]);

		$values = array();
		foreach ( $sets as $set ) {
			$row = array() ;
			foreach ( $set as $v ) {
				$row[] = self::escape($v);
			}
			$values[] = '('.implode(',', $row).')';
		}

		$sql = 'INSERT '.($this->ignore ? 'IGNORE ' : '').'INTO '.$table.' (`'.implode('`, `', $keys).'`) VALUES '.implode(',', $values) ;

		return $this->query($sql);
	}

	 /**
     * Compile a Replace sql string and runs Query
	 *
	 * @param String                $table_key - String of table's key name.
	 * @param Array                 $set - associative array of update values
	 * @return  Database_Result     Query result
	 */
	public function replace($table_key = '', $set = null)
	{
		if ($table_key === null)
		{
			if ( empty($this->from[0]) ) {
				throw new Nex_Exception('No table was specified for REPLACE query.', NEX_E_DATABASE_QUERY_COMPILE);
				return false;
			}

			$table = $this->from[0];
		}
		else{
			$table = $this->table($table_key);
		}

		$sql = "REPLACE INTO $table ";
		$sql .= (!empty($this->set)) ? "SET ".implode(', ',$this->set).' ' : "VALUES () " ;

		return $this->query($sql);
	}

	/**
	 * Compile a Replace sql string for multiple rows
	 * @param string $table_key
	 * @param array $sets multidimensional array
	 */
	public function replace_all ( $table_key, $sets = array() )
	{
		if ( count($sets) == 0  ) return false ;

		$table = self::table($table_key);

		$keys = array_keys($sets[0]);

		$values = array();
		foreach ( $sets as $set ) {
			$row = array() ;
			foreach ( $set as $v ) {
				$row[] = self::escape($v);
			}
			$values[] = '('.implode(',', $row).')';
		}

		$sql = 'REPLACE INTO '.$table.' (`'.implode('`, `', $keys).'`) VALUES '.implode(',', $values) ;

		return $this->query($sql);
	}

    /**
     * Build a final Sql SELECT string with instance's variables. Execute query with
     * @param bool $force_resource force return value to be the raw database result resource
     */
    public function select( )
    {
        $sql = $this->select_build_query();

		// Set flag $this->has_where
		if ( $this->where !== array() ) {
			$this->has_where = true ;
		}

        return $this->query($sql);
    }

	public function unbuffed_select( )
    {
		$this->unbuffed_query = true ;

        return $this->select();
    }

    /**
     * Build select query and return it
     */
    public function select_build_query()
    {
        $sql = 'SELECT '.(($this->select === array()) ? '*' : implode(', ', $this->select)) ;
		$sql .= ' FROM '.implode(', ',$this->from).' ';
        $sql .= implode(' ',$this->join).' ' ;
        $sql .= (!empty($this->where)) ? 'WHERE '.implode('',$this->where).' ' : '' ;
        $sql .= (!empty($this->group_by)) ? 'GROUP BY '.implode(', ',$this->group_by).' ' : '' ;
		$sql .= (!empty($this->having)) ? 'HAVING '.implode('',$this->having).' ' : '' ;
		$sql .= (!empty($this->order_by)) ? 'ORDER BY '.implode(', ',$this->order_by).' ' : '' ;

		if(!empty($this->limit)){
            $sql .= 'LIMIT '.((!empty($this->offset)) ? intval($this->offset).', ' : ' ').intval($this->limit);
        }

        return $sql ;
    }

    /**
     * Execute Sql query with givin Sql command and store results
     * @param string $sql Complete sql query
     */
    public function query($sql)
    {
		$return = true ;

		$func = ($this->unbuffed_query ? 'mysql_unbuffered_query' : 'mysql_query');

        // Execute Query and Trow Fatal if there is an error
        if( ! ($result = $func($sql, self::$connection[$this->database])) ){
            throw new Nex_Exception('Database query error : "'.$sql.'" : '.mysql_error(), NEX_E_DATABASE_QUERY_EXECUTE.'.'.mysql_errno());
			$return = false ;
		}

		// Save query
		$this->last_query = $sql ;

		// Add sql result to instance's variable
		$this->rows = array();
        $this->result = $result;

		return $return ;
    }

################################################################################
## Global functions
################################################################################

    /**
     * Return number of object in list
     */
    public function nbr() { return is_resource($this->result) && mysql_num_rows($this->result) ; }

	/**
	 * Return number of affected rows
	 */
	public function affected() { return mysql_affected_rows(self::$connection[$this->database]); }

    /**
     * Return last insert id
     */
    public function lastInsertId() { return mysql_insert_id(self::$connection[$this->database]); }

	/**
	 * Free mysql memory
	 */
	public function free_memory()
	{
		if ( is_resource($this->result) ) {
			mysql_free_result($this->result);
		}
	}

	/**
     * Create an array of rows from mysql result that can be used with standard php functions
     * @return array
	 */
	public function getRows()
	{
		$tmp = array();

		if ( $this->rows !== array() ) return $this->rows ;

		if ( is_resource($this->result) )
		{
			if ( !$this->unbuffed_query )
				mysql_data_seek($this->result, 0);

			if( $this->as_array == false ) {
				while($r = mysql_fetch_object($this->result)) {
					$this->rows[] = $r;
				}
			}
			else{
				while($r = mysql_fetch_assoc($this->result)) {
					$this->rows[] = $r;
				}
			}

			if ( !$this->unbuffed_query )
				mysql_data_seek($this->result, 0);
		}

		return $this->rows ;
	}

################################################################################
### Global static functions
################################################################################

	/**
     * This function connects to the database using the Parameters or the default config
     * @param string $database key of database in config
     */
    public function connect( )
    {
		// Make sure that connection wasn't established yet
		if( isset(self::$connection[$this->database]) ){
			return (self::$last_db_name !== $this->config['name']) ? $this->selectDB() : true ;
		}

        $user 		= $this->config['user'];
        $pass 		= $this->config['pass'];
        $host 		= $this->config['host'];

        if ( ! (self::$connection[$this->database] = mysql_connect($host,$user,$pass)) ) {
            throw new Nex_Exception(mysql_error(), NEX_E_DATABASE_CONNECT);
			return false ;
        }

		if ( $this->config['charset'] ){
			mysql_set_charset($this->config['charset'], self::$connection[$this->database]);
		}

        return $this->selectDB() ;
    }

    /**
     * This function select the database. If connection wasn't established yet,
     * it will try to connect with default param
     */
    public function selectDB ( )
    {
        $name = $this->config['name'];

        if( ! mysql_select_db($name, self::$connection[$this->database]) ){
            throw new Nex_Exception(mysql_error(), NEX_E_DATABASE_SELECT);
			return false ;
        }

        self::$last_db_name = $name ;
    }

    /**
     * This function will return the requested database table name with the prefix or not
     * It will check if dot is found meaning we have tablekey.field or db.tablekey.field
     *
     * @param string|array          $arr_key - key or array of key of a table in database config
     * @param bool                  $prefixed - return the name prefixed or not
     * @param string				$quotes -  type of quotes to use
     * @return string|array         $arr_table - table name with prefix or not
     *
     * @uses self::escape_field()
     */
    public function table( $arr_key , $prefixed = TRUE, $quotes = NEX_BACKTICK )
    {
        // INIT
        $arr_table = array();
		$prefix = $prefixed ? $this->config['prefix'] : '' ;
		$arr_key = (array) $arr_key;

        foreach ($arr_key as $key) {
			array_push($arr_table, self::escapeField($key, $prefix, $this->table_alias, TRUE, $quotes));
        }

        // return a string if $arr_table has only 1 row
        return (count($arr_table) == 1 ) ? $arr_table[0] : $arr_table ;
    }

	/**
	 * Split a string on query delimiter
	 * This is used to separate many query into array
	 * @param string $str
	 * @return array
	 */
	public static function splitOnDelimiter($str)
	{
		$boom = preg_split("/;[\r?\n]+/i", $str);
		foreach($boom as $key => $cell){
			$cell = trim(ltrim($cell));
			if($cell == ''){
				unset($boom[$key]);
			}
		}
		return $boom ;
	}

	/**
	 * Determines if the string has an arithmetic operator in it.
	 * @param string $str  		str to check
	 * @return bool
	 */
	public static function hasOperator($str)
	{
		return (bool) preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?\b/i', trim($str));
	}

	/**
	 * Split a string on arithmetic operator
	 * @param string $str  		str to split
	 * @return bool
	 */
	public static function splitOnOperator($str)
	{
		return preg_split('/[<>!=]|\sIS(?:\s+NOT\s+)?\b/i', trim($str));
	}

	/**
	 * Return arithmetic operator in a string
	 * @param string $str
	 * @param bool $as_array return first match or all in an array
	 */
	public static function findOperator($str, $as_array = FALSE)
	{
		if( ($count = preg_match_all('/[<>!=]|\sIS(?:\s+NOT\s+)?\b/i', trim($str), $matches)) > 0 ){
			return ($as_array == FALSE ) ? $matches[0] : $matches ;
		}
		return false ;
	}

	/**
	 * Escapes any input value.
	 * @param mixed $value value to escape
	 * @param bool $type IS NULL is used instead of NULL ( Used in where clause )
	 * @return string
	 */
	public static function escape($value, $type = QUERY_SET)
	{
		if ( is_string($value) && substr($value, 0, 1) == NEX_BACKTICK ) return mysql_real_escape_string($value) ;

		switch (gettype($value))
		{
			case 'string':
				$value = (( in_array(strtoupper($value), array('NOW()', 'NULL', 'IS NULL', 'RAND()')) ) ? $value : $quotes.mysql_real_escape_string($value).$quotes );
			break;
			case 'boolean':
				$value = (int) $value;
			break;
			case 'array':
				$value = $quotes.mysql_real_escape_string(arr::serialize($value)).$quotes;
			break;
			case 'double':
				// Convert to non-locale aware float to prevent possible commas
				$value = sprintf('%F', $value);
			break;
			default:
				$value = (($value === NULL) ? ($is_null === true ? 'IS '.($op == '!=' ? 'NOT ' : '') : '').'NULL' : $value);
			break;
		}

		return (string) (strtoupper(substr($value, 0, 3)) != 'IS ') ? $op.$value : $value ;
	}

	/**
	 * Escapes fields. Support bd name, table name/table key/table alias , field name
	 * It can take db.table.field, field, table, table.field, alias.field etc...
	 *
	 * @param string|array $value field to escape
	 * @param string $prefixed prefix table or not
	 * @param bool $consider_table when no '.' is found, consider that value as a Table or Field
	 * @return string
	 */
	public static function escapeField($value, $prefix = '', $alias = array(), $consider_table = FALSE, $quotes = NEX_BACKTICK )
	{
		$table_trick = ($consider_table == FALSE) ? 1 : 0 ;

		$was_array = (is_array($value)) ? true : false ;
		$value = (array) $value ;
		$tmp = array();
		foreach($value as $val)
		{
			if($val == '*'){ $tmp[] = $val; continue ; }

			$val = trim(str_replace($quotes, '', strval($val)));

			$db = $table = $field = '' ;

			// Retrieve fields with tables
			if ( preg_match_all('/((([a-z_0-9]+)\.)?([a-z_0-9]+)\.)?([a-z_0-9]+)/i', $val, $matches, PREG_SET_ORDER) )
			{
				foreach ( $matches as $i => $match )
				{
					// db.table.field
					if ( $match[3] && $match[4] && $match[5]) {
						$db = $quotes.$match[3].$quotes.'.' ;
						$table = (in_array($match[4], $alias) ? $match[4].'.' : $quotes.$prefix.Nex::model($match[4].'.table').$quotes.'.') ;
						$field = $quotes.$match[5].$quotes ;
					}
					// table.field || db.table
					elseif ( $match[4] && $match[5] ) {
						if ( $consider_table ) {
							$db = $quotes.$match[4].$quotes.'.' ;
							$table = (in_array($match[5], $alias) ? $match[5].'.' : $quotes.$prefix.Nex::model($match[5].'.table').$quotes) ;
						}
						else {
							$table = (in_array($match[4], $alias) ? $match[4].'.' : $quotes.$prefix.Nex::model($match[4].'.table').$quotes.'.') ;
							$field = $quotes.$match[5].$quotes ;
						}
					}
					// table || field
					elseif ( $match[5] ) {
						if ( $consider_table ) {
							$table = (in_array($match[5], $alias) ? $match[5].'.' : $quotes.$prefix.Nex::model($match[5].'.table').$quotes) ;
						}
						else {
							$field = $quotes.$match[5].$quotes ;
						}
					}

					$val = str_replace($match[0], $db.$table.$field, $val);
				}
			}

			$tmp[0] = $val ;

			/*$boom = explode('.', trim(str_replace($quotes, '', strval($val))));
			$count = count($boom);
			$dot = ($count > 1) ? '.' : '';

			$bd = ($count > 2) ? $quotes.array_shift($boom).$quotes.'.' : '' ;

			if(count($boom) > $table_trick){
				$boom_shift = array_shift($boom);
				$table = ((in_array($boom_shift, $alias) == FALSE)
							? (( ($_table = Nex::model($boom_shift.'.table')) != FALSE )
								? $quotes.$prefix.$_table.$quotes.$dot // Table key
								: $quotes.$prefix.$boom_shift.$quotes.$dot) // Table name
							: $boom_shift.$dot ); // Table alias
			}
			// No table
			else{
				$table = '' ;
			}
			$field = ($count == 1 && $consider_table != FALSE) ? '' : $quotes.$boom[0].$quotes ;
			$tmp[] = $bd.$table.$field ;*/
		}

		return ($was_array == false) ? $tmp[0] : $tmp ;
	}

}
