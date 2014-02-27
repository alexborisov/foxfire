<?php

/**
 * FOXFIRE DB - MYSQLI DRIVER
 * Exchanges data with a MySQL database via PHP's 'mysqli' library 
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database Driver mysqli
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_db_driver_mysqli {


	var $rows_affected = 0;		// Count of affected rows by previous query
	var $insert_id = 0;		// The ID generated for an AUTO_INCREMENT column by the previous query (usually INSERT).

	var $db_host;			// Database Host
	var $db_name;			// Database Name
	var $db_user;			// Database User Name
	var $db_pass;			// Database Password
	
	var $charset;			// Database character set - symbols and encodings allowed to be stored to the db
					// @see http://dev.mysql.com/doc/refman/5.0/en/charset-general.html
					// @see http://dev.mysql.com/doc/refman/5.0/en/charset-mysql.html
	
	var $dbh;			// Database handle as used by PHP's mysqli class


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
		
		
		// CASE 1: Attach to a supplied database handle (typically $wpdb's $dbh)
		// =======================================================================
		if( !empty($args['dbh']) ){
		    		
			$this->dbh = $args['dbh'];			
			
			return true;
			
		}
		
		// CASE 2: Generate a new database handle
		// =======================================================================
		
		// WARNING: PHP doesn't release a db connection handle until either the script finishes
		// running, times-out, or the handle is explicitly released using mysql_close(). This
		// isn't a problem for most WordPress plugins, because the WP core bootstraps $wpdb
		// generating a db handle for the current PID, then everything connects through $wpdb, 
		// sharing that handle.
		// 
		// It can be a MAJOR problem for advanced database work and/or unit testing:
		// 
		//  1) If a class instance is destroyed without closing its $dbh, the dbh might
		//     be re-used, at random, by the next thread requesting a database handle
		//     
		//  2) Even if mysql_close($dbh) is placed inside the class' __destruct() method,
		//     there's no guarantee that it will be reliably called in the event of an
		//     unset($class_instance); or $class_instance = null;
		// 
		//  3) So if it's necessary to create a class instance using a unique database handle,
		//     its crucial that $class_instance->__destruct() be called manually before
		//     disposing of the class instance.
		//
	
		
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

		if(!FOX_sUtil::keyExists('charset', $args)){

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Missing 'charset' parameter",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}
		
		if(!FOX_sUtil::keyExists('collate', $args)){

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Missing 'collate' parameter",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}		
				
		$this->db_host = $args['db_host'];
		$this->db_name = $args['db_name'];		
		$this->db_user = $args['db_user'];
		$this->db_pass = $args['db_pass'];
		$this->charset = $args['charset'];				
		
		$this->dbh = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
		

		if( mysqli_connect_error() ){	// $mysqli->connect_error is broken in PHP < 5.2.9

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Failed to connect to SQL server",
				'data'=>array('args'=>$args, 'dbh'=>$this->dbh),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		    
		}

		$charset_ok = mysqli_set_charset($this->dbh, $this->charset);
				
		if(!$charset_ok) {

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error setting charset",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}
		
		return true;
		
	}
	
	
	/**
	 * Releases the driver's dbh handle
	 *
         * @version 1.0
         * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */
	
	function release(){
	    
		$close_ok = mysqli_close($this->dbh);
		
		if(!$close_ok){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error closing SQL connection",
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

	    
		if( empty($query) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty query",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		$sql_result = mysqli_query($this->dbh, $query, MYSQLI_USE_RESULT);
		$sql_error = mysqli_error($this->dbh);
		
		if($sql_error){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while running query",
				'data'=>array('query'=>$query, 'result'=>$sql_result, 'error'=>$sql_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}		

		if( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ){
		    
			$result = $sql_result;
			
		} 
		elseif( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ){
		    
			$this->rows_affected = mysqli_affected_rows($this->dbh);
			
			// Take note of the insert_id
			if( preg_match( '/^\s*(insert|replace)\s/i', $query) ){
			    
				$this->insert_id = mysqli_insert_id($this->dbh);
			}
			
			// Return number of rows affected
			$result = $this->rows_affected;
			
		} 
		elseif( !is_bool($sql_result) ){
		    
			$num_rows = 0;
			$result = array();
			
			while( $row = mysqli_fetch_object($sql_result) ){
			    
				$result[$num_rows] = $row;
				$num_rows++;
			}				
		}
		else {
			return  $sql_result;
		}
		
		// Prevent memory leakage
		
		if ( !is_bool($sql_result) ){
		    
			mysqli_free_result($sql_result);
		}
		
		return $result;		
		
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
	    
	    
		if( empty($query) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty query",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}
		
		try {
			$query_result = $this->query($query);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		    			

		// Extract var out of cached query results 
		
		if( !empty($query_result[$y]) ){
		    
			$values = array_values( get_object_vars($query_result[$y]) );
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

	    
		if( empty($query) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty query",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}
		
		try {
			$query_result = $this->query($query);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		    			

		// Extract var out of cached query results 
		
		if( !empty($query_result[$y]) ){
		    
			return $query_result[$y];
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

	    
		if( empty($query) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty query",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}
		
		try {
			$query_result = $this->query($query);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		$result = array();
		
		// Extract the column values
		
		for( $y = 0, $j = count($query_result); $y < $j; $y++ ) {		    
			
			if( !empty($query_result[$y]) ){

				$values = array_values( get_object_vars($query_result[$y]) );
				
				if( isset( $values[$x] ) && $values[$x] !== '' ){

					$result[$y] = $values[$x];
				}
				else {
					$result[$y] = null;
				}				
			}			
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

	    
		if( empty($query) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty query",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}
		
		try {
			$result = $this->query($query);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::query()",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		
		return $result;

	}


	/**
         * Starts an SQL transaction. Note that only InnoDB tables currently support transactions.
         *
         * @version 1.0
         * @since 1.0
         * @return bool | Exception on failure. True on success.
         */

	public function beginTransaction(){

		
		$command_ok = mysqli_autocommit($this->dbh, FALSE);
		
		if($command_ok){

			return true;
		}
		else {

			$sql_error = mysqli_error($this->dbh);
			
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Database failed to start transaction",
				'data'=> array("result"=>$sql_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

	}


	/**
         * Commits an SQL transaction. Note that only InnoDB tables currently support transactions.
         *
         * @version 1.0
         * @since 1.0
         * @return bool | Exception on failure. True on success.
         */

	public function commitTransaction(){
	
		
		$commit_ok = mysqli_commit ($this->dbh);
		
		if($commit_ok){

			// Setting mysqli_autocommit true appears to trigger a commit, but
			// isn't documented on php.net
		    
			$command_ok = mysqli_autocommit($this->dbh, true);

			if($command_ok){
			    
				return true;
			}
			else {
				$sql_error = mysqli_error($this->dbh);

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Database failed to toggle auto-commit mode after committing transaction",
					'data'=> array("result"=>$sql_error),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    			    			    
			}
		}
		else {

			$sql_error = mysqli_error($this->dbh);
			
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to commit transaction",
				'data'=> array("result"=>$sql_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		

	}


	/**
         * Rolls back a SQL transaction. Note that only InnoDB tables currently support transactions.
         *
         * @version 1.0
         * @since 1.0
         * @return bool | Exception on failure. True on success.
         */

	public function rollbackTransaction(){

				
		$rollback_ok = mysqli_rollback($this->dbh);
		
		if($rollback_ok){

			// Setting mysqli_autocommit true appears to trigger a commit, but
			// isn't documented on php.net
		    
			$command_ok = mysqli_autocommit($this->dbh, true);

			if($command_ok){
			    
				return true;
			}
			else {
				$sql_error = mysqli_error($this->dbh);

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Database failed to toggle auto-commit mode after rolling-back transaction",
					'data'=> array("result"=>$sql_error),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    			    			    
			}
		}
		else {

			$sql_error = mysqli_error($this->dbh);
			
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to rollback transaction",
				'data'=> array("result"=>$sql_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}		

	}
		
	
	/**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 * 
         * @version 1.0
         * @since 1.0
         *
         * @param string $query | query string in vsprintf() format
	 * @param array $params |
	 *
         * @return string | Prepared query string	 
	 */
	
	function prepare($query, $params=null){
	    
		
		// Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query );
		
		// Quote the strings, avoiding escaped strings like %%s
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); 
		
		// Replace our %r raw string token with an unquoted %s
		$query = preg_replace( '|(?<!%)%r|', "%s", $query ); 			

		$escaped_params = array();
		
		if($params){
		    
			$cast = new FOX_cast();
		    
			foreach($params as $param){

			    
//				if( !FOX_sUtil::keyExists('escape', $param) || !FOX_sUtil::keyExists('val', $param) ||
//				    !FOX_sUtil::keyExists('php', $param) || !FOX_sUtil::keyExists('sql', $param) ){
//				    
//					$text  = "SAFETY INTERLOCK TRIP [ANTI SQL-INJECTION] - All data objects passed to the ";
//					$text .= "database driver must include 'val', 'escape', 'php', and 'sql' parameters. This ";
//					$text .= "interlock cannot be disabled.";
//
//					throw new FOX_exception( array(
//						'numeric'=>1,
//						'text'=>$text,
//						'data'=>$param,
//						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
//						'child'=>null
//					));				    				    
//				}
				
				if( FOX_sUtil::keyExists('php', $param) && FOX_sUtil::keyExists('sql', $param) ){

					try {				   
						$cast_val = $cast->PHPToSQL($param['val'], $param['php'], $param['sql']);
					}			
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Error while casting parameter",
							'data'=>array("val"=>$param['val'], "php"=>$param['php'], "sql"=>$param['sql'] ),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>$child
						));
					}

				}
				else {
					$cast_val = $param['val'];
				}
				
				if( $param['escape'] !== false ) {

					// NOTE: parameters are in reverse order from mysql_real_escape_string()
					$escaped_params[] = mysqli_real_escape_string($this->dbh, $cast_val);
				}
				else {			    
					$escaped_params[] = $cast_val;
				}		    
			}
			unset($param);
		
		}
		
		$result = vsprintf($query, $escaped_params);			
		
		return $result;
		
	}	

}


?>