<?php

/**
 * FOXFIRE EVENT LOGGING
 * Handles user event logging within FoxFire
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Logging
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_log_event extends FOX_db_base {

	// DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "FOX_log_event",
		"engine" => "InnoDB", // Required for transactions
		"cache_namespace" => "FOX_log_event",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc"),
		"columns" => array(
		    "id" =>	    array(	"php"=>"int",	    "sql"=>"bigint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,  "default"=>null,	"index"=>"PRIMARY"),
		    "tree" =>	    array(	"php"=>"int",	    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "branch" =>	    array(	"php"=>"int",	    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "node" =>	    array(	"php"=>"int",	    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "user_id" =>    array(	"php"=>"int",	    "sql"=>"int",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED",		"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "level" =>	    array(	"php"=>"int",	    "sql"=>"tinyint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "date" =>	    array(	"php"=>"int",	    "sql"=>"datetime",	    "format"=>"%s", "width"=>null,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "summary" =>    array(	"php"=>"string",    "sql"=>"varchar",	    "format"=>"%s", "width"=>128,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
		    "data" =>	    array(	"php"=>"serialize", "sql"=>"longtext",	    "format"=>"%s", "width"=>null,	"flags"=>"",			"auto_inc"=>false,  "default"=>null,	"index"=>false)
		 )
	);


	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_user_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	// ================================================================================================================

	var $config_class;		    // System config class

	var $dict_tree;			    // Tree dictionary singleton
	var $dict_branch;		    // Branch dictionary singleton
	var $dict_node;			    // Node dictionary singleton

	// ================================================================================================================


	public function FOX_log_event(&$args=null) {

		// Handle dependency-injection for unit tests
		if($args){

			$this->config_class = &$args['config_class'];
			$this->dict_tree = &$args['dict_tree'];
			$this->dict_branch = &$args['dict_branch'];
			$this->dict_node = &$args['dict_node'];
		}
		else {
			global $fox;
			$this->config_class = &$fox->config;
			$this->dict_tree = new FOX_log_dictionary_tree();
			$this->dict_branch = new FOX_log_dictionary_branch();
			$this->dict_node = new FOX_log_dictionary_node();
		}

	}


	/**
	 * Adds an event to the event log
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $event | Array holding data to be added to DB. An array in this form: key string for the column with value as null | int | string | array
	 *	=> KEY @param string "tree"
	 *	    => VAL @param int $tree | id of the event's tree
	 *	=> KEY @param string "branch"
	 *	    => VAL @param int $branch | id of the event's branch
	 *	=> KEY @param string "node"
	 *	    => VAL @param int $node | id of the event's node
	 *	=> KEY @param string "user_id"
	 *	    => VAL @param int $user_id | id of the user_id associated with this event. Use int 0 for a system event
	 *	=> KEY @param string "level"
	 *	    => VAL @param int $level | severity of this event. 1-255, where 1 is the most serious
	 *	=> KEY @param string "date"
	 *	    => VAL @param int $date | date and time the event occured, as linux datetime
	 *	=> KEY @param string "summary"
	 *	    => VAL @param string $summary | Human readable summary of the event. Max 128 characters.
	 *	=> KEY @param string "data"
	 *	    => VAL @param mixed $data | Full data associated with this event. Any PHP data type can be stored.
	 *
	 * @return bool/int | False on failure. ID of the created log entry on success.
	 */

	public function add($event){

		if( empty($event)){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"No event specified for addition",
				    'data'=> $event,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
			));

			return false;
		 }

		 // Convert tree, branch, and node strings to ids
		 // ===================================================================
		if( is_string($event["tree"]) && is_string($event["branch"]) && is_string($event["node"])){

			try{
			    
				$tree_id = $this->dict_tree->getToken($event["tree"]);
				$branch_id = $this->dict_branch->getToken($event["branch"]);
				$node_id = $this->dict_node->getToken($event["node"]);				
			}
			catch(FOX_exception $child) {
			    echo "Error reading dictionary";
				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"Error reading dictionary",
					    'data'=> array( "tree_id"=>$event["tree"],
							    "branch_id"=>$event["branch"],
							    "node_id"=>$event["node"] ),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				));

				return false;			
			}
		}
		else {

			$tree_id = $event["tree"];
			$branch_id = $event["branch"];
			$node_id = $event["node"];

		}

		// Check date
		if( !isset($event["date"])){

			$event["date"]= time();

		}

		 // Add row to database
		 // ===================================================================

		$db = new FOX_db();

		$data = array(
				"tree"=>$tree_id,
				"branch"=>$branch_id,
				"node"=>$node_id,
				"user_id"=>$event["user_id"],
				"level"=>$event["level"],
				"date"=>$event["date"],
				"summary"=>$event["summary"],
				"data"=>$event["data"],
		);

		try{
			$query_result = $db->runInsertQuery(self::$struct, $data, $columns=null, $ctrl=null);
		}
		catch(FOX_exception $child) {
		
			throw new FOX_exception(array(
				    'numeric'=>3,
				    'text'=>"Error writing to database",
				    'data'=>$data,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
			));

			return false;
		}

		return $db->insert_id;

	}


	/**
	 * Drops one or more events from the log, based on the supplied args
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @return bool/int | False on failure. Number of rows deleted on success.
	 */

	public function drop($args){

		//Check $args to prevent accidentally deleting all rows in the table
		if( is_null($args)){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"No args specified for deletion \n",
				'data'=> $args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));

			return false;
		}

		// array used to convert from dictionary to number
		$dic_col = array("tree"=>$this->dict_tree, "branch"=>$this->dict_branch, "node"=>$this->dict_node);

		$delete_dic = array();

		$processed_args = array();

		// Process args incase dic entries are used instead of numbers
		foreach($args as $arg){

			// check dic columns
			if( in_array($arg["col"],array_keys( $dic_col ) ) ){

				// if val is string or array of strings
				if( is_string($arg["val"]) || (is_array($arg["val"]) && is_string($arg["val"][0])) ){

					// if  op is equal add to delete dic array
					if($arg["op"]=="="){

						$delete_dic[$arg["col"]] = $arg["val"];
					}
					if( is_string($arg["val"])){

						try{
							$arg["val"] = $dic_col[$arg["col"]]->getToken($arg["val"]);
						}
						catch(FOX_exception $child){
						    
							throw new FOX_exception(array(
								'numeric'=>2,
								'text'=>"Error converting token to id \n",
								'data'=> $args,
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							));

							return false;
						    
						}
					}
					else {

						$vals = array();

						foreach($arg["val"] as $val){

							try{
								$val = $dic_col[$arg["col"]]->getToken($val);
								$vals[] = $val;
							}
							catch(FOX_exception $child){

								throw new FOX_exception(array(
									'numeric'=>3,
									'text'=>"Error converting token to id \n",
									'data'=> array('source'=>$val, 'args'=>$args),
									'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
									'child'=>$child
								));

								return false;

							}
						}

						$arg["val"] = $vals;

					}
//					$dic = null;
//
//					if($arg["col"]== "tree") {
//
//						$dic = $this->dict_tree;
//					}
//					elseif($arg["col"]== "branch") {
//
//						$dic = $this->dict_branch;
//					}
//					else {
//
//						$dic = $this->dict_node;
//					}
//
//					if( is_string($arg["val"])) {
//
//						$arg["val"] = $dic->getToken($arg["val"]);
//
//					}
//					else {
//
//						$vals = array();
//
//						foreach($arg["val"] as $val) {
//
//							$val = $dic->getToken($val);
//							$vals[] = $val;
//						}
//
//						$arg["val"] = $vals;
//
//					}

				}
			}

		    $processed_args[] = $arg;
		}

		$db = new FOX_db();
		try{
			$result = $db->runDeleteQuery(self::$struct, $processed_args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>4,
				'text'=>"Error deleting DB rows \n",
				'data'=> array('processed_args'=>$processed_args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));

			return false;

		}

		// if droping with equal(=) token remove tokens from cache
		if($result!=0 && count($delete_dic)>0){

			foreach($delete_dic as $key=>$value){

				try{
					$dic_col[$key]->dropToken($value);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Error deleting cache entries \n",
						'data'=> array('delete_dic'=>$delete_dic),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));

					return false;
				}				
			}
		}

		if ($result == 0){

			return false;
		}
		else {

			return $result;
		}

	}


	/**
	 * Drops one or more events from the log, based on the supplied event id's
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $id | Single event id as int. Multiple event id's as array of ints.
	 * @return bool/int | False on failure. Number of rows deleted on success.
	 */

	public function dropID($id){

		if( is_null($id) ){
			
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"No id specified for deletion \n",
				'data'=> $id,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));

			return false;
		}

		$db = new FOX_db();

		$args = array( array("col"=>"id", "op"=>"=", "val"=>$id) );
		
		try{
			$result = $db->runDeleteQuery(self::$struct, $args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"Error deleting DB rows \n",
				'data'=> array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));

			return false;
		}

		if($result == 0 ){
		    
			return false;
		}
		else {
		    
			return $result;
		}

	}
	/**
	 * Drops all events from the log
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param bool $drop_dic | True to delete entires in the dictionaries.
	 * @return bool | True on success.
	 */

	public function dropAll($drop_dic=true){

		try{
			$result = self::truncate();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Error deleting DB rows \n",
				'data'=> null,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));

			return false;
		}

		if ($result && $drop_dic ){

			try{
			    
				$this->dict_tree->truncate();
				$this->dict_branch->truncate();
				$this->dict_node->truncate();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					'numeric'=>2,
					'text'=>"Error deleting DB rows \n",
					'data'=> array('args'=>$args),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));

				return false;
			}
			
			try{
			    
				$this->dict_tree->flushCache();
				$this->dict_branch->flushCache();
				$this->dict_node->flushCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Error flushing cache \n",
					'data'=> null,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));

				return false;
			}
		}
		
		return $result;
	}
        /**
         * Returns an array of logged events based on supplied parameters. This function integrates with a jQuery
	 * datagrid control in the admin screens, allowing arbitrary sorting and filtering of events.
         *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	    => ARR @param array $args | Args in the form: array("col"=>column_name, "op"=>"<, >, =, !=", "val"=>"int | string | array()")
	 *		=> ARR @param int '' | Array index
	 *		    => VAL @param string $col | Name of the column this key describes	 *
	 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *		    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.	 *
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
	 * @param bool $return_words | True to use dictionary and return words for tree, branch and node
	 *
         * @return bool/int/array | False on failure. Int on count. Array of rows on success.
         */

	public function query($args=null, $columns=null, $ctrl=null, $return_words=true){

		if( !isset($ctrl) ){
		    $ctrl = array();
		}

		if( !isset($ctrl['format'])){

			$ctrl['format'] = "array_array";
		}		
		
		$join_args = array("tree"=>array(), "branch"=>array(), "node"=>array());
				
		if( is_array($args)){

			// Process args in case dic entries are used instead of numbers
			foreach($args as $arg){

				// check dic columns
				if( in_array($arg["col"],array( 'tree', 'branch', 'node') ) ){

					// if val is string or array of strings
					if( !is_numeric($arg["val"]) || (is_array($arg["val"]) && !is_numeric($arg["val"][0])) ){

						$join_args[$arg['col']][] = $args;
						continue;
					}
				}

			    $processed_args[] = $arg;
			}
		}

		$db = new FOX_db();
		
		// Check if need to use runSelectLeftJoin
		$num_join_args = count($join_args['tree']) + count($join_args['branch']) + count($join_args['node']);

		if($return_words || $num_join_args>0){

		    	if(!isset($ctrl['group'])){

				$ctrl["group"]= array( array("class"=>self::$struct, "col"=>"id", "sort"=>"ASC") );
			}


			$primary = array( "class"=>self::$struct, "args"=>$processed_args);

			$join = array(
				array("class"=>"FOX_log_dictionary_tree","on"=>array("pri"=>"tree", "op"=>"=", "sec"=>"id"), "args"=>$join_args['tree']),
				array("class"=>"FOX_log_dictionary_branch","on"=>array("pri"=>"branch", "op"=>"=", "sec"=>"id"), "args"=>$join_args['branch']),
				array("class"=>"FOX_log_dictionary_node","on"=>array("pri"=>"node", "op"=>"=", "sec"=>"id"), "args"=>$join_args['node'])
			);

			if( is_null($columns)){

				if ($return_words){
					$columns = array(
						array("table_alias"=>"t1", "col_name"=>"id", "col_alias"=>"id"),
						array("table_alias"=>"t2", "col_name"=>"token", "col_alias"=>"tree"),
						array("table_alias"=>"t3", "col_name"=>"token", "col_alias"=>"branch"),
						array("table_alias"=>"t4", "col_name"=>"token", "col_alias"=>"node"),
						array("table_alias"=>"t1", "col_name"=>"user_id", "col_alias"=>"user_id"),
						array("table_alias"=>"t1", "col_name"=>"level", "col_alias"=>"level"),
						array("table_alias"=>"t1", "col_name"=>"date", "col_alias"=>"date"),
						array("table_alias"=>"t1", "col_name"=>"summary", "col_alias"=>"summary"),
						array("table_alias"=>"t1", "col_name"=>"data", "col_alias"=>"data"),

					);
				}
				else {
					$columns = array(
						array("table_alias"=>"t1", "col_name"=>"id", "col_alias"=>"id"),
						array("table_alias"=>"t1", "col_name"=>"tree", "col_alias"=>"tree"),
						array("table_alias"=>"t1", "col_name"=>"branch", "col_alias"=>"branch"),
						array("table_alias"=>"t1", "col_name"=>"node", "col_alias"=>"node"),
						array("table_alias"=>"t1", "col_name"=>"user_id", "col_alias"=>"user_id"),
						array("table_alias"=>"t1", "col_name"=>"level", "col_alias"=>"level"),
						array("table_alias"=>"t1", "col_name"=>"date", "col_alias"=>"date"),
						array("table_alias"=>"t1", "col_name"=>"summary", "col_alias"=>"summary"),
						array("table_alias"=>"t1", "col_name"=>"data", "col_alias"=>"data"),

					);				    
				}
			}	
			try{
				$result = $db->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Error reading from DB \n",
					'data'=> array('primary'=>$primary, 'join'=>$join, 'columns'=>$columns, 'ctrl'=>$ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));

				return false;
			}			
			
		} 
		else {
		    
			if( !is_null($columns) && count($columns)>0){
			    
				$select_columns["mode"] ="include";
				
				if(count($columns)==1){

					$select_columns["col"]  = $columns[0]["col_name"];
				}
				else {

					foreach($columns as $col){

						$select_columns["col"][]  = $col["col_name"];
					}
				}
			}

			$result = $db->runSelectQuery(self::$struct, $processed_args, $select_columns, $ctrl);
			
		}
		


		if($result == 0){

		    return false;

		}
		else {

		    return $result;

		}

	} // End of function query()




} // End of class FOX_log_event



// ^F^ | This class allows the user to paginate through results in the admin screen ...because
// ^F^ | when the logging class has 100,000 events in it, they're definitely not going to fit
// ^F^ | on one screen. Note that the code below is just a stub. Because our query function
// ^F^ | and FOX_db_walker have different args formats, you might have to copy some of the functions
// ^F^ | from FOX_db_walker into the class below and restructure them, instead of just extending
// ^F^ | FOX_db_walker


/**
 * FOXFIRE EVENT LOG TEMPLATE CLASS
 * Operates main loop in templates when displaying event log items
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Logging
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_log_event_template extends FOX_db_walker {

	var $pag_links;			// Pagination links for the current page in the template object

	// ============================================================================================================ //

        /**
         * Creates a template object filled with query result objects, based on user supplied parameters
         *
         * @version 1.0
         * @since 1.0
         *
	 * @param array $args | @see FOX_db::runSelectQuery() for array structure
	 * @param bool/array $columns | @see FOX_db::runSelectQuery() for array structure
	 * @param array $ctrl | @see FOX_db::runSelectQuery() for array structure
	 *
         * @return bool/int/array | False on failure. Int on count. Array of objects on success.
         */

	function __construct($args=null, $columns=null, $ctrl=null) {


	    	// Run the parent constructor
		// =========================================================================

		$db_class = new FOX_log_event();

		parent::__construct($db_class, $args, $columns, $ctrl);

			    	// Build the pagination links string
		// =========================================================================

		$this->pag_links = paginate_links( array(

			'base' => add_query_arg( 'page', '%#%' ),
			'format' => '',
			'total' => $this->total_pages,
			'current' => (int) $ctrl["page"],
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'mid_size' => 1
		));
	}


} // End of class FOX_log_event_template



 /**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_log_event(){

	$cls = new FOX_log_event();
	
	try {
		$cls->install();
	}
	catch (FOX_exception $child) {

		// If the error is being thrown because the table already exists, 
		// just discard it
	    
		if( $child->data['child']->data['numeric'] != 2 ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error creating db table",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
	}
	
}
add_action( 'fox_install', 'install_FOX_log_event', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_log_event(){

	$cls = new FOX_log_event();
	
	try {
		$cls->uninstall();
	}
	catch (FOX_exception $child) {

		// If the error is being thrown because the table doesn't exist, 
		// just discard it
	    
		if( $child->data['child']->data['numeric'] != 3 ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error dropping db table",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
	}
	
}
add_action( 'fox_uninstall', 'uninstall_FOX_log_event', 2 );

?>