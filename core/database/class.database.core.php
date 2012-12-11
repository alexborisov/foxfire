<?php

/**
 * FOXFIRE CORE DATABASE CLASS
 * Handles advanced database functionality for the plugin
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

class FOX_db {


	var $db;				    // Local copy of database singleton
	var $dbh;				    // MySQL connection handle for this instance

	var $base_prefix;			    // Base prefix for database tables
	var $charset;				    // Database table columns charset
	var $collate;				    // Database table columns collate

	var $in_transaction = false;		    // True if this instance is engaged in a transaction

	var $num_queries = 0;			    // Number of queries made since this instance was created
	var $num_rows = 0;			    // Count of rows returned by the last query (SELECT)
	var $rows_affected = 0;			    // Count of rows affected by the last query (INSERT, UPDATE, DELETE)

	var $insert_id = 0;			    // The ID generated for an AUTO_INCREMENT column by the last INSERT or 
						    // REPLACE query. In the case of a MULTI-ROW query, the ID of the FIRST
						    // row will be returned. ID's of the remaining rows can be calculated 
						    // as [$insert_id + ($row_number_in_data_array * $auto_increment_increment)]

	var $auto_increment_increment = 1;	    // The auto-increment increment value used by the SQL server. Value should
						    // always be (int)1 unless a massive replication setup is being used
						    // @link http://dev.mysql.com/doc/refman/5.5/en/replication-options-master.html#sysvar_auto_increment_increment

	var $last_error = '';			    // The last error returned by SQL server
	var $last_query;			    // Full SQL string of the last query made
	var $last_result;			    // Results of the last query made

	var $builder;				    // Query generator object
	var $runner;				    // Query runner object


	/**
	 * DEBUG FLAGS - Output will be saved to '/core/utils/bp_media_log.txt
	 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_debug
	 * ============================================================================================================ //
	 */

		var $print_query_args = false;		    // Log args passed to the query generators
		var $print_query_sql = false;		    // Log SQL strings produced by the query generators
		var $print_result_raw = false;		    // Log raw data returned from SQL server
		var $print_result_cast = false;		    // Log returned data from the type caster
		var $print_result_formatted = false;	    // Log returned data from results formatter

		var $disable_typecast_write = false;	    // Do not convert between PHP and SQL data types when
		var $disable_typecast_read = false;	    // reading / writing the database. Used *only* during
							    // unit testing, with carefully formatted test data.
							    // @see class.database.typecast.php

	// ============================================================================================================ //


	function FOX_db(&$arg_db=null) {

		$this->__construct($arg_db);
	}


	function __construct(&$arg_db=null){

		// Hybrid dependency injection. If no dependencies are injected as args, the constructor
		// uses the wp global variables / singletons.

		if($arg_db){

			$this->db =& $arg_db;
			$this->dbh =& $arg_db->dbh;
			$this->base_prefix =& $arg_db->base_prefix;
			$this->charset =& $arg_db->charset;
			$this->collate =& $arg_db->collate;
			$this->builder = new FOX_queryBuilder($this);
			$this->runner = new FOX_queryRunner($this);
		}
		else {
			global $wpdb;
			$this->db =& $wpdb;
			$this->dbh =& $wpdb->dbh;
			$this->base_prefix =& $wpdb->base_prefix;
			$this->charset =& $wpdb->charset;
			$this->collate =& $wpdb->collate;
			$this->builder = new FOX_queryBuilder($this);
			$this->runner = new FOX_queryRunner($this);
		}

		// Because our local stats variables are bound by reference to the host
		// db instance, they automatically update

		$this->num_queries =& $this->db->num_queries;
		$this->num_rows =& $this->db->num_rows;
		$this->rows_affected =& $this->db->rows_affected;
		$this->insert_id =& $this->db->insert_id;
		$this->last_error =& $this->db->last_error;
		$this->last_query =& $this->db->last_query;
		$this->last_result =& $this->db->last_result;
	}


	/**
         * Starts an SQL transaction. Note that only InnoDB tables currently support transactions.
         *
         * @version 1.0
         * @since 1.0
         * @return bool | Exception on failure. True on success.
         */

	public function beginTransaction(){

		// If our database handle already has a transaction open, quit
		// ===============================================================

		if($this->in_transaction == true){

			if($this->print_query_sql == true){
				FOX_debug::addToFile("START TRANSACTION FAILED (TRANSACTION ALREADY OPEN)");
			}
			
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Database is already in a transaction. \n",
				'data'=> array("handle" => $this->dbh),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));			

		}

		// Otherwise, immediately set the semaphore
		// ===============================================================

		$this->in_transaction = true;

		$db_result = mysql_query("START TRANSACTION", $this->dbh);

		if($db_result){

			if($this->print_query_sql == true){
				FOX_debug::addToFile("START TRANSACTION (SUCCESS)");
			}

			return (bool)$db_result;

		}
		else {

			if($this->print_query_sql == true){
				FOX_debug::addToFile("START TRANSACTION FAILED (DATABASE ERROR)");
			}

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to start a transaction, \n",
				'data'=> array("handle"=>$this->dbh, "result"=>$db_result),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
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


		// If our database handle doesn't have a transaction open, quit
		// ===============================================================

		if($this->in_transaction != true){

			if($this->print_query_sql == true){
				FOX_debug::addToFile("COMMIT TRANSACTION FAILED (TRANSACTION NOT OPEN)");
			}

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Database not currently in a transaction. \n",
				'data'=> array("handle" => $this->dbh),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}


		// Otherwise, commit the transaction
		// ===============================================================

		$db_result = mysql_query("COMMIT", $this->dbh);

		if($db_result){

			$this->in_transaction = false;

			if($this->print_query_sql == true){
				FOX_debug::addToFile("COMMIT TRANSACTION (SUCCESS)");
			}

			return (bool)$db_result;

		}
		else {

			if($this->print_query_sql == true){
				FOX_debug::addToFile("COMMIT TRANSACTION FAILED (DATABASE ERROR)");
			}

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to commit the transaction, \n",
				'data'=> array("handle"=>$this->dbh, "result"=>$db_result),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
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


		// If our database handle doesn't have a transaction open, quit
		// ===============================================================

		if($this->in_transaction != true){

			if($this->print_query_sql == true){
				FOX_debug::addToFile("ROLLBACK TRANSACTION FAILED (TRANSACTION NOT OPEN)");
			}

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Database not currently in a transaction. \n",
				'data'=> array("handle" => $this->dbh),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}


		// Otherwise, rollback the transaction
		// ===============================================================

		$db_result = mysql_query("ROLLBACK", $this->dbh);

		if($db_result){

			$this->in_transaction = false;

			if($this->print_query_sql == true){
				FOX_debug::addToFile("ROLLBACK TRANSACTION (SUCCESS)");
			}

			return (bool)$db_result;

		}
		else {

			if($this->print_query_sql == true){
				FOX_debug::addToFile("ROLLBACK TRANSACTION FAILED (DATABASE ERROR)");
			}

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Database failed to rollback transaction. \n",
				'data'=> array("handle"=>$this->dbh, "result"=>$db_result),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}


	}



	/**
         * Runs a SELECT query with a JOIN statement on a pair of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $primary | Primary table class name and args
	 *	=> VAL @param string/array $class | Name of class that owns the primary table (as string), or the class's $struct array (as array)
	 *	=> ARR @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of the column in the db table this key describes
	 *		=> VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		=> VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param array $join | Joined tables class names and args
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string/array $class | Name of class that owns the joined table (as string), or the class's $struct array (as array)
	 *	    => ARR @param array $on | Join condition for this table
	 *		=> VAL @param string $pri | Name of primary table column to join on
	 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 * 	 	    => VAL @param string $sec | Name of joined table column to join on
	 *	    => ARR @param array $args | Args in the form: array("col"=>column_name, "op"=>"<, >, =, !=", "val"=>"int | string | array()")
	 *		=> ARR @param int '' | Array index
	 *		    => VAL @param string $col | Name of the column this key describes
	 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
	 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
	 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
	 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to sort by
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param bool/array $count | Count columns. Bool TRUE to use COUNT(DISTINCT primary_table.*)
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Column to count
	 *	=> ARR @param bool/array $sum | Sum columns.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Column to sum
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 *
	 * @param bool/array $columns | Primary table columns to use in query. NULL to select all columns. FALSE to select no columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings

         * @return bool/int/array | Exception on failure, int on count, array of rows on success.
         */

	public function runSelectQueryJoin($primary, $join, $columns=null, $ctrl=null){


		// Add default control params
		// ==========================

		$ctrl_default = array(
			"format"=>"array_object"
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


		// Trap Common Errors
		// =======================

		if( !is_array($primary) ){	
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty primary arg",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		if( !is_array($join) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Called with empty join arg",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in query generator",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}

		// Run on SQL server
		// =======================

		try {
			$result = $this->runner->runQuery($query, $ctrl);			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error executing query on SQL server",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}

		return $result;

	}

	/**
         * Runs a SELECT query with a LEFT JOIN statement on two or more plugin tables
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $primary | Primary table class name and args
	 *	=> VAL @param string/array $class | Name of class that owns the primary table (as string), or the class's $struct array (as array)
	 *	=> ARR @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of the column in the db table this key describes
	 *		=> VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		=> VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param array $join | Joined tables class names and args
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string/array $class | Name of class that owns the joined table (as string), or the class's $struct array (as array)
	 *	    => ARR @param array $on | Join condition for this table
	 *		=> VAL @param string $pri | Name of primary table column to join on
	 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 * 	 	    => VAL @param string $sec | Name of joined table column to join on
	 *	    => ARR @param array $args | Args in the form: array("col"=>column_name, "op"=>"<, >, =, !=", "val"=>"int | string | array()")
	 *		=> ARR @param int '' | Array index
	 *		    => VAL @param string $col | Name of the column this key describes
	 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param bool/array $columns | Primary table columns to use in query. NULL to select all columns. FALSE to select no columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
	 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
	 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
	 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to sort by
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param bool/array $count | Count columns. Bool TRUE to use COUNT(DISTINCT primary_table.*)
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Column to count
	 *	=> ARR @param bool/array $sum | Sum columns.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Column to sum
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 * 
         * @return bool/int/array | Exception on failure, int on count, array of rows on success.
         */

	public function runSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl=null){	   
		
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"format"=>"array_object"
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


		// Trap Common Errors
		// =======================

		if( !is_array($primary) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty primary arg",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		if( !is_array($join) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Called with empty join arg",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		// Build query string
		// =======================

		try {
			$query = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);			
		}
		catch (FOX_exception $child ) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in query generator",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    		    
		}

		// Run on SQL server
		// =======================

		try {
			$result = $this->runner->runQuery($query, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error executing query on SQL server",
				'data'=>array( "primary"=>$primary, "join"=>$join, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;
	}

	/**
         * Runs a SELECT query on one of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns. FALSE to select no columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
	 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
	 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
	 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of column to sort by
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *
	 *	=> VAL @param bool/string/array $count | Return a count of db rows. Bool true to use COUNT(*). Single column as string. Multiple columns as array.
	 *      => VAL @param bool/string/array $sum | Return a sum of db rows. Single column as string. Multiple columns as array.
	 *
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 * 
         * @return bool/int/array | Exception on failure. Int on count. Array of rows on success.
         */

	public function runSelectQuery($struct, $args=null, $columns=null, $ctrl=null){
	        
	    
		// Handle SELECT *
		// ==========================
	    
		if(!$args){
			$args = array();
		}
		
		// Add default control params
		// ==========================


		$ctrl_default = array(
			"format"=>"array_object",
			"args_format"=>"default",			
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


		// Trap Common Errors
		// =======================

		$col_names = array_keys($struct['columns']);


		switch($ctrl['args_format']){

			case "default" : {

				foreach($args as $arg){

					if( array_search($arg["col"], $col_names) === false ){
					
						throw new FOX_exception( array(
							'numeric'=>1,
							'text'=>"Called with argument referencing nonexistent column name",
							'data'=>array("faulting_column"=>$arg["col"], "struct"=>$struct, "args"=>$args, 
								      "columns"=>$columns, "ctrl"=>$ctrl),
							'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
							'child'=>null
						));
					}
				}
				unset($arg);

			} break;

			case "multi" : {

				foreach($args as $arg_block){

					foreach($arg_block as $arg){

						if( array_search($arg["col"], $col_names) === false ){

							throw new FOX_exception( array(
								'numeric'=>2,
								'text'=>"Called with argument referencing nonexistent column name",
								'data'=>array("faulting_column"=>$arg["col"], "struct"=>$struct, "args"=>$args, 
									      "columns"=>$columns, "ctrl"=>$ctrl),
								'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
								'child'=>null
							));
						}

					}
					unset($arg);
				}
				unset($arg_block);

			} break;

			case "matrix" : {

				foreach($args["args"] as $arg){

					if( array_search($arg["col"], $col_names) === false ){

						throw new FOX_exception( array(
							'numeric'=>3,
							'text'=>"Called with argument referencing nonexistent column name",
							'data'=>array("faulting_column"=>$arg["col"], "struct"=>$struct, "args"=>$args, 
								      "columns"=>$columns, "ctrl"=>$ctrl),
							'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
							'child'=>null
						));
					}
				}
				unset($arg);

			} break;

		}


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildSelectQuery($struct, $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error in query generator",
				'data'=>array( "struct"=>$struct, "args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		// Run on SQL server
		// =======================

		try {
			$result = $this->runner->runQuery($query, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}

	/**
         * Runs single-column keyed SELECT query on one of the plugin's db tables
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *
	 * @param string $col | Column name to use for WHERE construct
	 * @param string $op | Comparison operator ">=", "<=", ">", "<", "=", "!=", "<>"
	 * @param int/string $val | Comparison value to use in WHERE construct
	 *
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns. FALSE to select no columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
	 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
	 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
	 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of column to sort by
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *
	 *	=> VAL @param bool/string/array $count | Return a count of db rows. Bool true to use COUNT(*). Single column as string. Multiple columns as array.
	 *      => VAL @param bool/string/array $sum | Return a sum of db rows. Single column as string. Multiple columns as array.
	 *
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 *
         * @return bool/array | Exception on failure. Query array on success.
         */

	public function runSelectQueryCol($struct, $col, $op, $val, $columns=null, $ctrl=null){


		// Add default control params
		// ==========================

		$ctrl_default = array(
			"format"=>"array_object"
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildSelectQueryCol($struct, $col, $op, $val, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "col"=>$col, "op"=>$op, "val"=>$val,
					      "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$result = $this->runner->runQuery($query, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array("struct"=>$struct, "col"=>$col, "op"=>$op, "val"=>$val,
					      "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}

	/**
         * Runs an UPDATE query on one of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *
         * @param array/object $data | Class with $column_1, $column_2 in the namespace, or array of the form ("column_1"=>"value_1", "column_2"=>"value_2")
	 * 	=> ARR @param string | Name of the db column this key describes
	 *	    => VAL @param int/string | Value to assign to the column
	 *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param array $columns | Columns to include / exclude from query.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 * 
	 * @param array $ctrl | Control args (not yet implemented)
	 *
         * @return int | Exception on failure. Int number of rows affected on success.
         */

	public function runUpdateQuery($struct, $data, $args, $columns=null, $ctrl=null){


		// Handle *
		// ==========================
	    
		if(!$args){
			$args = array();
		}
		
		// Trap Common Errors
		// =======================

		$col_names = array_keys($struct['columns']);

		foreach($args as $arg){

			if( array_search($arg["col"], $col_names) === false ){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Called with argument referencing nonexistent db column",
					'data'=>array("faulting_column"=>$arg["col"], "struct"=>$struct, "data"=>$data, 
						      "args"=>$args, "columns"=>$columns, 'ctrl'=>$ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));

			}
		}

		// Build query string
		// =======================

		try {
			$query = $this->builder->buildUpdateQuery($struct, $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "data"=>$data, "args"=>$args, "columns"=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var'));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error executing query on SQL server",
				'data'=>array('query'=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}


        /**
         * Runs a single-column keyed UPDATE query on one of the plugin's db tables
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *
         * @param array/object $data | Class with $column_1, $column_2 in the namespace, or array of the form ("column_1"=>"value_1", "column_2"=>"value_2")
	 * 	=> KEY @param string | Name of the db column this key describes
	 *	    => VAL @param int/string | Value to assign to the column
	 *
	 * @param string $col | Column name to use for WHERE construct
	 * @param string $op | Comparison operator ">=", "<=", ">", "<", "=", "!=", "<>"
	 * @param int/string $val | Comparison value to use in WHERE construct
	 *
	 * @param array $columns | Columns to include / exclude from query.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 *
	 * @param array $ctrl | Control args (not yet implemented)
	 * 
         * @return int | Exception on failure. Int number of rows affected on success.
         */

	public function runUpdateQueryCol($struct, $data, $col, $op, $val, $columns=null, $ctrl=null){


		// Build query string
		// =======================

		try { 
			$query = $this->builder->buildUpdateQueryCol($struct, $data, $col, $op, $val, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "data"=>$data, "col"=>$col, "op"=>$op,
					      "val"=>$val, "columns"=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var'));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array('query'=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}


	/**
         * Runs an INSERT query that inserts a SINGLE row into one of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> ARR @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
         * @param array/object $data | Row array
	 *	    => KEY @param string | Name of the db column this key describes
	 *		=> VAL @param int/string | Value to assign to the column
	 *
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 *
	 * @param array $ctrl | Control args (not yet implemented)
	 * 
         * @return int | Exception on failure. Int number of rows affected on success.
         */

	public function runInsertQuery($struct, $data, $columns=null, $ctrl=null){


		// Wrap data array inside another array to handle buildInsertQuery's
		// multi-insert data format
		$data = array($data);

		// Build query string
		// =======================

		try {
			$query = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "data"=>$data, "columns"=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var'));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}


	/**
         * Runs an INSERT query that inserts MULTIPLE rows into one of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> ARR @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
         * @param array/object $data | Array of row arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => KEY @param string | Name of the db column this key describes
	 *		=> VAL @param int/string | Value to assign to the column
	 *
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 * 
	 * @param array $ctrl | Control args (not yet implemented)
	 * 
         * @return int | Exception on failure. Int number of rows affected on success.
         */

	public function runInsertQueryMulti($struct, $data, $columns=null, $ctrl=null){


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "data"=>$data, "columns"=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var'));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}

	/**
         * Runs an INDATE [INsert-upDATE query on one of the plugin's db tables. If the query attempts to insert a row
	 * whose primary key already exists, the existing row will be updated. Indate queries ONLY work on db tables
	 * that have a primary key (or a multi-column composite primary key).
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> ARR @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
         * @param array/object $data | Class with $column_1, $column_2 in the namespace, or array of the form ("column_1"=>"value_1", "column_2", "value_2")
	 *
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 * 
	 * @param array $ctrl | Control args (not yet implemented)
	 * 
         * @return int | Exception on failure. Int number of rows affected on success (int 1 if a row is inserted or updated, int 0 if no change) 
         */

	public function runIndateQuery($struct, $data, $columns=null, $ctrl=null){


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "data"=>$data, "columns"=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var') );
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}

	/**
         * Runs a DELETE query on one of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param string $args_format | "default" to use standard format, "multi", "matrix", or "trie"
	 * 
         * @return int | Exception on failure. Int number of rows deleted on success.
         */

	public function runDeleteQuery($struct, $args, $ctrl=null){


		// Add default control params
		// ==========================

		$ctrl_default = array(
			"args_format"=>"default"
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildDeleteQuery($struct, $args, $ctrl);		
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "args"=>$args, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var') );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}

        /**
         * Runs a single-column keyed DELETE query on one of the plugin's db tables.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 * @param string $col | Column name to use for WHERE construct
	 * @param string $op | Comparison operator ">=", "<=", ">", "<", "=", "!=", "<>"
	 * @param int/string $val | Comparison value to use in WHERE construct
	 * 
	 * @param array $ctrl | Control args (not yet implemented)
	 * 	 
         * @return int | Exception on failure. Int number of rows deleted on success.
         */

	public function runDeleteQueryCol($struct, $col, $op, $val, $ctrl=null){


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildDeleteQueryCol($struct, $col, $op, $val);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "col"=>$col, "op"=>$op, "val"=>$val, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$this->runner->runQuery($query, array('format'=>'var') );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $this->rows_affected;

	}

	/**
         * Adds a database table, given an array defining the table's structure
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> VAL @param string 'engine' | Name of table db engine
	 *	=> VAL @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 * 
	 * @param array $ctrl | Control args (not yet implemented)
	 * 	 
         * @return bool | True on success. Exception on failure.
         */

	public function runAddTable($struct, $ctrl=null){


		if($this->print_query_args == true){

			ob_start();
			print_r($struct);
			$out = ob_get_clean();
			FOX_debug::addToFile($out);
		}
		
		// Check that the table doesn't already exist in the db
		// ===========================================================
		 
		$sql =  "SELECT * FROM INFORMATION_SCHEMA.TABLES ";
		
		// The "table schema" column in MySQL's information_schema is actually
		// the database name. Since the schema is shared across all databases on
		// the SQL server, we have to specify it to prevent getting hits on other
		// databases that contain the same table name
		
                $sql .= "WHERE TABLE_SCHEMA = '" . $this->db->dbname . "' ";		
                $sql .= "AND TABLE_NAME = '" . $struct['table']. "'";

		try {
			$matches = $this->runner->runQuery($sql, array("format"=>"raw"));
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Failure during check if table exists query",
				'data'=>array('struct'=>$struct, 'sql'=>$sql),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		if($matches){		    
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Target table already exists in the database",
				'data'=>array('struct'=>$struct, 'sql'=>$sql, 'matches'=>$matches),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));		    
		}
				
		 						
		// Build the query
		// ===========================================================
		
		try {		    
			$sql = $this->builder->buildAddTable($struct);		
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Failure in query builder",
				'data'=>array('struct'=>$struct, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
	     
		// Run it on the SQL server
		// ===========================================================
		
		try {		    
			$sql_response = $this->runner->runQuery($sql, array("format"=>"raw"));	
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Failure when running query on SQL server",
				'data'=>array('struct'=>$struct, 'sql'=>$sql, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
		
		// Check the table was successfully added
		// ===========================================================	
		
		if($sql_response == true){
		
			return true;
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Table was not successfully created",
				'data'=>array('struct'=>$struct, 'sql'=>$sql, 'ctrl'=>$ctrl, 'sql_response'=>$sql_response),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));		    
		}

	}

	/**
         * Drops a database table, given an array defining the table's structure
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> VAL @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
	 * @param array $ctrl | Control args (not yet implemented)
	 * 	 
         * @return bool | True on success. Exception on failure.
         */

	public function runDropTable($struct, $ctrl=null){


		// Check table array exists, because if its sent in with
		// an empty name variable, it could damage the database
		// =======================================================

		if($this->print_query_args == true){

			ob_start();
			print_r($struct);
			$out = ob_get_clean();
			FOX_debug::addToFile($out);
		}

		if(empty($struct["table"])){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with missing table name",
				'data'=>array("struct"=>$struct, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}


		// Build query string
		// =======================

		try {
			$query = $this->builder->buildDropTable($struct);
						
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		// Run on SQL server
		// =======================

		try {
			$result = $this->runner->runQuery($query, array("format"=>"raw"));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}

	/**
         * Deletes all data from a database table, given an array defining the table's structure
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> VAL @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
	 * @param array $ctrl | Control args (not yet implemented)
	 * 	 
         * @return bool | True on success. Exception on failure.
         */

	public function runTruncateTable($struct, $ctrl=null){


		// Check table array exists, because if its sent in with
		// an empty name variable, it could damage the database
		// =======================================================

		if($this->print_query_args == true){

			ob_start();
			print_r($struct);
			$out = ob_get_clean();
			FOX_debug::addToFile($out);
		}

		if( empty($struct["table"]) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with missing table name",
				'data'=>array("struct"=>$struct, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		// Build query string
		// =======================

		try {
			$query = $this->builder->buildTruncateTable($struct);						
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Run on SQL server
		// =======================

		try {
			$result = $this->runner->runQuery($query, array("format"=>"raw") );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error executing query on SQL server",
				'data'=>array("query"=>$query, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}

	
} // End of class FOX_db

?>