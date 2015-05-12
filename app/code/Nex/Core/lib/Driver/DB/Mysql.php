<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

class Driver_DB_Mysql extends Driver_DB
{
    /**
     * Execute Sql query with givin Sql command and store results
     * @param string $sql Complete sql query
     */
    public function runQuery($sql)
    {
        try {
            mysqli_real_query(self::$connection[$this->database], $sql);

            if (mysqli_errno(self::$connection[$this->database])) {
                throw new Exception(mysqli_error(self::$connection[$this->database]), mysqli_errno(self::$connection[$this->database]));
            }

            $this->result = mysqli_store_result(self::$connection[$this->database]);
        }
        catch (Exception $e) {
            Nex::exception($e);
        }
    }

    public function runUnbuffedQuery($sql)
    {
        try {
            mysqli_real_query(self::$connection[$this->database], $sql);

            if (mysqli_errno(self::$connection[$this->database])) {
                throw new Exception(mysqli_error(self::$connection[$this->database]), mysqli_errno(self::$connection[$this->database]));
            }

            $this->result = mysqli_use_result(self::$connection[$this->database]);
        }
        catch (Exception $e) {
            Nex::exception($e);
        }
    }

    /**
     * Return number of object in list
     */
    public function count() { return is_object($this->result) && mysqli_num_rows($this->result); }

    /**
     * Return number of affected rows
     */
    public function countAffected() { return mysqli_affected_rows(self::$connection[$this->database]); }

    /**
     * Return last insert id
     */
    public function lastInsertId() { return mysqli_insert_id(self::$connection[$this->database]); }

    /**
     * Free mysql memory
     */
    public function freeResources()
    {
        if ($this->result && is_object($this->result)) {
            // Bug with php 5.3
            //$this->result->free();
        }
    }

    public function fetchRows()
    {
        // mysqli_fetch_all() needs mysqlnd driver, so we'll use good old loop
        // $this->rows = mysqli_fetch_all(self::$connection[$this->database], MYSQLI_ASSOC);

        while ( $r = mysqli_fetch_assoc($this->result) ) {
            $this->rows[] = $r ;
        }

        return $this->rows ;
    }

    /**
     * This function connects to the database using the Parameters or the default config
     * @param string $database key of database in config
     */
    public function connect()
    {
        $user = $this->config['user'];
        $pass = $this->config['pass'];
        $host = $this->config['host'];
        $name = $this->config['name'];

        // Make sure that connection wasn't established yet
        if ( isset(self::$connection[$this->database]) ) {
            return ($this->lastDB != $name ? $this->useDB($name) : true) ;
        }

        try {
            self::$connection[$this->database] = mysqli_connect($host, $user, $pass, $name);

            if ( mysqli_connect_errno(self::$connection[$this->database]) ) {
                throw new Exception(mysqli_connect_error(self::$connection[$this->database]), mysqli_connect_errno(self::$connection[$this->database]));
            }

            if ( $this->config['charset'] ) {
                mysqli_set_charset(self::$connection[$this->database], $this->config['charset']);
            }

            $this->useDB($name);
        }
        catch (Exception $e) {
            Nex::exception($e);
        }
    }

    /**
     * This function select the database. If connection wasn't established yet,
     * it will try to connect with default param
     */
    public function useDB($name)
    {
        try {
            mysqli_select_db(self::$connection[$this->database], $name);

            if (mysqli_errno(self::$connection[$this->database])) {
                throw new Exception(mysqli_error(self::$connection[$this->database]), mysqli_errno(self::$connection[$this->database]));
            }
        } catch (Exception $e) {
            Nex::exception($e);
        }
    }

    public function escape($value, $quote = NEX_COMPAT)
    {
        switch (gettype($value)) {
            case 'string':
                $value = ((in_array(strtoupper($value), array('NOW()', 'NULL', 'IS NULL', 'RAND()'))) ? $value : $quote.mysqli_real_escape_string(self::$connection[$this->database], $value).$quote);
                break;
            case 'boolean':
                $value = (int) $value;
                break;
            case 'array':
                $value = $quote.mysqli_real_escape_string(self::$connection[$this->database], serialize($value)).$quote;
                break;
            case 'double':
                // Convert to non-locale aware float to prevent possible commas
                $value = sprintf('%F', $value);
                break;
            default:
                $value = ($value === null) ? 'NULL' : $quote.$value.$quote ;
                break;
        }

        return $value ;
    }
}