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

class FOX_db_driver_mysql_i {


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
	
	var $foreign_dbh;		// True if this instance is bound to a foreign dbh. False if not.


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
			
			// Set the foreign dbh flag to prevent the class destroying the SQL 
			// server connection when self::__destruct() is called
			
			$this->foreign_dbh = true;
			
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
		

		if(!$this->dbh){

			throw new FOX_exception( array(
				'numeric'=>7,
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
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>8,
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

	    
		if( empty($query) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty query",
				'data'=>$query,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		$sql_result = mysql_query($query, $this->dbh);
		$sql_error = mysql_error($this->dbh);
		
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
		    
			$this->rows_affected = mysql_affected_rows($this->dbh);
			
			// Take note of the insert_id
			if( preg_match( '/^\s*(insert|replace)\s/i', $query) ){
			    
				$this->insert_id = mysql_insert_id($this->dbh);
			}
			
			// Return number of rows affected
			$result = $this->rows_affected;
			
		} 
		elseif( is_resource($sql_result) ){
		    
			$num_rows = 0;
			$result = array();
			
			while( $row = mysql_fetch_object($sql_result) ){
			    
				$result[$num_rows] = $row;
				$num_rows++;
			}				
		}
		else {
			return  $sql_result;
		}
		
		// Prevent memory leakage
		
		if ( is_resource($sql_result) ){
		    
			mysql_free_result($sql_result);
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

		
		try {
			$db_result = self::query("START TRANSACTION");
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if($db_result){

			return true;
		}
		else {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to start a transaction, \n",
				'data'=> array("result"=>$db_result),
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


		try {
			$db_result = self::query("COMMIT");
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if($db_result){

			return true;
		}
		else {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to commit the transaction, \n",
				'data'=> array("result"=>$db_result),
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


		try {
			$db_result = self::query("ROLLBACK");
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::query()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if($db_result){

			return true;
		}
		else {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to rollback transaction. \n",
				'data'=> array("result"=>$db_result),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}


	}
	
	
	/**
	 * Escapes content by reference for insertion into the database, for security
	 *
         * @version 1.0
         * @since 1.0
	 * @param string $string to escape
	 * @return void
	 */
	function escape_by_ref(&$string){
	    
		// NOTE: parameters are in reverse order from mysql_real_escape_string()	    
		$string = mysqli_real_escape_string($this->dbh, $string);
	}
		
	
	/**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 * 
         * @version 1.0
         * @since 1.0
         *
         * @param array $args | Query args array
	 *	=> VAL @param string [0] | First key in array is query string in vsprintf() format
	 *	=> VAL @param mixed  [N] | Each successive key is a var referred to in the query string
	 *
         * @return string | Prepared query string	 
	 */
	
	function prepare($args){

		$query = array_shift($args);
		
		// Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query );
		
		// Quote the strings, avoiding escaped strings like %%s
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); 

		array_walk($args, array(&$this, 'escape_by_ref'));

		return @vsprintf($query, $args);
		
	}	

}


?>