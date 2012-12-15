<?php

/**
 * FOXFIRE DB - MYSQL DRIVER
 * Exchanges data with a MySQL database via PHP's *deprecated* 'mysql' library 
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database Driver mysql
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_db_driver_mysql {


	var $rows_affected = 0;		// int		| Count of affected rows by previous query
	var $insert_id = 0;		// int		| The ID generated for an AUTO_INCREMENT column by the previous query (usually INSERT).
	var $last_result;		// array/null	| Results of the last query made

	var $db_host;			// string	| Database Host
	var $db_name;			// string	| Database Name
	var $db_user;			// string	| Database User	Name
	var $db_pass;			// string	| Database Password
	
	var $charset;			// string	| Database table columns charset
	
	var $dbh;			// int		| Database handle as returned by PHP's MySQL class


	// #################################################################################################### //


	/**
	 * Connects to the database server and selects a database
	 *
         * @version 1.0
         * @since 1.0
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param string $db_host | Database host
	 *	=> VAL @param string $db_name | Database name
	 *	=> VAL @param string $db_user | Database user name
	 *	=> VAL @param string $db_pass | Database password	 
	 *	=> VAL @param string $charset | Database charset
	 * 
	 * @return bool | Exception on failure. True on success.
	 */
	
	function __construct($args){

	    
		$args_default = array(
					'charset'=>'utf8'	
		);
		
		$args = wp_parse_args($args, $args_default);	
		
		
		if(!FOX_sUtil::keyExists('db_host', $args)){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing 'db_host' parameter",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}
		
		if(!FOX_sUtil::keyExists('db_name', $args)){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Missing 'db_name' parameter",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}
		
		if(!FOX_sUtil::keyExists('db_user', $args)){

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Missing 'db_user' parameter",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}		
		
		if(!FOX_sUtil::keyExists('db_pass', $args)){

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Missing 'db_pass' parameter",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}
		
//		register_shutdown_function( array( &$this, '__destruct' ) );

		$this->db_host = $args['db_host'];
		$this->db_name = $args['db_name'];		
		$this->db_user = $args['db_user'];
		$this->db_pass = $args['db_pass'];
		
		$this->dbh = @mysql_connect($this->db_host, $this->db_user, $this->db_pass, true);

		if(!$this->dbh){

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Failed to connect to SQL server",
				'data'=>array('args'=>$args, 'dbh'=>$this->dbh),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		    
		}

		// All versions of PHP past 5.2.3 have mysql_set_charset(), and all
		// versions of MySQL past 5.0.7 use it to set the charset instead
		// of the old 'SET NAMES %s' + 'COLLATE %s' method
		
		mysql_set_charset($this->charset, $this->dbh);
		
		
		try {
			$this->select($this->db_name, $this->dbh);
		}
		catch (BPM_exception $child) {

			throw new BPM_exception( array(
				'numeric'=>6,
				'text'=>"Error in self::select()",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
		
	}


	/**
	 * Selects a database using the current database connection.
	 *
         * @version 1.0
         * @since 1.0
	 * @param string $db MySQL database name
	 * @return bool | Exception on failure. True on success.
	 */
	
	function select($db){

	    
		$select_ok = @mysql_select_db($db, $this->dbh);
		
		if(!$select_ok){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error selecting database",
				'data'=>array('db'=>$db, 'error'=>$select_ok),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		    
		}
		
		return true;

	}


	/**
	 * Perform a MySQL database query, using current database connection.
	 *
         * @version 1.0
         * @since 1.0
	 * @param string $query Database query
	 * @return int|false Number of rows affected/selected or false on error
	 */
	
	function query($query){


		$return_val = 0;

		$this->result = @mysql_query( $query, $this->dbh );

		$sql_error = mysql_error($this->dbh);
		
		if($sql_error){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while running query",
				'data'=>array('query'=>$query, 'result'=>$this->result, 'error'=>$sql_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}		

		if( preg_match("/^\\s*(insert|delete|update|replace|alter) /i", $query) ){
		    
			$this->rows_affected = mysql_affected_rows($this->dbh);
			
			// Take note of the insert_id
			if( preg_match("/^\\s*(insert|replace) /i", $query) ){
			    
				$this->insert_id = mysql_insert_id($this->dbh);
			}
			
			// Return number of rows affected
			$return_val = $this->rows_affected;
			
		} 
		else {

			$num_rows = 0;
			
			while( $row = @mysql_fetch_object($this->result) ){
			    
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}

			@mysql_free_result($this->result);

			$return_val = $num_rows;
			
		}

		return $return_val;
		
	}


	/**
	 * Executes a SQL query and returns the value from the SQL result. If the SQL result contains more than 
	 * one column and/or more than one row, this function returns the value in the column and row specified.
	 *
         * @version 1.0
         * @since 1.0
	 * 
	 * @param string $query | SQL query as string
	 * @param int $x Optional. Column of value to return.  Indexed from 0.
	 * @param int $y Optional. Row of value to return.  Indexed from 0.
	 * 
	 * @return string | Exception on failure. Database query result (as string) on success.
	 */
	
	function get_var($query, $x=0, $y=0){
	    

		try {
			$this->query($query);
		}
		catch (BPM_exception $child) {

			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		    			

		// Extract var out of cached query results 
		
		if( !empty($this->last_result[$y]) ){
		    
			$values = array_values( get_object_vars($this->last_result[$y]) );
		}

		
		if( isset( $values[$x] ) && $values[$x] !== '' ){
		    
			return $values[$x];
		}
		else {
			return null;
		}

	}


	/**
	 * Executes a SQL query and returns the row from the SQL result
	 *
         * @version 1.0
         * @since 1.0
	 * @param string $query | SQL query as string
	 * @param int $y Optional. Row to return. Indexed from 0.
	 * 
	 * @return string | Exception on failure. Database query result (as string) on success.
	 */
	
	function get_row($query, $y=0 ){

	    
		try {
			$this->query($query);
		}
		catch (BPM_exception $child) {

			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		    			

		// Extract var out of cached query results 
		
		if( !empty($this->last_result[$y]) ){
		    
			return $this->last_result[$y];
		}
		else {
			return null;
		}
		
	}



	/**
	 * Executes a SQL query and returns the column from the SQL result.If the SQL result contains  
	 * more than one column, this function returns the column specified.
	 *
         * @version 1.0
         * @since 1.0
	 * @param string $query | SQL query as string
	 * @param int $x | Column to return. Indexed from 0.
	 * 
	 * @return array | Exception on failure. Database query result (as array of strings) on success.
	 */
	function get_col($query, $x=0 ){

	    
		try {
			$this->query($query);
		}
		catch (BPM_exception $child) {

			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		$result = array();
		
		// Extract the column values
		
		for( $i = 0, $j = count($this->last_result); $i < $j; $i++ ) {
		    
			$result[$i] = $this->get_var(null, $x, $i);
		}
		
		return $result;
		
	}


	/**
	 * Executes a SQL query and returns the entire SQL result.
	 *
         * @version 1.0
         * @since 1.0
	 * @param string $query | SQL query as string
	 * @return mixed | Exception on failure. Database query result on success.
	 */
	
	function get_results($query){

	    
		try {
			$this->query($query);
		}
		catch (BPM_exception $child) {

			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		
		return $this->last_result;

	}


}


?>