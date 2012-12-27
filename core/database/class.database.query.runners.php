<?php

/**
 * FOXFIRE DATABASE QUERY RUNNER CLASS
 * Runs pre-built queries against the SQL server and returns formatted results
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

class FOX_queryRunner {


	var $parent;				    // Reference to parent class

	// ============================================================================================================ //


	function FOX_queryRunner(&$parent_class) {

		$this->__construct($parent_class);
	}


	function __construct(&$parent_class){

		// We link the $parent var inside this class to the instance of the parent class passed in
		// the constructor. This means we can access any variable or function inside the parent class
		// as "$this->parent->varName"

		$this->parent =& $parent_class;
	}

	
	/**
         * Runs a query on the database. Returns results in a specific format.
         *
         * @version 1.0
         * @since 1.0
	 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_select_formatter
         *
         * @param string $sql | SQL query string
	 *
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param string $format | Return format for query: "col", "row", "var", "array_key_object", "array_key_array"
	 *				       "array_key_single", "array_object", "array_array", "raw", or (null)
	 *				       @see result formatter headers inside this method for detailed docs
	 *
	 *	=> VAL @see FOX_db::runSelectQuery() and FOX_db::runSelectQueryJoin() for docs on remaining $ctrl options
	 *
         * @return bool | Exception on failure. Query results array on success.
         */

	public function runQuery($query, $ctrl=null){


		$ctrl_default = array(
					'format'=>'raw',		    
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);
		
		
		if($this->parent->print_query_args == true){

			ob_start();
			print_r($query);
			print_r($ctrl);
			$out = ob_get_clean();
			FOX_debug::addToFile($out);
		}

		// Handle single parameter as string/int, or multiple
		// parameters passed as an array
		// ==================================================

		if( is_array($query) ){

			$sql = $this->parent->driver->prepare($query["query"]);			
		}
		else {
			$sql = $query;
			$ctrl = array("format"=>"raw");
		}


		if($this->parent->print_query_sql == true){

			FOX_debug::addToFile($sql);
		}


		// EXAMPLE TABLE "test_table"
		// =================================
		//  col_1 | col_2 | col_3 | col_4 |
		// =================================
		//    1   |  red  |  dog  |  big  |
		//    2   |  green|  cat  |  med  |
		//    3   |  blue |  bird | small |
		//    4   |  black|  fish |  tiny |


		$cast = new FOX_cast();

		switch($ctrl["format"]){

		    // VAR
		    // =============================================================================
		    // Used when fetching query results that return a single variable.
		    //
		    // EXAMPLE: "SELECT COUNT(*) FROM test_table WHERE col_2 = red"
		    // RESULT: string "1" (all data is returned in string format)

		    case "var" : {

			    try {
				    $result = $this->parent->driver->get_var($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>1,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }

			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "\nRAW, format = var\n";
				    print_r($result);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="var", $result, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = var\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

		    } break;


		    // COL
		    // =============================================================================
		    // Used when fetching query results which only contain values from a single column.
		    //
		    // EXAMPLE: "SELECT col2 FROM test_table"
		    // RESULT: array("red", "green", "blue", "black")

		    case "col" : {
			    			    
			    try {
				    $result = $this->parent->driver->get_col($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>2,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }			    

			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "\nRAW, format = col\n";
				    print_r($result);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }


			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="col", $result, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = col\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

		    } break;


		    // ROW_OBJECT
		    // =============================================================================
		    // Returns a single database row as an object, with variable names mapped to
		    // column names. If the query returns multiple rows, only the first row is returned.
		    //
		    // EXAMPLE: "SELECT * FROM test_table WHERE col_1 = 2"
		    // RESULT: object stdClass(
		    //		    col_1->2
		    //		    col_2->"green"
		    //		    col_3->"cat"
		    //		    col_4->"med"
		    //	    )

		    case "row_object" : {

			    try {
				    $result = $this->parent->driver->get_row($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>3,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }			

			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "\nRAW, format = row\n";
				    print_r($result);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }


			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="row_object", $result, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = row_object\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

		    } break;


		    // ROW_ARRAY
		    // =============================================================================
		    // Returns a single database row as an array, with key names mapped to column
		    // names. If the query returns multiple rows, only the first row is returned.
		    //
		    // EXAMPLE: "SELECT * FROM test_table WHERE col_1 = 2"
		    // RESULT: array(
		    //		    "col_1"=>2
		    //		    "col_2"=>"green"
		    //		    "col_3"=>"cat"
		    //		    "col_4"=>"med"
		    //	       )

		    case "row_array" : {

			    
			    try {
				    $data = $this->parent->driver->get_row($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>4,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "\nRAW, format = row_array\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="row_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = row_array\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    // Convert row object into array
				    foreach($data as $key => $value){
					    $result[$key] = $value;
				    }

				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = row_array\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }



		    } break;


		    // ARRAY_KEY_OBJECT
		    // =============================================================================
		    //
		    // When used with a single column name passed as a string:
		    //
		    // Returns results as an array of objects, where the primary array key names are
		    // set based on the contents of a database column.
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"]="col_3"
		    // RESULT:   array(
		    //		    "dog" =>stdClass( "col_1"->1, "col_2"->"red", "col_3"->"dog", "col_4"->"big"),
		    //		    "cat" =>stdClass( "col_1"->2, "col_2"->"green", "col_3"->"cat", "col_4"->"med"),
		    //		    "bird"=>stdClass( "col_1"->3, "col_2"->"blue", "col_3"->"bird", "col_4"->"small"),
		    //		    "fish"=>stdClass( "col_1"->4, "col_2"->"black", "col_3"->"fish", "col_4"->"tiny"),
		    //	     )
		    // When used with multiple column names passed as an array of strings:
		    //
		    // Returns results as an array of arrays^N, where the array key names and heirarchy
		    // are set based on the contents of the $key_col array. This output format REQUIRES
		    // that each taxonomy group (in the example below "col_3" + "col_4") is UNIQUE
		    //
		    // EXAMPLE TABLE "test_table_2"
		    // ======================================
		    //  col_1 | col_2 | col_3 | col_4 | col_5
		    // ======================================
		    //    1   |  red  |  dog  |  big  | heavy
		    //    2   |  green|  dog  |  med  | light
		    //    3   |  blue |  bird | small | light
		    //    4   |  black|  fish |  tiny | average
		    //    5   |  black|  fish | large | light
		    //
		    // UNIQUE KEY col_3_col4(col_3, col_4)
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_3, "col_4")
		    // RESULT:   array(
		    //		    "dog" =>array(
		    //				    "big"=>stdClass("col_1"->1, "col_2"->"red", "col_5"->"heavy"),
		    //				    "med"=>stdClass("col_1"->2, "col_2"->"green", "col_5"->"light")
		    //		    ),
		    //		    "bird"=>array(
		    //				    "small"=>stdClass("col_1"->3, "col_2"->"blue", "col_5"->"light")
		    //		    ),
		    //		    "fish"=>array(
		    //				    "tiny"=>stdClass("col_1"->4, "col_2"->"black", "col_5"->"average"),
		    //				    "large"=>stdClass("col_1"->5, "col_2"->"black", "col_5"->"light")
		    //		    )
		    //	     )

		    case "array_key_object" : {			    

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>5,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "\nRAW, format = array_key_object\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_key_object\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    // If a single column name is passed as a string, use the more efficient
				    // direct assignment algorithm to build a 1 level tree

				    if( !is_array($ctrl["key_col"]) ){

					    foreach($data as $row){

						    $key = $row->{$ctrl["key_col"]};
						    $result[$key] = $row;
					    }
				    }

				    // If an array of column names are passed, use the less efficient eval()
				    // algorithm to build a n-level deep tree

				    else {

					    foreach($data as $row){

						    // Since there is no functionality in PHP for creating a new array key
						    // based on a name stored in a variable ( $$ variable variable syntax does
						    // not work for multidimensional arrays, we have to build a string of PHP
						    // code and use eval() to run it

						    $eval_str = "\$result";

						    foreach($ctrl["key_col"] as $keyname){

							    $eval_str .= '["' . $row->{$keyname} . '"]';
						    }

						    $eval_str .= " = \$row_copy;";

						    $row_copy = new stdClass();

						    // Copy the row object into a new stdClass, skipping keys that are used as the
						    // branch variables

						    foreach($row as $key => $value){

							    if( array_search($key, $ctrl["key_col"]) === false ){
								    $row_copy->{$key} = $value;
							    }
						    }

						    // Run the PHP string we have built
						    eval($eval_str);

					    }
				    }


				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = array_key_object\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;


		    // ARRAY_KEY_ARRAY
		    // =============================================================================
		    // When used with a single column name passed as a string:
		    //
		    // Returns results as an array of arrays, where the primary array key names are
		    // set based on the contents of a database column.
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"]="col_3"
		    // RESULT:   array(
		    //		    "dog" =>array( "col_1"=>1, "col_2"=>"red", "col_3"=>"dog", "col_4"=>"big"),
		    //		    "cat" =>array( "col_1"=>2, "col_2"=>"green", "col_3"=>"cat", "col_4"=>"med"),
		    //		    "bird"=>array( "col_1"=>3, "col_2"=>"blue", "col_3"=>"bird", "col_4"=>"small"),
		    //		    "fish"=>array( "col_1"=>4, "col_2"=>"black", "col_3"=>"fish", "col_4"=>"tiny"),
		    //	     )
		    //
		    // When used with multiple column names passed as an array of strings:
		    //
		    // Returns results as an array of arrays^N, where the array key names and heirarchy
		    // are set based on the contents of the $key_col array. This output format REQUIRES
		    // that each taxonomy group (in the example below "col_3" + "col_4") is UNIQUE
		    //
		    // EXAMPLE TABLE "test_table_2"
		    // ======================================
		    //  col_1 | col_2 | col_3 | col_4 | col_5
		    // ======================================
		    //    1   |  red  |  dog  |  big  | heavy
		    //    2   |  green|  dog  |  med  | light
		    //    3   |  blue |  bird | small | light
		    //    4   |  black|  fish |  tiny | average
		    //    5   |  black|  fish | large | light
		    //
		    // UNIQUE KEY col_3_col4(col_3, col_4)
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_3, "col_4")
		    // RESULT:   array(
		    //		    "dog" =>array(
		    //				    "big"=>array("col_1"=>1, "col_2"=>"red", "col_5"=>"heavy"),
		    //				    "med"=>array("col_1"=>2, "col_2"=>"green", "col_5"=>"light")
		    //		    ),
		    //		    "bird"=>array(
		    //				    "small"=>array("col_1"=>3, "col_2"=>"blue", "col_5"=>"light")
		    //		    ),
		    //		    "fish"=>array(
		    //				    "tiny"=>array("col_1"=>4, "col_2"=>"black", "col_5"=>"average"),
		    //				    "large"=>array("col_1"=>5, "col_2"=>"black", "col_5"=>"light")
		    //		    )
		    //	     )

		    case "array_key_array" : {

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>6,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_key_array\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_key_array\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    // If a single column name is passed as a string, use the more efficient
				    // direct assignment algorithm to build a 1 level tree

				    if( !is_array($ctrl["key_col"]) ){

					    foreach($data as $row){

						    $arr = array();

						    // Convert row object into array
						    foreach($row as $key => $value){
							    $arr[$key] = $value;
						    }

						    // Insert row array into primary array as named key
						    $result[$row->{$ctrl["key_col"]}] = $arr;
					    }
				    }

				    // If an array of column names are passed, use the less efficient eval()
				    // algorithm to build a n-level deep tree

				    else {

					    foreach($data as $row){

						    // Since there is no functionality in PHP for creating a new array key
						    // based on a name stored in a variable ( $$ variable variable syntax does
						    // not work for multidimensional arrays), we have to build a string of PHP
						    // code and use eval() to run it

						    $eval_str = "\$result";

						    foreach($ctrl["key_col"] as $keyname){

							    $eval_str .= '["' . $row->{$keyname} . '"]';
						    }

						    $eval_str .= " = \$arr;";

						    $arr = array();

						    // Convert row object into array, skipping keys that are used as the
						    // branch variables

						    foreach($row as $key => $value){

							    // Check if this is a first-order row. If it is, "lift" it up a 
							    // level in the results array to avoid adding an unnecessary "L0"
							    // wrapper array around it
							
							    $order = count( array_keys((array)$row) ) - count($ctrl["key_col"]);
														    
							    if($order > 1){
								
								    if( array_search($key, $ctrl["key_col"]) === false ){
									    $arr[$key] = $value;
								    }
							    }
							    else {
								    $arr = $value;
							    }
						    }

						    // Run the PHP string we have built
						    eval($eval_str);

					    }
				    }
				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = array_key_array\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;


		    // ARRAY_KEY_ARRAY_GROUPED
		    // =============================================================================
		    //
		    // Requires at least TWO column names, and columns not specified in $key_col are not
		    // included in the results set. Returns results as an array of arrays^N-1, where the
		    // array key names and heirarchy are set based on the contents of the $key_col array.
		    // Results in the last db column specified in $key_col are grouped together in an
		    // int-keyed array. The key name corresponds to the order in which a rows is returned
		    // from the database and the value of each key is the column's value in the database
		    // row. For a working example of how to use this result formatter, see the function
		    // database_resultFormatters::test_array_key_array_grouped() in the database unit tests.
		    //
		    // EXAMPLE TABLE "test_table_2"
		    // ===============================
		    //  col_1 | col_2 | col_3 | col_4
		    // ===============================
		    //    1   |  red  |  dog  | A
		    //    2   |  green|  dog  | B
		    //    3   |  green|  dog  | C
		    //    4   |  green|  dog  | D
		    //    5   |  blue |  bird | A
		    //    6   |  black|  bird | A
		    //    7   |  black|  fish | A
		    //    8   |  black|  fish | B
		    //
		    // UNIQUE KEY no_duplicates(col_2, col_3, col_4)
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_2, "col_3", "col_4")
		    // NOTE: "col_1" is excluded because it is not specified in $ctrl["key_col"]
		    // RESULT:   array(
		    //		    "dog" =>array(
		    //				    "red"=>	array("A"),
		    //				    "green"=>	array("B","C","D")
		    //		    ),
		    //		    "bird" =>array(
		    //				    "blue"=>	array("A"),
		    //				    "black"=>	array("A")
		    //		    ),
		    //		    "fish" =>array(
		    //				    "black"=>	array("A","B")
		    //		    )
		    //	     )

		    case "array_key_array_grouped" : {

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>7,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_key_array_grouped\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_key_array_grouped\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    foreach($data as $row){

					    // Since there is no functionality in PHP for creating a new array key
					    // based on a name stored in a variable ( $$ variable variable syntax does
					    // not work for multidimensional arrays), we have to build a string of PHP
					    // code and use eval() to run it

					    $eval_str = "\$result";

					    $idx = sizeof($ctrl["key_col"]) - 1;
					    $grouped_col = $ctrl["key_col"][$idx];

					    foreach($ctrl["key_col"] as $keyname){

						    if($keyname != $grouped_col){
							    $eval_str .= '["' . $row->{$keyname} . '"]';
						    }
						    else {
							    $eval_str .= '[]';
						    }
					    }

					    $eval_str .= " = \$row->" . $grouped_col . ";";

					    // Run the PHP string we have built
					    eval($eval_str);

				    }

				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = array_key_array_grouped\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;


		    // ARRAY_KEY_ARRAY_TRUE
		    // =============================================================================
		    //
		    // Returns results as an array of arrays^N-1, where the array key names and heirarchy
		    // are set based on the contents of the $key_col array. Results in the last db column
		    // specified in $key_col are grouped together in an result-keyed array where the key
		    // name is the column's value in the database row and the key's value is (bool)true.
		    // For a working example of how to use this result formatter, see the function
		    // database_resultFormatters::test_array_key_array_true() in the database unit tests.
		    //
		    // EXAMPLE TABLE "test_table_2"
		    // ===============================
		    //  col_1 | col_2 | col_3 | col_4
		    // ===============================
		    //    1   |  red  |  dog  | A
		    //    2   |  green|  dog  | B
		    //    3   |  green|  dog  | C
		    //    4   |  green|  dog  | D
		    //    5   |  blue |  bird | A
		    //    6   |  black|  bird | A
		    //    7   |  black|  fish | A
		    //    8   |  black|  fish | B
		    //
		    // UNIQUE KEY no_duplicates(col_2, col_3, col_4)
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_2, "col_3", "col_4")
		    // NOTE: "col_1" is excluded because it is not specified in $ctrl["key_col"]
		    // RESULT:   array(
		    //		    "dog" =>array(
		    //				    "red"=>	array("A"=>"true"),
		    //				    "green"=>	array("B"=>"true","C"=>"true","D"=>"true")
		    //		    ),
		    //		    "bird" =>array(
		    //				    "blue"=>	array("A"=>"true"),
		    //				    "black"=>	array("A"=>"true")
		    //		    ),
		    //		    "fish" =>array(
		    //				    "black"=>	array("A"=>"true","B"=>"true")
		    //		    )
		    //	     )

		    case "array_key_array_true" : {

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>8,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_key_array_true\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_key_array_true\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    foreach($data as $row){

					    // Since there is no functionality in PHP for creating a new array key
					    // based on a name stored in a variable ( $$ variable variable syntax does
					    // not work for multidimensional arrays), we have to build a string of PHP
					    // code and use eval() to run it

					    $eval_str = "\$result";

					    foreach($ctrl["key_col"] as $keyname){

						    $eval_str .= '["' . $row->{$keyname} . '"]';
					    }

					    $eval_str .= " = true;";

					    // Run the PHP string we have built
					    eval($eval_str);

				    }

				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = array_key_array_true\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;


		    // ARRAY_KEY_ARRAY_FALSE
		    // =============================================================================
		    //
		    // Returns results as an array of arrays^N-1, where the array key names and heirarchy
		    // are set based on the contents of the $key_col array. Results in the last db column
		    // specified in $key_col are grouped together in an result-keyed array where the key
		    // name is the column's value in the database row and the key's value is (bool)false.
		    // For a working example of how to use this result formatter, see the function
		    // database_resultFormatters::test_array_key_array_false() in the database unit tests.
		    //
		    // EXAMPLE TABLE "test_table_2"
		    // ===============================
		    //  col_1 | col_2 | col_3 | col_4
		    // ===============================
		    //    1   |  red  |  dog  | A
		    //    2   |  green|  dog  | B
		    //    3   |  green|  dog  | C
		    //    4   |  green|  dog  | D
		    //    5   |  blue |  bird | A
		    //    6   |  black|  bird | A
		    //    7   |  black|  fish | A
		    //    8   |  black|  fish | B
		    //
		    // UNIQUE KEY no_duplicates(col_2, col_3, col_4)
		    //
		    // EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_2, "col_3", "col_4")
		    // NOTE: "col_1" is excluded because it is not specified in $ctrl["key_col"]
		    // RESULT:   array(
		    //		    "dog" =>array(
		    //				    "red"=>	array("A"=>"false"),
		    //				    "green"=>	array("B"=>"false","C"=>"false","D"=>"false")
		    //		    ),
		    //		    "bird" =>array(
		    //				    "blue"=>	array("A"=>"false"),
		    //				    "black"=>	array("A"=>"false")
		    //		    ),
		    //		    "fish" =>array(
		    //				    "black"=>	array("A"=>"false","B"=>"false")
		    //		    )
		    //	     )

		    case "array_key_array_false" : {

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>9,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_key_array_false";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_key_array_false";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    foreach($data as $row){

					    // Since there is no functionality in PHP for creating a new array key
					    // based on a name stored in a variable ( $$ variable variable syntax does
					    // not work for multidimensional arrays), we have to build a string of PHP
					    // code and use eval() to run it

					    $eval_str = "\$result";

					    foreach($ctrl["key_col"] as $keyname){

						    $eval_str .= '["' . $row->{$keyname} . '"]';
					    }

					    $eval_str .= " = false;";

					    // Run the PHP string we have built
					    eval($eval_str);

				    }

				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = array_key_array_false";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;



		    // ARRAY_KEY_SINGLE
		    // =============================================================================
		    // Returns results as an array of ints or strings, where the array key names
		    // are set based on the contents of a database column.
		    //
		    // EXAMPLE: "SELECT col_2, col_3 FROM test_table" + $ctrl["key_col"]="col_2", $ctrl["val_col"]="col_3"
		    // RESULT:   array(
		    //		     "red"=>"dog",
		    //		     "green"=>"cat",
		    //		     "blue"=>"bird",
		    //		     "black"=>"fish"
		    //	     )

		    case "array_key_single" : {

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>10,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_key_single\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_key_single\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    foreach($data as $row){

					    $result[$row->{$ctrl["key_col"]}] = $row->{$ctrl["val_col"]};
				    }

				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nCAST, format = array_key_single\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;


		    // ARRAY_OBJECT
		    // =============================================================================
		    // Returns results as an array of objects, where the primary array keys are
		    // zero-indexed ints
		    //
		    // EXAMPLE: "SELECT * FROM test_table"
		    // RESULT:   array(
		    //		    stdClass( "col_1"->1, "col_2"->"red", "col_3"->"dog", "col_4"->"big"),
		    //		    stdClass( "col_1"->2, "col_2"->"green", "col_3"->"cat", "col_4"->"med"),
		    //		    stdClass( "col_1"->3, "col_2"->"blue", "col_3"->"bird", "col_4"->"small"),
		    //		    stdClass( "col_1"->4, "col_2"->"black", "col_3"->"fish", "col_4"->"tiny"),
		    //	     )

		    case "array_object" : {

			    try {
				    $result = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>11,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_object\n";
				    print_r($result);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $result, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_object\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($this->parent->print_result_formatted == true){

				    ob_start();
				    print_r($result);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }


		    } break;


		    // ARRAY_ARRAY
		    // =============================================================================
		    // Returns results as an array of arrays, where the primary array keys are
		    // zero-indexed ints
		    //
		    // EXAMPLE: "SELECT * FROM test_table"
		    // RESULT:   array(
		    //		    array( "col_1"=>1, "col_2"=>"red", "col_3"=>"dog", "col_4"=>"big"),
		    //		    array( "col_1"=>2, "col_2"=>"green", "col_3"=>"cat", "col_4"=>"med"),
		    //		    array( "col_1"=>3, "col_2"=>"blue", "col_3"=>"bird", "col_4"=>"small"),
		    //		    array( "col_1"=>4, "col_2"=>"black", "col_3"=>"fish", "col_4"=>"tiny"),
		    //	     )

		    case "array_array" : {

			    try {
				    $data = $this->parent->driver->get_results($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>12,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){
				    ob_start();
				    echo "RAW, format = array_array\n";
				    print_r($data);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

			    if($this->parent->disable_typecast_read == false){

				    $cast->queryResult($format="array_object", $data, $query["types"]);

				    if($this->parent->print_result_cast == true){
					    ob_start();
					    echo "\nCAST, format = array_array\n";
					    print_r($data);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }
			    }

			    if($data){

				    $result = array();

				    foreach($data as $row){

					    $arr = array();

					    // Convert row object into array
					    foreach($row as $key => $value){

						    $arr[$key] = $value;
					    }

					    // Insert row array into primary array as unnamed key
					    $result[] = $arr;
				    }

				    if($this->parent->print_result_formatted == true){
					    ob_start();
					    echo "\nFORMATTED, format = array_array\n";
					    print_r($result);
					    $out = ob_get_clean();
					    FOX_debug::addToFile($out);
				    }

				    unset($data); // Reduce memory usage
			    }

		    } break;

		    
		    // RAW
		    // =============================================================================
		    // Runs a default SQL query. Returned result format depends on the query.

		    case "raw" : {			    			   			   

			    try {
				    $result = $this->parent->driver->query($sql);
			    }    
			    catch (FOX_exception $child) {

				    throw new FOX_exception( array(
					    'numeric'=>13,
					    'text'=>"Error in database driver",
					    'data'=>array('query'=>$query, 'sql'=>$sql),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
				    ));		    
			    }
			    
			    if($this->parent->print_result_raw == true){

				    ob_start();
				    echo "format = null\n";
				    print_r($result);
				    $out = ob_get_clean();
				    FOX_debug::addToFile($out);
			    }

		    } break;
		    
		    default : {
			
			    throw new FOX_exception( array(
				    'numeric'=>14,
				    'text'=>"Invalid query runner format",				
				    'data'=>array('faulting_format'=>$ctrl["format"],"query"=>$query, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
			    ));
			
		    }


		} // END switch($ctrl["format"])


		return $result;


	}


} // End of class FOX_queryRunner

?>