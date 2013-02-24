<?php

/**
 * FOXFIRE DATABASE QUERY BUILDER CLASS
 * Generates SQL statements "on-the-fly" for use by the core database class
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

class FOX_queryBuilder {


	var $base_prefix;	
	var $charset;
	var $collate;

	// ============================================================================================================ //


	function __construct(&$parent_class){


		$this->base_prefix =& $parent_class->base_prefix;
		$this->charset =& $parent_class->charset;
		$this->collate =& $parent_class->collate;
		
	}


	/**
         * Implodes an array of heirarchical datastore keys into a single query $args array
         *
         * @version 1.0
         * @since 1.0
	 * 
	 * @param array $key_col | Array of column names
	 * 
	 * @param array $args | Array of key arrays
	 *	=> ARR @param int '' | Array containing "column_name"=>"value"
	 * 
	 * @param array $ctrl | Control args
	 *	=> VAL @param bool $optimize | True to optimize the $args matrix
	 * 	=> VAL @param string $prefix | Prefix to add to column names
	 *	=> VAL @param bool $trap_null | Throw an exception if one or more walks 
	 *					in the args array collapses to "WHERE 1 = 1"
	 *
         * @return array | Exception on failure. Data array on success
         */

	public function buildWhereMatrix($struct, $key_col, $args, $ctrl=null){

	    
		$ctrl_default = array(
					'mode'=>'matrix'
		);
		
		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);		
		
		try {
			$cls = new FOX_queryBuilder_whereMatrix($struct, $key_col, $args, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error building object",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		
		try {
			return $cls->render();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error during render",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

	}

	
	/**
         * Implodes a trie of WHERE conditions into an optimized query $args array
         *
         * @version 1.0
         * @since 1.0
	 * @param array $key_col | Array of column names
	 * 
	 * @param array $args | Array of key arrays
	 *	=> ARR @param int '' | Array containing "column_name"=>"value"
	 * 
	 * @param array $ctrl | Control args
	 *	=> VAL @param bool $optimize | True to optimize the $args matrix
	 * 	=> VAL @param string $prefix | True to optimize the $args matrix	 
	 *
         * @return bool | Exception on failure. Data array on success
         */

	public function buildWhereTrie($struct, $key_col, $args, $ctrl=null){
		
		
		$ctrl_default = array(
					'mode'=>'trie',
					'hash_token_vals' => false	    
		);
		
		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);
		
		try {
			$cls = new FOX_queryBuilder_whereMatrix($struct, $key_col, $args, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error building object",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		
		try {
			return $cls->render();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error during render",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		

	}


	/**
         * Builds a WHERE-OR-WHERE construct for an SQL query
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> VAL @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
	 * @param array $args | Array of args arrays
	 *	=> ARR @param int '' | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of the column in the db table this key describes
	 *		=> VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		=> VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param string $caller | Name of the method calling this function.
	 * @param bool $prefix | Prefix to prepend to table names
	 *
         * @return bool/int/array | Exception on failure. Int on count. Array of rows on success.
         */

	public function buildWhereMulti($struct, $args, $caller, $prefix=null){

		$groups_left = count($args) - 1;
		$where = ' AND ';
		$params_list = array();

		if( empty($struct) || empty($args) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty struct or args arrays",
				'data'=>array("struct"=>$struct, "args"=>$args, "caller"=>$caller, "prefix"=>$prefix),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		foreach( $args as $group ){

			try {
				$parse_result = self::buildWhere($struct, $group, $caller, $prefix);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Parse error in WHERE group",
					'data'=>array("group"=>$group, "struct"=>$struct, "args"=>$args, "caller"=>$caller, "prefix"=>$prefix),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$where .= "(" . substr($parse_result["where"],5) . ")";	    // Trim the "AND " off the beginning of the returned string

			if($groups_left > 0){

				$groups_left--;
				$where .= " OR ";
			}

			$params_list = array_merge($params_list, $parse_result['params']);
			
		}
		unset($group);

		return array('where'=>$where, 'params'=>$params_list);

	}



	/**
         * Builds a WHERE construct for an SQL query
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> VAL @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param string $caller | Name of the method calling this function.
	 * @param bool $prefix | Prefix to prepend to table names
	 *
         * @return bool/int/array | Exception on failure. Int on count. Array of rows on success.
         */

	public function buildWhere($struct, $args, $caller, $prefix=null){

		// DESIGN NOTE: Passing the calling method's name as $caller is a lot less
		// expensive than using debug_backtrace(). Also, its nearly impossible to use
		// debug_backtrace() with PHPUnit, since the stack trace is usually 200K+ lines

		$where = '';
		$params_list = array();
		$data_types = array();


		// Trap users supplying invalid compare operators
		// ==============================================

		foreach( $args as $col_params ){

			$valid_ops = array(">=", "<=", ">", "<", "=", "!=", "<>");

			if( array_search($col_params["op"], $valid_ops) === false ){
			    
				$text =  "\nMethod {$caller}() called buildWhere() with an illegal SQL comparison operator. \n";
				$text .= "\nColumn name: '{$col_params["col"]}' \nBad Operator: '{$col_params["op"]} '\n";

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>$text,
					'data'=>array("struct"=>$struct, "args"=>$args, "caller"=>$caller, "prefix"=>$prefix),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			if( $struct["columns"][$col_params["col"]]["format"] == "%r" ){

				$escape = false;
			}
			else {
				$escape = true;
			}
					
			// If the compare is being run on a single value, add a structure like "column_name < %d"
			// to the query, and copy the value to the params list.
			// ======================================================================================
			if( !is_array($col_params["val"]) ){

				$where .= " AND " . $prefix . $col_params["col"] . " " . $col_params["op"] . " " . $struct["columns"][$col_params["col"]]["format"];
				
				$params_list[] = array(	'escape'=>$escape, 
							'val'=>$col_params['val'], 
							'php'=>$struct['columns'][$col_params['col']]['php'],
							'sql'=>$struct['columns'][$col_params['col']]['sql'] 
				);				
			}

			// If the compare is being run on an array of values, add a structure like
			// "column_name IN(%d, %d, %d)" to the query, and copy the values to the params list.
			// ======================================================================================
			else {

				$where .=" AND ";

				// Combine arrays of values into a grouped query. Trap invalid
				// operators for IN() construct.
				// ------------------------------------------------------------

				if( $col_params["op"] == "=" ) {

					$where .= $prefix . $col_params["col"] . " IN(";
				}
				elseif( ($col_params["op"] == "!=") || ($col_params["op"] == "<>") ) {

					$where .= $prefix . $col_params["col"] . " NOT IN(";
				}
				else {

					$text =  "\nMethod {$caller}() called buildWhere() with an illegal SQL comparison operator for a SQL IN() construct.";
					$text .= "\nValid operators for this query type are: '=', '!=', or '<>' .\n";
					$text .= "\nColumn name: '{$col_params["col"]}' \nBad Operator: '{$col_params["op"]} '\n";

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>$text,
						'data'=>array("struct"=>$struct, "args"=>$args, "caller"=>$caller, "prefix"=>$prefix),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}


				// Implode the array into a single string and add values to params list
				// --------------------------------------------------------------------

				$vals_left = count($col_params["val"]) - 1;

				foreach($col_params["val"] as $val){

					$where .= $struct["columns"][$col_params["col"]]["format"];

					if($vals_left != 0){
						$where .= ", ";
						$vals_left--;
					}
					
					$params_list[] = array(	'escape'=>$escape, 
								'val'=>$val, 
								'php'=>$struct['columns'][$col_params['col']]['php'],
								'sql'=>$struct['columns'][$col_params['col']]['sql'] 
					);															
				}
				unset($val);

				$where .= ")";
			}		
			

		} //ENDOF foreach($args as $col_name => $col_params)

		return array('where'=>$where, 'params'=>$params_list);


	}

	/**
         * Builds a SELECT query with a JOIN statement, for processing by $wpdb->prepare().
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
	 *		=> VAL @param string/array $sort | Direction to sort in. "ASC", "DESC", array(val, val, val) where position in array
	 *						   is the desired position in the results set for the row or rows with columm matching val
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> ARR @param bool/array $count | Count columns. Bool TRUE to use COUNT(DISTINCT primary_table.*)
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Column to count
	 *	=> ARR @param bool/array $sum | Sum columns
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Column to sum
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 *
         * @return bool/int/array | Exception on failure, int on count, array of rows on success.
	 */

	public function buildSelectQueryJoin($primary, $join, $columns=null, $ctrl=null){


		// Switch between "operation mode" (pass as class name)
		// and "unit test mode" (pass as array)
		// ====================================================

		if( is_string($primary["class"]) ){

			$primary_struct = call_user_func( array($primary["class"],'_struct') );
		}
		else {
			$primary_struct = $primary["class"];
		}

		// ====================================================

		$ctrl_default = array(
			'page' => 0,
			'per_page' => 0,
			'offset' => false,
			'sort' => false,
			'group' => false,
			'count'=>false,
			'alias_prefix' => 'alias_'
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);

		// Make sure control parameters are properly typecast to help avoid time-consuming debugging problems
		$ctrl['page'] = (int)$ctrl['page'];
		$ctrl['per_page'] = (int)$ctrl['per_page'];
		$ctrl['offset'] = (int)$ctrl['offset'];


		// Add primary SELECT columns
		// ######################################################

		$select_columns = array();
		$from_tables = array();
		$type_cast = array();

		// Passing "null" = "select all columns", passing "false" = "select no columns"

		if($columns === null){
			
			$from_tables[] = $this->base_prefix . $primary_struct["table"];
			
			// If the table doesn't contain any columns which use GIS data types, we can
			// use the more efficient "SELECT *" construct
			
			if( !self::hasGISColumn($primary_struct) ){
			
				$select_columns[] = $this->base_prefix . $primary_struct["table"] . ".*";

				// Add all primary table columns to the typecast array
				foreach( $primary_struct["columns"] as $name => $params){

					// NOTE: MySQL automatically strips the table prefix off the key names in the returned
					// result, so if we request primaryTableName.colName_1, the result back from the SQL
					// server will be colName_1 => value

					$type_cast[$name] = array("php"=>$params["php"], "sql"=>$params["sql"] );
				}	
				unset($name, $params);
				
			}	
			// If the table contains one or more columns which use GIS data type, we have to process
			// them individually so we can wrap the column names with AsText() clauses
			
			else {	
			    
				foreach( $primary_struct["columns"] as $name => $params){

					// NOTE: MySQL automatically strips the table prefix off the key names in the returned
					// result, so if we request primaryTableName.colName_1, the result back from the SQL
					// server will be colName_1 => value

					if( !self::isGISDataType($params['sql']) ){

						$select_columns[] = $this->base_prefix . $primary_struct["table"] . "." . $name;					    
						$type_cast[$name] = array("php"=>$params["php"], "sql"=>$params["sql"] );
					}
					else {					    
						// MySQL will return data from column names wrapped in an AsText() clause 
						// as "AsText(column_name)", with the table prefix stripped as above
					    
						$select_columns[] = "AsText(" . $this->base_prefix . $primary_struct["table"] . "." . $name . ")";
						$type_cast["AsText(".$name.")"] = array("php"=>$params["php"], "sql"=>$params["sql"] );
					}
				}
				unset($name, $params);
			}			

		}
		else {

			// Handle single column name as string

			if( !is_array($columns["col"]) ){

				$temp = array();
				$temp[0] = $columns["col"];
				$columns["col"] = $temp;
			}

			foreach($primary_struct["columns"] as $name => $params){

				// We cannot use the array_keys() column selection algorithm that we use in the other
				// query builders here because we have to prefix the column names with their table name, and
				// because we add aliased column names from the joined tables in the next stage

				if ($columns["mode"] == "include"){

					if( array_search($name, $columns["col"] ) !== false) {
						
						// NOTE: MySQL automatically strips the table prefix off the key names in the returned
						// result, so if we request primaryTableName.colName_1, the result back from the SQL
						// server will be colName_1 => value

						if( !self::isGISDataType($params['sql']) ){

							$select_columns[] = $this->base_prefix . $primary_struct["table"] . "." . $name;					    
							$type_cast[$name] = array("php"=>$params["php"], "sql"=>$params["sql"] );
						}
						else {					    
							// MySQL will return data from column names wrapped in an AsText() clause 
							// as "AsText(column_name)", with the table prefix stripped as above

							$select_columns[] = "AsText(" . $this->base_prefix . $primary_struct["table"] . "." . $name . ")";
							$type_cast["AsText(".$name.")"] = array("php"=>$params["php"], "sql"=>$params["sql"] );
						}						
						
					}
				}
				elseif ($columns["mode"] == "exclude"){

					if( array_search($name, $columns["col"] ) === false) {
						
						// NOTE: MySQL automatically strips the table prefix off the key names in the returned
						// result, so if we request primaryTableName.colName_1, the result back from the SQL
						// server will be colName_1 => value

						if( !self::isGISDataType($params['sql']) ){

							$select_columns[] = $this->base_prefix . $primary_struct["table"] . "." . $name;					    
							$type_cast[$name] = array("php"=>$params["php"], "sql"=>$params["sql"] );
						}
						else {					    
							// MySQL will return data from column names wrapped in an AsText() clause 
							// as "AsText(column_name)", with the table prefix stripped as above

							$select_columns[] = "AsText(" . $this->base_prefix . $primary_struct["table"] . "." . $name . ")";
							$type_cast["AsText(".$name.")"] = array("php"=>$params["php"], "sql"=>$params["sql"] );
						}						
					}
				}

			} unset($name); unset($params);

			$from_tables[] = $this->base_prefix . $primary_struct["table"];
		}


		// Add primary args to WHERE statement
		// ######################################################

		$params_list = array();
		$where_string = "1 = 1";   // We need to pre-load it with a value in case the user adds zero 'where' conditions

		// Primary table args. The "if" clause handles the case where the user passes no args because
		// they do not want to constrain the query using a column in the primary table.

		if($primary["args"]){

			$prefix = $this->base_prefix . $primary_struct["table"] . ".";

			try {
				$result = self::buildWhere( $primary_struct, $primary["args"], __FUNCTION__, $prefix);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error building primary args WHERE construct",
					'data'=>array("primary_struct"=>$primary_struct, "primary_args"=>$primary["args"],
						      "function_name"=>__FUNCTION__, "prefix"=>$prefix),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$where_string .= $result['where'];
			$params_list = array_merge($params_list, $result['params']);
			unset($result);

		}


		// Build LEFT JOIN statements
		// ######################################################

		foreach( $join as $join_obj ){


			// Switch between "operation mode" (pass as class name)
			// and "unit test mode" (pass as array)
			// ====================================================

			if( is_string($join_obj["class"]) ){

				$join_struct = call_user_func(array($join_obj["class"],'_struct'));
			}
			else {
				$join_struct = $join_obj["class"];
			}

			// ====================================================


			$join_string .= " INNER JOIN " . $this->base_prefix  . $join_struct["table"] . " AS " . $ctrl['alias_prefix'] . $join_struct["table"] . " ON (" .
					$this->base_prefix . $primary_struct["table"] . "." . $join_obj["on"]["pri"] .
					" " . $join_obj["on"]["op"] . " " .
					$ctrl['alias_prefix'] . $join_struct["table"] . "." . $join_obj["on"]["sec"] . ")";

			// Joined table args. The "if" clause handles the case where the user passes no args because
			// they do not want to constrain the query using a column in the joined table.

			if($join_obj["args"]){

				// All joined tables have to be aliased [ real_table_name AS alias_table_name ] to
				// make the INNER JOIN statements comply with SQL syntax rules

				$prefix = $ctrl['alias_prefix'] . $join_struct["table"] . ".";

				try {
					$result = self::buildWhere( $join_struct, $join_obj["args"], __FUNCTION__, $prefix);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error building joined table WHERE construct",
						'data'=>array("primary_struct"=>$primary_struct, "primary_args"=>$primary["args"],
							      "function_name"=>__FUNCTION__, "prefix"=>$prefix),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}

				$where_string .= $result['where'];
				$params_list = array_merge($params_list, $result['params']);
				unset($result);
			}

			// Add join SELECT columns
			// ==================================================

			if( $join_obj["col"] != null ){

				// Handle single column name as string

				if( !is_array($join_obj["col"]) ){

					$temp = array();
					$temp[0] = $join_obj["col"];
					$join_obj["col"] = $temp;
				}

				foreach( $join_obj["col"] as $name ){

					// Because we are using aliases for the joined tables, we have to use the alias names in the
					// select statement. So if we have "INNER JOIN example_table AS alias_example_table", we have to
					// use "SELECT alias_example_table.column_name" instead of "SELECT example_table.column_name"
					
					if( !self::isGISDataType($join_struct["columns"][$name]['sql']) ){

						$select_columns[] = $ctrl['alias_prefix'] . $join_struct["table"] . "." . $name;
						
						$type_cast[$ctrl['alias_prefix'].$join_struct["table"].".".$name] = array(
						    
							"php"=>$join_struct["columns"][$name]["php"],
							"sql"=>$join_struct["columns"][$name]["sql"] 
						);
					}
					else {					    					   
						// Typical output: "AsText(alias_joinTable.fooColumn)"
					    
						$select_columns[] = "AsText(" . $ctrl['alias_prefix'] . $join_struct["table"] . "." . $name . ")";
						
						$type_cast["AsText(".$ctrl['alias_prefix'].$join_struct["table"].".".$name.")"] = array(
						    
							"php"=>$join_struct["columns"][$name]["php"],
							"sql"=>$join_struct["columns"][$name]["sql"] 
						);						
					}					
				}

			}

		    unset($join_struct);

		}   
		unset($join_obj); 

		
		// Build SELECT string
		// ######################################################

		$columns_left = count($select_columns) - 1;

		// Add a "DISTINCT" prefix to the columns string to remove any
		// duplicate rows created by the INNER JOIN

		if($columns_left >= 0){
			$select_string .= "DISTINCT ";
		}

		foreach( $select_columns as $name ){

			$select_string .= $name;

			if($columns_left != 0){
				$select_string .= ", ";
				$columns_left--;
			}

		} unset($name);


		// Build FROM string
		// ######################################################

		$tables_left = count($from_tables) - 1;

		foreach( $from_tables as $name ){

			$from_string .= $name;

			if($tables_left != 0){
				$from_string .= ", ";
				$tables_left--;
			}

		} unset($name);


		// Build COUNT, ORDER BY, GROUP BY, and LIMIT
		// ######################################################

		if($ctrl['count'] === true){

			// Simple counts can bypass the rest of the query builder

			$query = "SELECT COUNT(DISTINCT " . $this->base_prefix . $primary_struct["table"] . ".*)" .
				  " FROM " . $this->base_prefix . $primary_struct["table"] . $join_string . " WHERE " . $where_string;

		}
		else {

			// Build $count_string
			// ==========================================
			if($ctrl['count']){

				if( is_array($ctrl['count']) ){

					$columns_left = count($ctrl['count']) - 1;

					foreach( $ctrl['count'] as $key ){

						// Switch between "operation mode" (pass as class name)
						// and "unit test mode" (pass as array)
						// ====================================================

						if( is_string($key["class"]) ) {

							$key_struct = call_user_func( array($key["class"],'_struct') );
						}
						else {
							$key_struct = $key["class"];
						}

						// ====================================================

						$count_string .= "COUNT(DISTINCT " . $this->base_prefix . $key_struct["table"] . "." . $key["col"] . ")";

						if($columns_left != 0){
							$count_string .= ", ";
							$columns_left--;
						}

					} unset($name);

				}

				// If other columns are being selected in the query, or if columns are being
				// summed in our query, we have to add a comma and space after our list of count columns

				if( ($select_string != "") || $ctrl['sum'] != "" ){

					$count_string .= ", ";
				}

			}


			// Build $sum_string
			// ==========================================
			if($ctrl['sum']){

				if( is_array($ctrl['sum']) ){

					$columns_left = count($ctrl['sum']) - 1;

					foreach( $ctrl['sum'] as $key ){

						// Switch between "operation mode" (pass as class name)
						// and "unit test mode" (pass as array)
						// ====================================================

						if( is_string($key["class"]) ) {

							$key_struct = call_user_func( array($key["class"],'_struct') );
						}
						else {
							$key_struct = $key["class"];
						}

						// ====================================================

						$sum_string .= "SUM(DISTINCT " . $this->base_prefix . $key_struct["table"] . "." . $key["col"] . ")";

						if($columns_left != 0){
							$count_string .= ", ";
							$columns_left--;
						}

					} unset($name);

				}

				// If other columns are being selected in the query, we have to
				// add a comma and space after our list of count columns

				if($select_string != ""){

					$sum_string .= ", ";
				}

			}


			// Build $order_string
			// ==========================================

			// Note that in a mySQL INNER JOIN query, it is theoretically possible to sort the data set by a column that isn't in the
			// returned data set (but which is in the primary or one of the joined tables) ...but only when not using SELECT DISTINCT.
			// @see http://dev.mysql.com/doc/refman/5.5/en/select.html

			if($ctrl['sort']){

				$order_string = " ORDER BY ";

				$keys_left = count($ctrl['sort']) - 1;

				foreach($ctrl['sort'] as $key){

					// Switch between "operation mode" (pass as class name)
					// and "unit test mode" (pass as array)
					// ====================================================

					if( is_string($key["class"]) ) {

						$key_struct = call_user_func( array($key["class"],'_struct') );
					}
					else {
						$key_struct = $key["class"];
					}

					// ====================================================


					if( is_array($key["sort"]) ){

						// Handle arbitrary ordering using MySQL's FIND_IN_SET() function
						$order_string .= "FIND_IN_SET(" . $this->base_prefix . $key_struct["table"] . ".";
						$order_string .= $key["col"] . ", '" . implode(",", $key["sort"]) . "')";

					}
					else {
						$order_string .= $this->base_prefix . $key_struct["table"] . "." . $key["col"] . " " . $key["sort"];
					}


					if($keys_left != 0){
						$order_string .= ", ";
						$keys_left--;
					}

					unset($key_struct);
					
				} 
				unset($key); 

			}


			// Build $group_string
			// ==========================================

			if($ctrl['group']){

				$group_string = " GROUP BY ";

				$keys_left = count($ctrl['group']) - 1;

				foreach($ctrl['group'] as $key){

					// Switch between "operation mode" (pass as class name)
					// and "unit test mode" (pass as array)
					// ====================================================

					if( is_string($key["class"]) ) {

						$key_struct = call_user_func( array($key["class"],'_struct') );
					}
					else {
						$key_struct = $key["class"];
					}

					// ====================================================

					$group_string .= $this->base_prefix . $key_struct["table"] . "." . $key["col"] . " " . $key["sort"];

					if($keys_left != 0){
						$group_string .= ", ";
						$keys_left--;
					}
					
					unset($key_struct);

				} 
				unset($key); 

			}


			// Handle $offset and limits
			// ==========================================
			// The format for a LIMIT construct is: "LIMIT [A: offset from zero], [B: max records to return]". Because it is logically
			// impossible to offset a window that is infinitely wide, we have set the size of the "window" [B] in order to "offset" [B]
			// it. This is why we can only use the LIMIT construct if $per_page has been set.

			if( $ctrl['per_page'] ){

				if($ctrl['offset']){

					$limits = " LIMIT %d, %d";
					$params_list[] = array('escape'=>true, 'val'=>$ctrl['offset']);
					$params_list[] = array('escape'=>true, 'val'=>$ctrl['per_page']);
				}
				else {
					$limits = " LIMIT %d, %d";
					$params_list[] = array('escape'=>true, 'val'=>($ctrl['page'] - 1) * $ctrl['per_page']);
					$params_list[] = array('escape'=>true, 'val'=>$ctrl['per_page']);
				}

			}

			// Build the query
			// ######################################################

			$query = "SELECT " . $count_string . $sum_string . $select_string .
				 " FROM " . $from_string . $join_string .
				 " WHERE " . $where_string . $group_string . $order_string . $limits;

		}


		// Merge all return data into an array
		// ###############################################################

		$result = array(		    
			'query'=>$query,
			'params'=>$params_list,
			'types'=>$type_cast
		);

		return $result;

	}
	
	
	/**
         * Builds a SELECT query with a LEFT JOIN statement, for processing by $wpdb->prepare().
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $primary | Primary table class name and args
	 *	=> VAL @param string/array $class | Name of class that owns the primary table (as string), or the class's $struct array (as array)
	 *	=> VAL @param string $alias | Alias for the primary table
	 *	=> ARR @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of the column in the db table this key describes
	 *		=> VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		=> VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param array $join | Joined tables class names and args
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string/array $class | Name of class that owns the joined table (as string), or the class's $struct array (as array)
	  *	    => VAL @param string $alias | Alias for the joined table that is used when outputting columns
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
	 * @param object/array $columns | Either array with specific columns to include/exclue or anything else to return all columns.
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $table_class | Class of table that the column is from.
	 *	    => VAL @param string $table_alias | Alias table that the column is from. Used instead of table_class not required
	 *	    => VAL @param string $col_name | Name of the column
	 *	    => VAL @param string $col_alias | Alias of the column
	 *	    => VAL @param bool $count | True to count this column
	 *	    => VAL @param bool/array $sum | True sum this column, array sums multiple columns
	 *	        => ARR @param int '' | Array index
	 *			=> VAL @param string $table_alias | Table alias of table that the column is from. if table alias is not set the default is t(number)
	 *			=> VAL @param string $col_name | Name of the column
	 *			=> VAL @param string $col_alias | Column alias used instead of table alias and col_name
	 *			=> VAL @param bool $count | True to count this column
	 *			=> VAL @param string $op | Operation to perform on column value +,- or *(muliple) default +
	 *
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
	 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
	 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
	 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to sort by
	 *		=> VAL @param string $col_alias | Column alias used instead of class and col. not required
	 *		=> VAL @param string/array $sort | Direction to sort in. "ASC", "DESC", array(val, val, val) where position in array
	 *						   is the desired position in the results set for the row or rows with columm matching val
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $class | Class name that owns the table
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $col_alias | Column alias used instead of class and col. not required
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 * 
         * @return int/array | Exception on failure, int on count, array of rows on success.
	 */

	public function buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl=null){


		// Switch between "operation mode" (pass as class name)
		// and "unit test mode" (pass as array)
		// ====================================================

		if( is_string($primary["class"]) ){

			$primary_struct = call_user_func( array($primary["class"],'_struct') );
		}
		else {
			$primary_struct = $primary["class"];
		}

		// ====================================================

		$ctrl_default = array(
			'page' => 0,
			'per_page' => 0,
			'offset' => false,
			'sort' => false,
			'group' => false
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);

		// Make sure control parameters are properly typecast to help avoid time-consuming debugging problems
		$ctrl['page'] = (int)$ctrl['page'];
		$ctrl['per_page'] = (int)$ctrl['per_page'];
		$ctrl['offset'] = (int)$ctrl['offset'];


		// Add primary SELECT columns
		// ######################################################

		$select_columns = array();
		$type_cast = array();

		// Check if primary table alias has been set and is valid if not use default 't1'
		
		$alias = array();

		if( is_string($primary["alias"]) ) {

			$alias["tables"][$primary_struct["table"]] = $primary["alias"];
		}
		else {

			$alias["tables"][$primary_struct["table"]] = "t1";
		}

		// Create an array of all columns from each table used if all columns selected or
		// if only col_name and not table class provided as a column
		
		$avail_columns = array();
		$avail_columns[$alias["tables"][$primary_struct["table"]]] = $primary_struct["columns"];

		// Build from string
		$from_string = $this->base_prefix . $primary_struct["table"]." AS ".$alias["tables"][$primary_struct["table"]];

		// Add primary args to WHERE statement
		// ######################################################

		$params_list = array();
		$where_string = "1 = 1";   // We need to pre-load it with a value in case the user adds zero 'where' conditions

		// Primary table args. The "if" clause handles the case where the user passes no args because
		// they do not want to constrain the query using a column in the primary table.

		if($primary["args"]){

			try {
				$result = self::buildWhere( $primary_struct, $primary["args"], __FUNCTION__, 
							    $alias["tables"][$primary_struct["table"]]."." );
			}
			catch (FOX_Exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error building primary args WHERE construct",
					'data'=>array("primary_struct"=>$primary_struct, "primary_args"=>$primary["args"],
						      "function_name"=>__FUNCTION__, "prefix"=>$alias["tables"][$primary_struct["table"]]),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$where_string .= $result['where'];
			$params_list = array_merge($params_list, $result['params']);
			unset($result);

		}

		// Build LEFT JOIN statements
		// ######################################################

		$join_num = 2;
		
		foreach( $join as $join_obj ){

			// Switch between "operation mode" (pass as class name)
			// and "unit test mode" (pass as array)
			// ====================================================

			if( is_string($join_obj["class"]) ){

				$join_struct = call_user_func(array($join_obj["class"],'_struct'));
			}
			else {
				$join_struct = $join_obj["class"];
			}

			// ====================================================

			// Check if table alias has been set and is valid if not use default 't' plus table number 2,3,4 etc
			if( is_string($join_obj["alias"]) ) {

				$alias["tables"][$join_struct["table"]] = $join_obj["alias"];
			}
			else {

				$alias["tables"][$join_struct["table"]] = "t".$join_num;
			}
			
			$join_num++;
			
			// Create an array of all columns from each table used if all columns selected
			// or if only col_name and not table class provided as a column
			$avail_columns[$alias["tables"][$join_struct["table"]]] = $join_struct["columns"];

			

			$join_string .= " LEFT JOIN " . $this->base_prefix  . $join_struct["table"] ." AS ".$alias["tables"][$join_struct["table"]].  " ON (" .
					$alias["tables"][$primary_struct["table"]]."." . $join_obj["on"]["pri"] .
					" " . $join_obj["on"]["op"] . " " .
					$alias["tables"][$join_struct["table"]]. "." . $join_obj["on"]["sec"] . ")";

			// Joined table args. The "if" clause handles the case where the user passes no args because
			// they do not want to constrain the query using a column in the joined table.

			if( $join_obj["args"] ){

				// All joined tables have to be aliased [ real_table_name AS alias_table_name ] to
				// make the LEFT JOIN statements comply with SQL syntax rules

				try {
					$result = self::buildWhere( $join_struct, $join_obj["args"], __FUNCTION__, 
								    $alias["tables"][$join_struct["table"]].".");
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error building joined table WHERE construct",
						'data'=>array("primary_struct"=>$primary_struct, "primary_args"=>$primary["args"],
							      "function_name"=>__FUNCTION__, "prefix"=>$alias["tables"][$join_struct["table"]]),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}

				$where_string .= $result['where'];
				$params_list = array_merge($params_list, $result['params']);
				unset($result);

			}

			unset($join_struct);
			
		}   
		unset($join_obj); 

		
		// Build SELECT string
		// ######################################################

		$valid_sum_ops = array("+", "-", "*");
		
		// If columns is array process else return all columns
		
		if( is_array($columns)) {

			foreach ($columns as $col) {

				$select_column ="";
				$alias_string = "";
				$add_closing_brackets = false;

				// If sum array is used don't process top level column data apart from col_alias
				$process_top_level_column_naming =true;

				if( is_string($col)){
				    $col = array("col_name"=>$col);
				}

				// If table class used get table_alias
				if(isset($col["table_class"])) {

					if( is_string($col["table_class"]) ){

						$col["table_alias"] = $alias["tables"][call_user_func(array($col["table_class"],'_struct'))];
					}
					else {

					    $col["table_alias"] = $alias["tables"][$col["table_class"]["table"]];
					}
				}

				// If count set add Count to start of this select column
				if($col["count"] === true) {

					$select_column ="COUNT( ";
					$add_closing_brackets = true;
				} 
				// If sum set add SUM to start of this select column
				elseif( isset($col["sum"])) {

					$select_column = "SUM( ";
					$add_closing_brackets = true;

					
					if( is_array($col["sum"])) {
					    
						// If multple sum columns don't process top level column data
						$process_top_level_column_naming =false;

						$first_sum = true;

						foreach($col["sum"] as $sum_col) {

							// If op not set use add
							if( !isset($sum_col["op"])) {

								$sum_col["op"] = "+";
							}

							// If op is valid add it to select column
							if( in_array($sum_col["op"], $valid_sum_ops)) {

								    // If first sum column and op is add don't need to add the op
								    if($first_sum) {

									    if($sum_col["op"] != "+") {

										    $select_column .=" ".$sum_col["op"]." ";
									    }
								    } 
								    else {
									    $select_column .=" ".$sum_col["op"]." ";
								    }
								    
								    $first_sum = false;
								    
								    // If table_class used instead of table_alias get table alias
								    if( isset($sum_col["table_class"])) {

									    if( is_string($sum_col["table_class"]) ){

										    $sum_col["table_alias"] = $alias["tables"][call_user_func(array($sum_col["table_class"],'_struct'))];
									    } 
									    else {

										    $sum_col["table_alias"] = $alias["tables"][$sum_col["table_class"]["table"]];
									    }
								    }

								    $sum_close_brackets = false;

								    if($sum_col["count"]==true) {

									    $select_column .= "COUNT( ";
									    $sum_close_brackets = true;
								    }

								    // If col_alias is used don't need to use tables_alias and col_name
								    if( isset($sum_col["col_alias"])) {

									    $select_column .=$sum_col["col_alias"];
								    } 
								    else {
									    if( isset($sum_col["table_alias"])) {

										    if( isset($sum_col["col_name"]))     {

											    $select_column .=$sum_col["table_alias"].".".$sum_col["col_name"];
										    } 
										    elseif($sum_col["count"]==true) {

											    $select_column .=$sum_col["table_alias"].".*";
										    }
									    }
								    }
								    if($sum_close_brackets) {

									    $select_column .= " )";
								    }
								    
							}
						}

					}

				}
				// If column not multiple sum column
				if($process_top_level_column_naming ) {

					if( isset($col["table_alias"]) ) {
					    
						// Cannot only use table_alias in column definition if no col_name and not using count
						if( isset($col["col_name"]) || isset($col["count"]) ) {

							$select_column .= $col["table_alias"].".";
						}
					}

					if( isset($col["col_name"])) {

						$select_column .= $col["col_name"];

						if( isset($col["table_alias"])) {
						    
							// This is set in case there is no column alias
							$alias_string .= $col["table_alias"].$col["col_name"];
						}
					} 
					elseif($col["count"] === true) {

						$select_column .= "*";
					}

				}

				if($add_closing_brackets) {

					$select_column .= " )";

				}

				if( isset($col["col_alias"])) {

					$alias_string = $col["col_alias"];

				}

				// If table_alias not set and not count or sum search available columns for it
				if( !isset($col["count"]) && !isset($col["sum"]) && !isset($col["table_alias"])) {

					foreach ($avail_columns as $table_alias=>$table_columns) {

						if( in_array($col["col_name"], array_keys($table_columns))) {

							$col["table_alias"] = $table_alias;
							break;

						}
					}

				}

				if( strlen($alias_string)>0 && strlen($select_column)>0) {

					$select_column .= " AS ". $alias_string;
					$type_name = $alias_string;

				} 
				else {

					$type_name = $col["col_name"];
				}
				
				// Add type casting
				if( strlen($select_column)>0 && !isset($col["count"]) && !isset($col["sum"])  ) {

					$type_cast[$type_name] = array(
						"php"=>$avail_columns[$col["table_alias"]][$col["col_name"]]["php"],
						"sql"=>$avail_columns[$col["table_alias"]][$col["col_name"]]["sql"]
					);
					
				} 
				elseif( strlen($select_column)>0) {

					$type_cast[$alias_string] = array("php"=>"int", "sql"=>"bigint");
				}

				if( strlen($select_column)>0) {
					$select_columns[] = $select_column;
				}

			}

		} 
		else {

			foreach($avail_columns as $key=>$value) {

				foreach(array_keys($value) as $col) {

					$select_columns[] = $key.".".$col." AS ". $key.$col;
					$type_cast[$key.$col] = array("php"=>$value[$col]["php"], "sql"=>$value[$col]["sql"]);

				}
			}

		}

		$first_column = true;

		foreach( $select_columns as $name ){

			if(!$first_column) {

				$select_string .= ", ";
			}

			$select_string .= $name;
			$first_column = false;

		} unset($name);



		// Build $order_string
		// ==========================================

		// Note that in a mySQL INNER JOIN query, it is theoretically possible to sort the data set by a column that isn't in the
		// returned data set (but which is in the primary or one of the joined tables) ...but only when not using SELECT DISTINCT.
		// @see http://dev.mysql.com/doc/refman/5.5/en/select.html

		if($ctrl['sort']){

			$order_string = " ORDER BY ";

			$first_sort =true;
			
			foreach( $ctrl['sort'] as $key){

				// Switch between "operation mode" (pass as class name)
				// and "unit test mode" (pass as array)
				// ====================================================

				if( is_string($key["class"]) ) {

					$key_struct = call_user_func( array($key["class"],'_struct') );
				}
				else {
					$key_struct = $key["class"];
				}


				// ====================================================


				if(!$first_sort){

					$order_string .= ", ";
				}
				
				$first_sort = false;
				
				if( is_array($key["sort"]) ){

					// Handle arbitrary ordering using MySQL's FIND_IN_SET() function
					$order_string .= "FIND_IN_SET(" . $alias["tables"][$key_struct["table"]] . ".";
					$order_string .= $key["col"] . ", '" . implode(",", $key["sort"]) . "')";

				} 
				elseif( isset($key["col_alias"])) {

					$order_string .= $key["col_alias"]. " " . $key["sort"];;

				}
				else {
					$order_string .= $alias["tables"][$key_struct["table"]] . "." . $key["col"] . " " . $key["sort"];
				}

				unset($key_struct);
				
			} 
			unset($key); 

		}


		// Build $group_string
		// ==========================================

		if($ctrl['group']){

			$group_string = " GROUP BY ";

			$first_group = true;

			foreach( $ctrl['group'] as $key ){

				// Switch between "operation mode" (pass as class name)
				// and "unit test mode" (pass as array)
				// ====================================================

				if( is_string($key["class"]) ) {

					$key_struct = call_user_func( array($key["class"],'_struct') );
				}
				else {
					$key_struct = $key["class"];
				}

				// ====================================================

				if(!$first_group) {

					$group_string .= ", ";
				}

				if( isset($key["col_alias"])) {

					$group_string .= $key["col_alias"] . " " . $key["sort"];

				} 
				else {

					$group_string .= $alias["tables"][$key_struct["table"]]. "." . $key["col"] . " " . $key["sort"];

				}
				$first_group = false;

				unset($key_struct);
				
			} 
			unset($key); 

		}


		// Handle $offset and limits
		// ==========================================
		// The format for a LIMIT construct is: "LIMIT [A: offset from zero], [B: max records to return]". Because it is logically
		// impossible to offset a window that is infinitely wide, we have set the size of the "window" [B] in order to "offset" [B]
		// it. This is why we can only use the LIMIT construct if $per_page has been set.

		if( $ctrl['per_page'] ){

			if($ctrl['offset']){

				$limits = " LIMIT %d, %d";
				$params_list[] = array('escape'=>true, 'val'=>$ctrl['offset']);
				$params_list[] = array('escape'=>true, 'val'=>$ctrl['per_page']);
			}
			else {
				$limits = " LIMIT %d, %d";
				//$params_list[] = absint(($ctrl['page'] - 1)) * $ctrl['per_page'];
				$params_list[] = array('escape'=>true, 'val'=>($ctrl['page'] - 1) * $ctrl['per_page']);
				$params_list[] = array('escape'=>true, 'val'=>$ctrl['per_page']);
			}

		}

		// Build the query
		// ######################################################

		$query = "SELECT " . $count_string . $sum_string . $select_string .
			 " FROM " . $from_string . $join_string .
			 " WHERE " . $where_string . $group_string . $order_string . $limits;


		// Merge all return data into an array
		// ###############################################################

		$result = array(		    
			'query'=>$query,
			'params'=>$params_list,
			'types'=>$type_cast
		);

		return $result;

	}


	/**
         * Builds a select query for processing by $wpdb->prepare().
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
	 *		=> VAL @param string/array $sort | Direction to sort in. "ASC", "DESC", or array(val, val, val) where position in array
	 *						   is the desired position in the results set for the row or rows with columm matching val
	 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
	 *	    => ARR @param int '' | Array index
	 *		=> VAL @param string $col | Name of column to apply GROUP to
	 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
	 *	=> VAL @param bool/string/array $count | Return a count of db rows. Bool true to use COUNT(*). Single column as string. Multiple columns as array.
	 *	=> VAL @param bool/string/array $sum | Return sum of db rows. Single column as string. Multiple columns as array.
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 *	=> VAL @param string $args_format | "default" to use standard format, "multi", or "matrix"
	 * 
         * @return bool/array | Exception on failure. Int on count. Array of rows on success.
         */

	public function buildSelectQuery($struct, $args, $columns=null, $ctrl=null){


		// Switch between "operation mode" (pass as class name)
		// and "unit test mode" (pass as array)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================

		$columns_list = array_keys($struct["columns"]);

		$ctrl_default = array(
			'page' => 0,
			'per_page' => 0,
			'offset' => false,
			'sort' => false,
			'group' => false,
			'count' => false,
			'args_format' => 'default'
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);

		// Make sure control parameters are properly typecast to help avoid time-consuming debugging problems
		$ctrl['page'] = (int)$ctrl['page'];
		$ctrl['per_page'] = (int)$ctrl['per_page'];
		$ctrl['offset'] = (int)$ctrl['offset'];


		// Add SELECT columns
		// ######################################################

		$select_columns = array();
		$type_cast = array();

		// Passing "null" = "select all columns", passing "false" = "select no columns"

		if($columns === null){

			// If the table doesn't contain any columns which use GIS data types, we can
			// use the more efficient "SELECT *" construct
		    
			if( !self::hasGISColumn($struct) ){
			
				$select_string = "*";

				// Add all primary table columns to the typecast array

				$column_names = array_keys($struct["columns"]);

				foreach( $column_names as $name ){

					$type_cast[$name] = array("php"=>$struct["columns"][$name]["php"], "sql"=>$struct["columns"][$name]["sql"] );
				}
				unset($name);			    
			}
			
			// If the table contains one or more columns which use GIS data type, we have to process
			// them individually so we can wrap the column names with AsText() clauses
			
			else {			    
				$select_columns = $columns_list;
			}

		}
		elseif($columns) {

			if( !is_array($columns["col"]) ){   // Handle single column name as string

				$temp = array();
				$temp[0] = $columns["col"];
				$columns["col"] = $temp;
			}

			if ($columns["mode"] == "include"){

				$select_columns = array_intersect($columns_list, $columns["col"]);
			}
			elseif ($columns["mode"] == "exclude"){

				$select_columns = array_diff($columns_list, $columns["col"]);
			}
		}

		// Assemble select string (if not selecting * )
		// =============================================

		$columns_left = count($select_columns) - 1;

		foreach( $select_columns as $name ){

		    
			if( ($struct["columns"][$name]["sql"] == "point") || ($struct["columns"][$name]["sql"] == "polygon") ){
			    
				$select_string .= "AsText(" . $name . ")";
			}
			else {
				$select_string .= $name;			    			    
			}											

			if($columns_left != 0){
				$select_string .= ", ";
				$columns_left--;
			}

			$type_cast[$name] = array("php"=>$struct["columns"][$name]["php"], "sql"=>$struct["columns"][$name]["sql"] );
			
		} 
		unset($name);


		// Build WHERE statement
		// ######################################################

		$params_list = array();
		$where = "1 = 1";   // We need to pre-load it with a value in case the user adds zero 'where' conditions

		// Primary table args. The "if" clause handles the case where the user passes no args because
		// they do not want to constrain the query using a column in the primary table.
		
		if($args){

			try {
			    
				switch($ctrl['args_format']){

					case "multi" : {

						$result = self::buildWhereMulti($struct, $args, __FUNCTION__, $prefix=null);

					} break;

					case "matrix" : {

						$result = self::buildWhereMatrix($struct, $args["key_col"], $args["args"], $matrix_ctrl=null);

					} break;
				    
				    	case "trie" : {

						$result = self::buildWhereTrie($struct, $args["key_col"], $args["args"], $trie_ctrl=null);

					} break;
				    
					default : {

						$result = self::buildWhere($struct, $args, __FUNCTION__, $prefix=null);

					} break;

				}
				
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error in WHERE clause generator",
					'data'=>array("struct"=>$struct, "args"=>$args, "function_name"=>__FUNCTION__),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$where .= $result['where'];
			$params_list = array_merge($params_list, $result['params']);
			
			unset($result);
			
		}



		// Build COUNT, SUM, ORDER BY, GROUP BY, and LIMIT
		// ######################################################

		if( $ctrl['count'] === true ){

				// Simple counts can bypass the rest of the query builder
				$query = "SELECT COUNT(*) FROM " . $this->base_prefix . $struct["table"] . " WHERE " . $where;
		}
		else {


			// Build $count_string
			// ==========================================
			if($ctrl['count']){

				if( is_array($ctrl['count']) ){

					$columns_left = count($ctrl['count']) - 1;

					foreach( $ctrl['count'] as $name ){

						$count_string .= "COUNT(" . $name . ")";

						if($columns_left != 0){
							$count_string .= ", ";
							$columns_left--;
						}
					} 
					unset($name);

				}
				elseif( is_string($ctrl['count']) ){

					$count_string = "COUNT(" . $ctrl['count'] . ")";
				}

				// If other columns are being selected in the query, or if columns are being
				// summed in our query, we have to add a comma and space after our list of count columns

				if( ($select_string != "") || ($ctrl['sum'] != "") ){

					$count_string .= ", ";
				}

			}


			// Build $sum_string
			// ==========================================
			if($ctrl['sum']){

				if( is_array($ctrl['sum']) ){

					$columns_left = count($ctrl['sum']) - 1;

					foreach( $ctrl['sum'] as $name ){

						$sum_string .= "SUM(" . $name . ")";

						if($columns_left != 0){
						    
							$sum_string .= ", ";
							$columns_left--;
						}
					} 
					unset($name);

				}
				elseif( is_string($ctrl['sum']) ){

					$sum_string = "COUNT(" . $ctrl['sum'] . ")";
				}

				// If other columns are being selected in the query, we have to add a comma and space
				// after our list of sum columns

				if($select_string != ""){

					$count_string .= ", ";
				}

			}

			// Build $order_string
			// ==========================================
			
			if($ctrl['sort']){

				$order_string = " ORDER BY ";

				$keys_left = count($ctrl['sort']) - 1;

				foreach( $ctrl['sort'] as $key ){

					if( is_array($key["sort"]) ){
					    
						// Handle arbitrary ordering using MySQL's FIND_IN_SET() function
						$order_string .= "FIND_IN_SET(" . $key["col"] . ", '" . implode(",", $key["sort"]) . "')";

					}
					else {
						$order_string .= $key["col"] . " " . $key["sort"];
					}

					if($keys_left != 0){
					    
						$order_string .= ", ";
						$keys_left--;
					}
				} 
				unset($key);

			}


			// Build $group_string
			// ==========================================
			if($ctrl['group']){

				$group_string = " GROUP BY ";

				$keys_left = count($ctrl['group']) - 1;

				foreach( $ctrl['group'] as $key ){

					$group_string .= $key["col"] . " " . $key["sort"];

					if($keys_left != 0){
					    
						$group_string .= ", ";
						$keys_left--;
					}
				} 
				unset($key);

			}

			// Handle $offset and limits
			// ==========================================
			// The format for a LIMIT construct is: "LIMIT [A: offset from zero], [B: max records to return]". Because it is logically
			// impossible to offset a windows that is infinitely wide, we have set the size of the "window" [B] in order to "offset" [B]
			// it. This is why we can only use the LIMIT construct if $per_page has been set.
			
			if($ctrl['per_page']){

				if($ctrl['offset']){

					$limits = " LIMIT %d, %d";
					
					$params_list[] = array(
								'escape'=>true, 
								'val'=>$ctrl['offset'],
								'php'=>'int',
								'sql'=>'int' 					    
					);
					
					$params_list[] = array(
								'escape'=>true, 
								'val'=>$ctrl['per_page'],
								'php'=>'int',
								'sql'=>'int' 						    
					);
				}
				else {
					$limits = " LIMIT %d, %d";
					
					$params_list[] = array(
								'escape'=>true, 
								'val'=>($ctrl['page'] - 1) * $ctrl['per_page'],
								'php'=>'int',
								'sql'=>'int' 						    
					);
					
					$params_list[] = array(
								'escape'=>true, 
								'val'=>$ctrl['per_page'],
								'php'=>'int',
								'sql'=>'int' 						    
					);
				}
			}

			// Build the query
			// ######################################################

			$query = "SELECT " . $count_string . $sum_string . $select_string . " FROM " . $this->base_prefix . $struct["table"] . " WHERE " . $where . $group_string . $order_string . $limits;

		}


		// Merge all return data into an array
		// ###############################################################

		$result = array(		    
			'query'=>$query,
			'params'=>$params_list,
			'types'=>$type_cast
		);		

		return $result;
	}


	/**
         * Builds a single-column keyed select query for processing by $wpdb->prepare().
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
	 *	=> VAL @param bool/string/array $count | Return a count of db rows. Bool true to use primary key. Single column as string. Multiple columns as array..
	 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
	 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
	 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
	 *	=> VAL @param string $args_format | "default" to use standard format, "multi", or "matrix"
	 * 
         * @return bool/array | Exception on failure. Query array on success.
         */

	public function buildSelectQueryCol($struct, $col, $op, $val, $columns=null, $ctrl=null){


		$args = array( array("col"=>$col, "op"=>$op, "val"=>$val ) );

		try {
			$result = self::buildSelectQuery($struct, $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "col"=>$col, "op"=>$op, "val"=>$val, "columns"=>$columns, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;
		
	}


	/**
         * Builds an update query for processing by $wpdb->prepare().
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
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 * 
         * @return bool/array | Exception on failure. Int on count. Array of rows on success.
         */

	public function buildUpdateQuery($struct, $data, $args, $columns=null){


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================

		$params_list = array();
		$columns_list = array();

		$columns_list = array_keys($struct["columns"]);


		// Handle data passed as array where the array has missing
		// or nonexistent db column names
		// =====================================================

		if( is_array($data) ){
		    
			$columns_list = array_intersect($columns_list, array_keys($data) );
		}


		// Include or exclude one or more columns from the query
		// ######################################################

		if($columns != null){

			if( !is_array($columns["col"]) ){   // Handle single column name as string

				$temp = array();
				$temp[0] = $columns["col"];
				$columns["col"] = $temp;
			}

			if ($columns["mode"] == "include"){

				$columns_list = array_intersect($columns_list, $columns["col"]);
			}
			elseif ($columns["mode"] == "exclude"){

				$columns_list = array_diff($columns_list, $columns["col"]);
			}
		}


		// Build the columns string
		// ######################################################

		$columns_left = count($columns_list) - 1;

		foreach($columns_list as $column_name){

			$columns_string .= $column_name . " = " . $struct["columns"][$column_name]["format"];
			$in_type = $struct["columns"][$column_name]["php"];
			$out_type = $struct["columns"][$column_name]["sql"];

			$cast = new FOX_cast();

			if( $struct["columns"][$column_name]["format"] == "%r" ){
			    
				$escape = false; 
			}
			else {
				$escape = true;
			}
			
			if( is_array($data) ){
				// Handle data passed as array
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data[$column_name], $in_type, $out_type) );
			}
			else {
				// Handle data passed as object
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data->{$column_name}, $in_type, $out_type) );
			}

			if($columns_left != 0){
				$columns_string .= ", ";
				$columns_left--;
			}

		}


		// Build WHERE statement
		// ######################################################

		$where = "1 = 1";   // We need to pre-load it with a value in case the user adds zero 'where' conditions

		// Primary table args. The "if" clause handles the case where the user passes no args because
		// they do not want to constrain the query using a column in the primary table.
		
		if( is_array($args) ){

			try {
				$result = self::buildWhere($struct, $args, __FUNCTION__, $prefix=null);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error in WHERE clause generator",
					'data'=>array("struct"=>$struct, "data"=>$data, "args"=>$args, "function_name"=>__FUNCTION__),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$where .= $result['where'];
			$params_list = array_merge($params_list, $result['params']);
			unset($result);
			
		}
		elseif( $args !== 'overwrite_all' ){

			$text =  "\nSAFTEY INTERLOCK TRIP [OVERWRITE TABLE] - buildUpdateQuery() called with no WHERE args. Running this query as supplied would ";
			$text .= "overwrite ALL rows in the target table. If this is what you really want to do, set \$args = 'overwrite_all' ";
						

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>$text,
				'data'=>array("struct"=>$struct, "data"=>$data, "args"=>$args, "function_name"=>__FUNCTION__),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}


		// Build the query
		// ######################################################

		$query = "UPDATE " . $this->base_prefix . $struct["table"] . " SET " . $columns_string . " WHERE " . $where;

		// Merge all return data into an array
		// ###############################################################

		$result = array(		    
			'query'=>$query,
			'params'=>$params_list
		);

		return $result;
		
	}


        /**
         * Builds a single-column keyed update query for processing by $wpdb->prepare().
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
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 * 
         * @return array | Exception on failure. Query array on success.
         */

	public function buildUpdateQueryCol($struct, $data, $col, $op, $val, $columns=null){


		$args = array( array("col"=>$col, "op"=>$op, "val"=>$val) );

		try {
			$result = self::buildUpdateQuery($struct, $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in query generator",
				'data'=>array("struct"=>$struct, "data"=>$data, "args"=>$args, "columns"=>$columns),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;
		
	}


	/**
         * Builds an insert query for processing by $wpdb->prepare().
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
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
         * @return array | Exception on failure. Query array on success.
         */

	public function buildInsertQuery($struct, $data, $columns=null){


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================

		$params_list = array();
		$columns_list = array();

		$columns_list = array_keys($struct["columns"]);


		// Include or exclude one or more columns from the query
		// ######################################################

		if($columns != null){

			if( !is_array($columns["col"]) ){   // Handle single column name as string

				$temp = array();
				$temp[0] = $columns["col"];
				$columns["col"] = $temp;
			}

			if ($columns["mode"] == "include"){

				$column_names = array_intersect($columns_list, $columns["col"]);
			}
			elseif ($columns["mode"] == "exclude"){

				$column_names = array_diff($columns_list, $columns["col"]);
			}
		}
		else {
			    $column_names = $columns_list;
		}


		// Build the columns string
		// ######################################################

		$columns_count = count($column_names) - 1;
		$columns_left = $columns_count;

		foreach($column_names as $column_name){

			$columns_string .= $column_name;

			if($columns_left != 0){
				$columns_string .= ", ";
				$columns_left--;
			}

		}

		// CASE 1 - Array Mode
		// ==============================
		if( is_array($data) ){

			$blocks_left = count($data) - 1;
			$cast = new FOX_cast();

			foreach($data as $block){

				$query_formats .= "(";

				$columns_left = $columns_count;

				foreach($column_names as $column_name){

					$query_formats .= $struct["columns"][$column_name]["format"];
					$in_type = $struct["columns"][$column_name]["php"];
					$out_type = $struct["columns"][$column_name]["sql"];

					if( $struct["columns"][$column_name]["format"] == "%r" ){

						$escape = false; 
					}
					else {
						$escape = true;
					}
					
					$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($block[$column_name], $in_type, $out_type) );

					if($columns_left != 0){
						$query_formats .= ", ";
						$columns_left--;
					}

				}

				$query_formats .= ")";

				if($blocks_left != 0){
					$query_formats .= ", ";
					$blocks_left--;
				}

			}
			unset($data); unset($block);
		}

		// CASE 2 - Object Mode
		// ==============================
		else {

			$columns_left = $columns_count;
			$cast = new FOX_cast();

			$query_formats .= "(";

			foreach($column_names as $column_name){

				$query_formats .= $struct["columns"][$column_name]["format"];
				$in_type = $struct["columns"][$column_name]["php"];
				$out_type = $struct["columns"][$column_name]["sql"];

				if( $struct["columns"][$column_name]["format"] == "%r" ){

					$escape = false; 
				}
				else {
					$escape = true;
				}	
				
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data->{$column_name}, $in_type, $out_type) );

				if($columns_left != 0){
					$query_formats .= ", ";
					$columns_left--;
				}

			}

			$query_formats .= ")";

		}

		$query = "INSERT INTO " . $this->base_prefix . $struct["table"] . " (" . $columns_string . ") VALUES " . $query_formats;

		
		// Merge all return data into an array
		// ###############################################################

		$result = array(		    
			'query'=>$query,
			'params'=>$params_list
		);

		return $result;
		
	}


	/**
         * Builds an indate [INsert-upDATE] query for processing by $wpdb->prepare().
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
	 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
	 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
	 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
	 *
         * @return array | Exception on failure. Query array on success.
         */

	public function buildIndateQuery($struct, $data, $columns=null){


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================

		$params_list = array();
		$columns_list = array();

		$columns_list = array_keys($struct["columns"]);


		// Handle data passed as array where the array has missing
		// or nonexistent db column names
		// =====================================================

		if( is_array($data) ){
		    $columns_list = array_intersect($columns_list, array_keys($data) );
		}


		// Include or exclude one or more columns from the query
		// ######################################################

		if($columns != null){

			if( !is_array($columns["col"]) ){   // Handle single column name as string

				$temp = array();
				$temp[0] = $columns["col"];
				$columns["col"] = $temp;
			}

			if ($columns["mode"] == "include"){

				$column_names = array_intersect($columns_list, $columns["col"]);
			}
			elseif ($columns["mode"] == "exclude"){

				$column_names = array_diff($columns_list, $columns["col"]);
			}
		}
		else {
			    $column_names = $columns_list;
		}


		// Build the INSERT columns string
		// ######################################################

		$columns_count = count($column_names) - 1;
		$columns_left = $columns_count;

		foreach($column_names as $column_name){

			$insert_columns_string .= $column_name;

			if($columns_left != 0){
				$insert_columns_string .= ", ";
				$columns_left--;
			}

		}

		// CASE 1 - Array Mode
		// ==============================
		if( is_array($data) ){

			$cast = new FOX_cast();

			$query_formats .= "(";

			$columns_left = $columns_count;

			foreach($column_names as $column_name){

				$query_formats .= $struct["columns"][$column_name]["format"];
				$in_type = $struct["columns"][$column_name]["php"];
				$out_type = $struct["columns"][$column_name]["sql"];

				if( $struct["columns"][$column_name]["format"] == "%r" ){

					$escape = false; 
				}
				else {
					$escape = true;
				}	
				
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data[$column_name], $in_type, $out_type) );

				if($columns_left != 0){
					$query_formats .= ", ";
					$columns_left--;
				}
			}

			$query_formats .= ")";
		}

		// CASE 2 - Object Mode
		// ==============================
		else {

			$columns_left = $columns_count;
			$cast = new FOX_cast();

			$query_formats .= "(";

			foreach($column_names as $column_name){

				$query_formats .= $struct["columns"][$column_name]["format"];
				$in_type = $struct["columns"][$column_name]["php"];
				$out_type = $struct["columns"][$column_name]["sql"];

				if( $struct["columns"][$column_name]["format"] == "%r" ){

					$escape = false; 
				}
				else {
					$escape = true;
				}
				
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data->{$column_name}, $in_type, $out_type) );

				if($columns_left != 0){
					$query_formats .= ", ";
					$columns_left--;
				}
			}

			$query_formats .= ")";
		}


		// Build the UPDATE columns string
		// ######################################################

		$columns_left = $columns_count;

		foreach($column_names as $column_name){

			$update_columns_string .= $column_name . " = " . $struct["columns"][$column_name]["format"];
			$in_type = $struct["columns"][$column_name]["php"];
			$out_type = $struct["columns"][$column_name]["sql"];

			$cast = new FOX_cast();
			
			if( $struct["columns"][$column_name]["format"] == "%r" ){

				$escape = false; 
			}
			else {
				$escape = true;
			}			

			if( is_array($data) ){
				// Handle data passed as array
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data[$column_name], $in_type, $out_type) );
			}
			else {
				// Handle data passed as object
				$params_list[] = array('escape'=>$escape, 'val'=>$cast->PHPToSQL($data->{$column_name}, $in_type, $out_type) );
			}

			if($columns_left != 0){
				$update_columns_string .= ", ";
				$columns_left--;
			}

		}

		$query = "INSERT INTO " . $this->base_prefix . $struct["table"] . " (" . $insert_columns_string . ") VALUES " . $query_formats .
			 " ON DUPLICATE KEY UPDATE " . $update_columns_string;


		// Merge all return data into an array
		// ###############################################################
		
		$result = array(		    
			'query'=>$query,
			'params'=>$params_list
		);		

		return $result;
	}


	/**
         * Builds a delete query for processing by $wpdb->prepare().
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
	 *	=> VAL @param string $args_format | "default" to use standard format, "multi", or "matrix"
	 * 
         * @return array | Exception on failure. Query array on success.
         */

	public function buildDeleteQuery($struct, $args, $ctrl=null){


		$ctrl_default = array(
			'args_format' => 'default'
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================


		// Build WHERE statement
		// ######################################################

		$where = "1 = 1";   // We need to pre-load it with a value in case the user adds zero 'where' conditions

		// Primary table args. The "if" clause handles the case where the user passes no args because
		// they do not want to constrain the query using a column in the primary table.
		
		if($args){

			try {
			    
				switch($ctrl['args_format']){

					case "multi" : {

						$result = self::buildWhereMulti($struct, $args, __FUNCTION__, $prefix=null);

					} break;

					case "matrix" : {

						// Forward the 'hash_token_vals' flag, if set
					    
						if( FOX_sUtil::keyExists('hash_token_vals', $args) ){
						
							$matrix_ctrl = array('hash_token_vals'=>$args['hash_token_vals']);
						}
						else {
							$matrix_ctrl = null;
						}
						
						$result = self::buildWhereMatrix($struct, $args["key_col"], $args["args"], $matrix_ctrl);

					} break;
				    
					case "trie" : {

						// Forward the 'hash_token_vals' flag, if set
					    
						if( FOX_sUtil::keyExists('hash_token_vals', $args) ){
						
							$trie_ctrl = array('hash_token_vals'=>$args['hash_token_vals']);
						}
						else {
							$trie_ctrl = null;
						}
						
						$result = self::buildWhereTrie($struct, $args["key_col"], $args["args"], $trie_ctrl);

					} break;				    

					default : {

						$result = self::buildWhere($struct, $args, __FUNCTION__, $prefix=null);

					} break;

				}
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error in buildWhere generator",
					'data'=>array("struct"=>$struct, "args"=>$args, "ctrl"=>$ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$where .= $result['where'];
			$params_list = $result['params'];
			unset($result);
		}
		else {

			// From the MySQL site: "DELETE FROM tbl_name does not regenerate the table but instead deletes all rows, one by one."
			// @link http://dev.mysql.com/doc/refman/5.5/en/innodb-restrictions.html

			$text =  "\nSAFTEY INTERLOCK TRIP [DESTROY TABLE] - buildDeleteQuery() called with no WHERE args. Running this query would delete ";
			$text .= " ALL rows in the target table. If this is what you really want to do, use buildTruncateTable().\n";

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>$text,
				'data'=>array("struct"=>$struct, "args"=>$args, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		$query = "DELETE FROM " . $this->base_prefix . $struct["table"] . " WHERE " . $where;


		// Merge all return data into an array
		// ###############################################################

		$result = array(		    
			'query'=>$query,
			'params'=>$params_list
		);

		return $result;

	}


        /**
         * Builds a single-column keyed delete query for processing by $wpdb->prepare().
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 * @param string $col | Column name to use for WHERE construct
	 * @param string $op | Comparison operator ">=", "<=", ">", "<", "=", "!=", "<>"
	 * @param int/string $val | Comparison value to use in WHERE construct
	 * 
         * @return array | Exception on failure. Query array on success.
         */

	public function buildDeleteQueryCol($struct, $col, $op, $val){


		$args = array( array("col"=>$col, "op"=>$op, "val"=>$val) );

		try {
			$result = self::buildDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in guery generator",
				'data'=>array("struct"=>$struct, "args"=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;
		
	}

	/**
         * Builds a query to add or restructure a database table, given an array defining the table's structure
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
	 *	=> VAL @param string 'db_table_name' | Name of the db table
	 *	=> VAL @param string 'engine' | Name of table db engine
	 *	=> ARR @param array 'columns' | Array of database column arrays.
	 *	    => ARR @param string '' | Name of the db column this key describes
	 *		=> VAL @param string 'format' | Display format of column, usually %s or %d, @see http://php.net/manual/en/function.sprintf.php
	 *		=> VAL @param string 'type' Build string used in table creation query
	 *		=> VAL @param bool 'index' True if the column is indexed by the SQL server. False if not.
	 *	=> ARR @param array 'composite_keys' | Array of composite key arrays
	 *	    => ARR @param '' | Composite key array
	 *		=> VAL @param string 'name' | Name of composite key
	 *		=> VAL @param array 'columns' | Array of column names to use for key
	 *
         * @return array | Exception on failure. Query string on success.
         */

	public function buildAddTable($struct){


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}

		// Check for illegal keys
		// ====================================================

		$cls = new FOX_dbUtil();

		foreach($struct["columns"] as $key => $fake_var){


			if( $cls->isReservedWord($key) == true ){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Tried to use SQL reserved word as column name",
					'data'=>array("struct"=>$struct, "illegal_key"=>$key),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

		}
		unset($key, $fake_var);


		$charset_collate = '';

		if ( !empty($this->charset) ){
		    $charset_collate = "DEFAULT CHARACTER SET {$this->charset}";
		}

		if ( !empty($this->collate) ){
			$charset_collate .= " COLLATE {$this->collate}";
		}


		// Trap missing table definition
		// ====================================================

		if( empty($struct["table"]) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Missing table definition array",
				'data'=>array("struct"=>$struct),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Trap missing columns array
		// ====================================================

		if( empty($struct["columns"]) ){

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Missing columns array",
				'data'=> array("struct"=>$struct),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}


		// Build the columns string
		// ====================================================

		$columns_left = count($struct["columns"]) - 1;
		$keys = array();

		foreach( $struct["columns"] as $name => $params ){

			// Trap missing column name
			// ===========================
			if( empty($name) ){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Missing column name",
					'data'=> array("columns_array"=>$struct["columns"], "name"=>$name, "params"=>$params),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			// Trap missing SQL data type
			// ===========================
			if( empty($params["sql"]) ){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Missing sql data type. \n",
					'data'=>array("columns_array"=>$struct["columns"], "name"=>$name,
						      "params"=>$params, "type"=>$params["sql"]),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}


			// Build the columns string
			// ===========================

			$query_columns .= $name . " " . $params["sql"];

			if($params["width"]){
				$query_columns .= "(" . $params["width"] . ")";
			}

			if($params["flags"]){
				$query_columns .= " " . $params["flags"];
			}

			if($params["auto_inc"] == true){

				$query_columns .= " AUTO_INCREMENT";

				if($check) {

					$auto_inc_count++;

					// Trap multiple auto-incrememt columns
					// ========================================

					if($auto_inc_count > 1){

						throw new FOX_exception( array(
							'numeric'=>6,
							'text'=>"MySQL tables cannot have more than one auto-increment column",
							'data'=> array("params"=>$params),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}

				}
			}

			if($params["default"] !== null){
				$query_columns .= " DEFAULT '" . $params["default"] . "'";
			}

			if($columns_left != 0){
				$query_columns = $query_columns . ", ";
				$columns_left--;
			}


			// Add indexed columns to keys array
			if($params["index"]){
				$keys[$name] = $params;
			}

		}
		unset($name, $params);


		// Build the keys string
		// ---------------------

		foreach($keys as $name => $params){

			if($params["index"] === "PRIMARY"){

			    $keys_string .= ", PRIMARY KEY (" . $name . ")";
			}
			elseif($params["index"] === "UNIQUE"){

			    $keys_string .= ", UNIQUE KEY " . $name . " (" . $name . ")";
			}
			elseif($params["index"] === "FULLTEXT"){


				if($struct["table"] == "InnoDB") {

					// From the MySQL site: "InnoDB tables do not support FULLTEXT indexes."
					// @link http://dev.mysql.com/doc/refman/5.5/en/innodb-restrictions.html

					throw new FOX_exception( array(
						'numeric'=>7,
						'text'=>"FULLTEXT indices cannot be added to InnoDB tables. \n",
						'data'=> array("params"=>$params),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));					
				}

				$keys_string .= ", FULLTEXT KEY (" . $name . ")";

			}
			elseif( is_array($params["index"]) ){

				if($params["index"]["index"] === "PRIMARY"){

					$keys_string .= ", PRIMARY KEY " . $params["index"]["name"] . " (";
				}
				elseif($params["index"]["index"] === "UNIQUE"){

					$keys_string .= ", UNIQUE KEY " . $params["index"]["name"] . " (";
				}
				else{
					$keys_string .= ", KEY " . $params["index"]["name"] . " (";
				}

				$keys_left = count($params["index"]["col"]) - 1;

				foreach ($params["index"]["col"] as $col){

					$keys_string .= $col;

					if($keys_left != 0){
						$keys_string .= ", ";
						$keys_left--;
					}
				}

				$keys_string .= ")";


				// Handle secondary index on the row
				// =======================================================================

				if($params["index"]["this_row"]){


					if($params["index"]["this_row"] === "PRIMARY"){

					    $keys_string .= ", PRIMARY KEY (" . $name . ")";
					}
					elseif($params["index"]["this_row"] === "UNIQUE"){

					    $keys_string .= ", UNIQUE KEY " . $name . " (" . $name . ")";
					}
					elseif($params["index"]["this_row"] === "FULLTEXT"){

						if($struct["table"] == "InnoDB") {

							// From the MySQL site: "InnoDB tables do not support FULLTEXT indexes."
							// @link http://dev.mysql.com/doc/refman/5.5/en/innodb-restrictions.html

							throw new FOX_exception( array(
								'numeric'=>8,
								'text'=>"FULLTEXT indices cannot be added to InnoDB tables",
								'data'=> array("params"=>$params),
								'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
								'child'=>null
							));
						}

						$keys_string .= ", FULLTEXT KEY (" . $name . ")";

					}
					elseif($params["index"]["this_row"] === true){

						$keys_string .= ", KEY " . $name . " (" . $name . ")";
					}
				}

			}
			else{

			    $keys_string .= ", KEY " . $name . " (" . $name . ")";
			}

		}
		unset($name, $params);


		$result = "CREATE TABLE " . $this->base_prefix . $struct["table"] . " ( " . $query_columns . $keys_string . " ) ENGINE=" . $struct["engine"] . " " . $charset_collate . ";";

		return $result;
		
	}


	/**
         * Builds a query to drop a database table, given an array defining the table's structure
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
         * @return bool/string | Exception on failure. Query string on success.
         */

	public function buildDropTable($struct) {


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================

		$result = "DROP TABLE " . $this->base_prefix . $struct["table"];

		return $result;
		
	}


	/**
         * Deletes all rows from a database table, given an array defining the table's structure
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
         * @return bool | Exception on failure. True on success.
         */

	public function buildTruncateTable($struct){


		// Switch between unit test mode (pass as array) and
		// normal mode (pass as class name)
		// ====================================================

		if( is_string($struct) ){

			$struct = call_user_func( array($struct,'_struct') );
		}
		// ====================================================

		$result = "TRUNCATE TABLE " . $this->base_prefix . $struct["table"];

		return $result;
		
	}

	
	/**
         * Determines if a table contains a column which uses a GIS data type such as 'point' or 'polygon'
         *
         * @version 1.0
         * @since 1.0
         *
         * @param array $struct | Structure of the db table, @see class FOX_db header for examples
         * @return bool | Exception on failure. True if one or more columns use a GIS data type. False if not.
         */
	
	public function hasGISColumn($struct){
	    
	    
		foreach( $struct['columns'] as $column ){
		
			if( self::isGISDataType($column['sql']) ){

				return true;
			}
		}
		unset($column);
		
		return false;
		
	}
	
	
	/**
         * Determines if a SQL column's data type is a GIS data type
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $type | Column data type as string
         * @return bool | Exception on failure. True if GIS data type. False if not.
         */
	
	public function isGISDataType($type){
	    
	    
		if( ($type == 'point') || ($type == 'polygon') ){

			return true;
		}
		else {		
			return false;
		}
		
	}	
	
	
	
} // End of class FOX_queryBuilder

?>