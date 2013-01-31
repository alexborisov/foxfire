<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DB QUERY STRING BUILDERS
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


class database_queryBuilders_indate extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new FOX_queryBuilder($test_db);

	}

	
	function test_buildIndateQuery(){

	    
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

		$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53');

		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_1, col_2, col_3) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE col_1 = %d, col_2 = %s, col_3 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>17),	
					    array('escape'=>true, 'val'=>'s_31'),
					    array('escape'=>true, 'val'=>'s_53'),	
					    array('escape'=>true, 'val'=>17),
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_53'),			    
			)		    
		);		
		
		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result);


		// Array as data source, single column using INCLUDE mode
		// ============================================================================================

		$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

		$columns = array("mode"=>"include", "col"=>"col_2");
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_2) VALUES (%s) ON DUPLICATE KEY UPDATE col_2 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_31'),			    
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result);


		// Array as data source, multiple columns using INCLUDE mode
		// ============================================================================================

		$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_1, col_3) VALUES (%d, %s) ON DUPLICATE KEY UPDATE col_1 = %d, col_3 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>17),	
					    array('escape'=>true, 'val'=>'s_19'),
					    array('escape'=>true, 'val'=>17),	
					    array('escape'=>true, 'val'=>'s_19'),			    
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result);


		// Array as data source, single column using EXCLUDE mode
		// ============================================================================================

		$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

		$columns = array("mode"=>"exclude", "col"=>"col_1");
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_2, col_3) VALUES (%s, %s) ON DUPLICATE KEY UPDATE col_2 = %s, col_3 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_19'),
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_19'),			    
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result);


		// Array as data source, multiple columns using EXCLUDE mode
		// ============================================================================================

		$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_2) VALUES (%s) ON DUPLICATE KEY UPDATE col_2 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_31'),			    
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result);
		

		// #### OBJECT MODE ################################################################


		// Object as data source
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_1, col_2, col_3) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE col_1 = %d, col_2 = %s, col_3 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>17),	
					    array('escape'=>true, 'val'=>'s_31'),
					    array('escape'=>true, 'val'=>null),	
					    array('escape'=>true, 'val'=>17),
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>null),			    
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result);


		// Object as data source, single column using INCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"include", "col"=>"col_2");
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_2) VALUES (%s) ON DUPLICATE KEY UPDATE col_2 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_31'),		    
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result);


		// Object as data source, multiple columns using INCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_1, col_3) VALUES (%d, %s) ON DUPLICATE KEY UPDATE col_1 = %d, col_3 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>17),	
					    array('escape'=>true, 'val'=>'s_19'),	
					    array('escape'=>true, 'val'=>17),	
					    array('escape'=>true, 'val'=>'s_19'),
			)		    
		);			

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result);


		// Object as data source, single column using EXCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"exclude", "col"=>"col_1");
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_2, col_3) VALUES (%s, %s) ON DUPLICATE KEY UPDATE col_2 = %s, col_3 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_19'),	
					    array('escape'=>true, 'val'=>'s_31'),	
					    array('escape'=>true, 'val'=>'s_19'),
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result);


		// Object as data source, multiple columns using EXCLUDE mode
		// ============================================================================================

		$data = new stdClass();
		$data->col_1 = 17;
		$data->col_2 = "s_31";
		$data->col_3 = "s_19";

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );
		
		$check_args = array(
		    
			'query'=> "INSERT INTO {$table} (col_2) VALUES (%s) ON DUPLICATE KEY UPDATE col_2 = %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>'s_31'),		
					    array('escape'=>true, 'val'=>'s_31'),
			)		    
		);		

		try {
			$result = $this->builder->buildIndateQuery($struct, $data, $columns);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result);
		

	}

	
	function tearDown() {	

		parent::tearDown();		
	}	

}

?>