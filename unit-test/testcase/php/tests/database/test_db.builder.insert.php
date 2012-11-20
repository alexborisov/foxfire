<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DB QUERY STRING BUILDERS
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class database_queryBuilders_insert extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new FOX_queryBuilder($test_db);

	}


	function test_buildInsertQuery(){

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
		    
			array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53')
		);

		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_1, col_2, col_3) VALUES (%d, %s, %s)";

		$check_array = array(17, "s_31", "s_53");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, single column using INCLUDE mode
		// ============================================================================================

		$data = array(

			array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
		);

		$columns = array("mode"=>"include", "col"=>"col_2");

		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_2) VALUES (%s)";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, multiple columns using INCLUDE mode
		// ============================================================================================

		$data = array(

			array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
		);

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_1, col_3) VALUES (%d, %s)";

		$check_array = array(17, "s_19");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, single column using EXCLUDE mode
		// ============================================================================================

		$data = array(

			array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
		);

		$columns = array("mode"=>"exclude", "col"=>"col_1");

		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_2, col_3) VALUES (%s, %s)";

		$check_array = array("s_31", "s_19");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}	

		$this->assertEquals($check_args, $result["query"]);


		// Array as data source, multiple columns using EXCLUDE mode
		// ============================================================================================

		$data = array(

			array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_2) VALUES (%s)";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}	

		$this->assertEquals($check_args, $result["query"]);
		

		// Array as data source, multiple inserts in single query
		// ============================================================================================

		$data = array(

			array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53'),
			array('col_1'=>94, 'col_2'=>'s_66', 'col_3'=>'s_81'),
			array('col_1'=>21, 'col_2'=>'s_13', 'col_3'=>'s_42')
		);


		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_1, col_2, col_3) VALUES (%d, %s, %s), (%d, %s, %s), (%d, %s, %s)";

		$check_array = array(17, "s_31", "s_53", 94, "s_66", "s_81", 21, "s_13", "s_42");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns=null);
		}
		catch (FOX_exception $child) {
		    
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
		$check_args[0] = "INSERT INTO {$table} (col_1, col_2, col_3) VALUES (%d, %s, %s)";

		$check_array = array(17, "s_31", null);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns=null);
		}
		catch (FOX_exception $child) {
		    
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
		$check_args[0] = "INSERT INTO {$table} (col_2) VALUES (%s)";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
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
		$check_args[0] = "INSERT INTO {$table} (col_1, col_3) VALUES (%d, %s)";

		$check_array = array(17, "s_19");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Object as data source, single column using EXCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"exclude", "col"=>"col_1");

		$check_args = array();
		$check_args[0] = "INSERT INTO {$table} (col_2, col_3) VALUES (%s, %s)";

		$check_array = array("s_31", "s_19");
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
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
		$check_args[0] = "INSERT INTO {$table} (col_2) VALUES (%s)";

		$check_array = array("s_31");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildInsertQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]);
		

	}

	
	function tearDown() {	

		parent::tearDown();		
	}	

}

?>