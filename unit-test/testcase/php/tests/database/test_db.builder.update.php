<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DB QUERY STRING BUILDERS
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class database_queryBuilders_update extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new BPM_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new BPM_queryBuilder($test_db);

	}


	function test_buildUpdateQuery(){


		$struct = array(

			"table" => "test_a",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
			 )
		);


		
		$table = $this->base_prefix . $struct["table"];


		// #### ARRAY MODE ################################################################


		// Array as data source
		// ============================================================================================

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31'
		);

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_2 = %s WHERE 1 = 1";

		$check_array = array(17, "s_31");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, single column using INCLUDE mode
		// ============================================================================================

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31',
			'col_3'=>'s_19'
		);

		$columns = array("mode"=>"include", "col"=>"col_2");

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_2 = %s WHERE 1 = 1";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, multiple columns using INCLUDE mode
		// ============================================================================================

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31',
			'col_3'=>'s_19'
		);

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_3 = %s WHERE 1 = 1";

		$check_array = array(17, "s_19");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, single column using EXCLUDE mode
		// ============================================================================================

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31',
			'col_3'=>'s_19'
		);

		$columns = array("mode"=>"exclude", "col"=>"col_1");

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_2 = %s, col_3 = %s WHERE 1 = 1";

		$check_array = array("s_31", "s_19");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);



		// Array as data source, multiple columns using EXCLUDE mode
		// ============================================================================================

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31',
			'col_3'=>'s_19'
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_2 = %s WHERE 1 = 1";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, with constraints
		// ============================================================================================

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31'
		);

		$args = array(

			array("col"=>"col_1", "op"=>"=", "val"=>53)
		);

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_2 = %s WHERE 1 = 1 AND col_1 = %d";

		$check_array = array(17, "s_31", 53);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args, $columns=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// #### OBJECT MODE ################################################################


		// Object as data source
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_2 = %s, col_3 = %s WHERE 1 = 1";

		$check_array = array(17, "s_31", null);
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);


		// Object as data source, single column using INCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"include", "col"=>"col_2");

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_2 = %s WHERE 1 = 1";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);


		// Object as data source, multiple columns using INCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_3 = %s WHERE 1 = 1";

		$check_array = array(17, "s_19");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Object as data source, single column using EXCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"exclude", "col"=>"col_2");

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_3 = %s WHERE 1 = 1";

		$check_array = array(17, "s_19");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Object as data source, multiple columns using EXCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_2 = %s WHERE 1 = 1";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args="overwrite_all", $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Object as data source, with constraints
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";

		$args = array(
			    array("col"=>"col_1", "op"=>"=", "val"=>11)
		);

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_1 = %d, col_2 = %s, col_3 = %s WHERE 1 = 1 AND col_1 = %d";

		$check_array = array(17, "s_31", null, 11);
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQuery($struct, $data, $args, $columns=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);
		

	}



	function test_buildUpdateQueryCol(){


		// This method is essentially a pass-through function to buildUpdateQuery(), so very little testing is required
		// --------------------------------------------------------------------------------------------------------------

		$struct = array(

			"table" => "test_a",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
			 )
		);


		
		$table = $this->base_prefix . $struct["table"];

		$data = array(
			'col_1'=>17,
			'col_2'=>'s_31'
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1") );

		$check_args = array();
		$check_args[0] = "UPDATE {$table} SET col_2 = %s WHERE 1 = 1 AND col_1 <> %d";

		$check_array = array("s_31", 37);
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildUpdateQueryCol($struct, $data, "col_1", "<>", 37, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
				
		$this->assertEquals($check_args, $result["query"]);
		

	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	


}

?>