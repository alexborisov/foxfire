<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DB SQL/PHP TYPE CASTING CLASS
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

// TODO: Update to include tests for the new config class typecasters

class database_typeCasters extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_typeCast_SQLtoPHP",
		"engine" => "MyISAM",
		"columns" => array(
		    "col_1" =>	array(	"php"=>"bool",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false, "default"=>null,  "index"=>"PRIMARY"),
		    "col_2" =>	array(	"php"=>"bool",	    "sql"=>"varchar",	"format"=>"%s", "width"=>1,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_3" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_4" =>	array(	"php"=>"int",	    "sql"=>"varchar",	"format"=>"%s", "width"=>16,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_5" =>	array(	"php"=>"float",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_6" =>	array(	"php"=>"float",	    "sql"=>"varchar",	"format"=>"%s", "width"=>16,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_7" =>	array(	"php"=>"string",    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_8" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>16,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_9" =>	array(	"php"=>"array",	    "sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_10" =>	array(	"php"=>"array",	    "sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_11" =>	array(	"php"=>"array",	    "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_12" =>	array(	"php"=>"int",	    "sql"=>"date",	"format"=>"%s", "width"=>null,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		    "col_13" =>	array(	"php"=>"int",	    "sql"=>"datetime",	"format"=>"%s", "width"=>null,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false),
		 )
	);


    	function setUp() {

		parent::setUp();
		$this->tdb = new FOX_db();

		try {
			$this->tdb->runAddTable(self::$struct);			
		}
		catch (FOX_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct);
				}
				catch (FOX_exception $child) {

					$this->fail($child->dumpString( array('depth'=>10, 'data'=>true)) );	
				}
			    
			}
			
			// CASE 2: something is seriously wrong with the database. Abort.
			// ===============================================================================			
			else {
				$this->fail($child->dumpString(10));				
			}
			
		}
		
	}


	function test_dataOut(){

		$input_data = array(
				    array(
					"col_1"=>1,
					"col_2"=>"1",
					"col_3"=>17384,
					"col_4"=>29411,
					"col_5"=>4871312,
					"col_6"=>"43.71175",
					"col_7"=>32871,
					"col_8"=>"test_string",
					"col_9"=>  array("val_1"=>1, "val_2"=>"test_01", "val_3"=>17.239),
					"col_10"=> array("val_1"=>(string)1, "val_2"=>(string)-23580, "val_3"=>(string)17.239),
					"col_11"=> array("val_1"=>(float)2, "val_2"=>(float)-27450, "val_3"=>(float)-26.17239),
					"col_12"=> (int)mktime(0, 0, 0, 11, 21, 2010),
					"col_13"=> (int)mktime(18, 44, 52, 11, 21, 2010),
				    )
		);

		$check_data = new stdClass();

		$check_data->col_1 = true;
		$check_data->col_2 = true;
		$check_data->col_3 = (int)17384;
		$check_data->col_4 = (int)29411;
		$check_data->col_5 = (float)4871312;
		$check_data->col_6 = (float)43.71175;
		$check_data->col_7 = (string)"32871";
		$check_data->col_8 = (string)"test_string";
		$check_data->col_9 = array("val_1"=>1, "val_2"=>"test_01", "val_3"=>17.239);
		$check_data->col_10 = array("val_1"=>(string)1, "val_2"=>(string)-23580, "val_3"=>(string)17.239);
		$check_data->col_11 = array("val_1"=>(float)2, "val_2"=>(float)-27450, "val_3"=>(float)-26.17239);
		$check_data->col_12 = (int)mktime(0, 0, 0, 11, 21, 2010);
		$check_data->col_13 = (int)mktime(18, 44, 52, 11, 21, 2010);

		// Set the disable_typecast flags to prevent FOX_db from automatically typecasting data
		// written to / read from the datyabase

		$this->tdb->disable_typecast_write = true;
		$this->tdb->disable_typecast_read = true;

		// Load the test data into the database. Because $disable_typecast_write is set it will be
		// stored *exactly* as sent in to the db
		
		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $input_data, $columns=null, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
					
		$this->assertEquals(1, $result, 'runInsertQueryMulti() reported adding wrong number of rows');
		
		
		// Get the "types" array for the test data by running the query's "builder" function and extracting it from the result
		
		try {
			$result = $this->tdb->builder->buildSelectQueryCol(self::$struct, $col = 'col_1', $op = "=", $val = '1', $columns=null, 
									   $ctrl=array("format"=>"row_object"));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$types = $result["types"];

		// Fetch the test data into the database. Because $disable_typecast_read is set it will be
		// returned *exactly* as stored in the db
		
		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, $col = 'col_1', $op = "=", $val = '1', $columns=null, 
								$ctrl=array("format"=>"row_object"));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Run the returned test data through the query result typecaster, then compare the result to the check array.
		
		$cst = new FOX_cast();
		
		try {
			$cst->queryResult($format="row_object", $result, $types);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals($check_data, $result);
		
		
	}


	function test_dataIn(){		
	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}	    

		$input_data = array(
				    array(
					"col_1"=>1,
					"col_2"=>"1",
					"col_3"=>17384,
					"col_4"=>29411,
					"col_5"=>4871312,
					"col_6"=>"43.71175",
					"col_7"=>32871,
					"col_8"=>"test_string",
					"col_9"=>  array("val_1"=>1, "val_2"=>"test_01", "val_3"=>17.239),
					"col_10"=> array("val_1"=>(string)1, "val_2"=>(string)-23580, "val_3"=>(string)17.239),
					"col_11"=> array("val_1"=>(float)2, "val_2"=>(float)-27450, "val_3"=>(float)-26.17239),
					"col_12"=> (int)mktime(0, 0, 0, 11, 21, 2010),
					"col_13"=> (int)mktime(18, 44, 52, 11, 21, 2010),
				    )
		);

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $input_data, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}	
		
		
		$this->assertEquals(1, $result, 'runInsertQueryMulti() reported adding wrong number of rows');
		

		// Disable typecasting on data returned from the query
		$this->tdb->disable_typecast_read = true;

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, $col = 'col_1', $op = "=", $val = '1', $columns=null, 
								$ctrl=array("format"=>"row_object"));
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		

		$check_data_1 = new stdClass();

		$check_data_1->col_1 = "1";
		$check_data_1->col_2 = "1";
		$check_data_1->col_3 = "17384";
		$check_data_1->col_4 = "29411";
		$check_data_1->col_5 = "4871312";
		$check_data_1->col_6 = "43.71175";
		$check_data_1->col_7 = "32871";
		$check_data_1->col_8 = "test_string";
		$check_data_1->col_9 = (string)'a:3:{s:5:"val_1";i:1;s:5:"val_2";s:7:"test_01";s:5:"val_3";d:17.239000000000000767386154620908200740814208984375;}';
		$check_data_1->col_10 = (string)'a:3:{s:5:"val_1";s:1:"1";s:5:"val_2";s:6:"-23580";s:5:"val_3";s:6:"17.239";}';
		$check_data_1->col_11 = (string)'a:3:{s:5:"val_1";d:2;s:5:"val_2";d:-27450;s:5:"val_3";d:-26.17239000000000004320099833421409130096435546875;}';
		$check_data_1->col_12 = "2010-11-21";
		$check_data_1->col_13 = "2010-11-21 18:44:52";

		$this->assertEquals($check_data_1, $result);


		// Re-enable typecasting on data returned from the query
		$this->tdb->disable_typecast_read = false;

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, $col = 'col_1', $op = "=", $val = '1', $columns=null, 
								$ctrl=array("format"=>"row_array"));
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$check_data_2 = array(

			"col_1"=>true,
			"col_2"=>true,
			"col_3"=>(int)17384,
			"col_4"=>(int)29411,
			"col_5"=>(float)4871312,
			"col_6"=>(float)43.71175,
			"col_7"=>(string)"32871",
			"col_8"=>(string)"test_string",
			"col_9"=> array("val_1"=>1, "val_2"=>"test_01", "val_3"=>17.239),
			"col_10"=> array("val_1"=>(string)1, "val_2"=>(string)-23580, "val_3"=>(string)17.239),
			"col_11"=> array("val_1"=>(float)2, "val_2"=>(float)-27450, "val_3"=>(float)-26.17239),
			"col_12"=> mktime(0, 0, 0, 11, 21, 2010),
			"col_13"=> mktime(18, 44, 52, 11, 21, 2010)
		);

		$this->assertEquals($check_data_2, $result);
		
	}


	function tearDown() {

		$this->tdb = new FOX_db();
		
		try {
			$this->tdb->runDropTable(self::$struct);
		}
		catch (FOX_exception $child) {
		    		    
			$this->fail("Error while dropping database table. Error code: " . $child->data['numeric']);			    
		}		

		parent::tearDown();
		
	}


	
}

?>