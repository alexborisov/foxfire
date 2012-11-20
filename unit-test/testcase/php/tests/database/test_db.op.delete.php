<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DATABASE - DELETE
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

class database_runDeleteQuery extends RAZ_testCase {

    
	static $struct = array(

		"table" => "fox_test_runDeleteQuery",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	"index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
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

					$this->fail("Table already existed. Failure while clearing table. Error code: " . $child->data['numeric']);
				}
			    
			}
			
			// CASE 2: something is seriously wrong with the database. Abort.
			// ===============================================================================			
			else {
				$this->fail("Failure while adding table to database. Error code: " . $fail->data['numeric']);				
			}
			
		}
		
	}


	function test_runDeleteQuery(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$data_insert = array(		    
			array(	"name"=>"data_01",  "text"=>"Test Text String 01",  "priv"=>1,	"files"=>222,	"space"=>3333),
			array(	"name"=>"data_02",  "text"=>"Test Text String 02",  "priv"=>2,	"files"=>333,	"space"=>4444)		    		    		    
		);

		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		$data_check_01->priv = "1";
		$data_check_01->files = "222";
		$data_check_01->space = "3333";

		$data_check_02 = new stdClass();
		$data_check_02->name = "data_02";
		$data_check_02->text = "Test Text String 02";
		$data_check_02->priv = "2";
		$data_check_02->files = "333";
		$data_check_02->space = "4444";

		$check_array = array($data_check_01, $data_check_02);

		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		// Verify database is in correct state
		// ========================================================
		
		$ctrl = array("format"=>"array_object");

		try {
			$result = $this->tdb->runSelectQuery(self::$struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$this->assertEquals($check_array, $result);

		// Delete rows
		// ========================================================
		
		$args = array(
			array("col"=>"name", "op"=>"=", "val"=>"data_01")
		);

		try {
			$this->tdb->runDeleteQuery(self::$struct, $args);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Verify database is in correct state
		// ========================================================	
		
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQuery(self::$struct, $args=null, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$data_check_03 = new stdClass();
		$data_check_03->name = "data_02";
		$data_check_03->text = "Test Text String 02";
		$data_check_03->priv = "2";
		$data_check_03->files = "333";
		$data_check_03->space = "4444";

		$check_array = array($data_check_03);

		$this->assertEquals($check_array, $result);

	}


	function tearDown() {

		$tdb = new FOX_db();
		$tdb->runDropTable(self::$struct);
		parent::tearDown();
	}

}



class database_runDeleteQueryCol extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runDeleteQueryCol",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	"index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
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

					$this->fail("Table already existed. Failure while clearing table. Error code: " . $child->data['numeric']);
				}
			    
			}
			
			// CASE 2: something is seriously wrong with the database. Abort.
			// ===============================================================================			
			else {
				$this->fail("Failure while adding table to database. Error code: " . $fail->data['numeric']);				
			}
			
		}
		
	}


	function test_runDeleteQueryCol(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$data_insert = array(		    
			array(	"name"=>"data_01",  "text"=>"Test Text String 01",  "priv"=>1,	"files"=>222,	"space"=>3333),
			array(	"name"=>"data_02",  "text"=>"Test Text String 02",  "priv"=>2,	"files"=>333,	"space"=>4444)		    		    		    
		);		

		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		$data_check_01->priv = "1";
		$data_check_01->files = "222";
		$data_check_01->space = "3333";

		$data_check_02 = new stdClass();
		$data_check_02->name = "data_02";
		$data_check_02->text = "Test Text String 02";
		$data_check_02->priv = "2";
		$data_check_02->files = "333";
		$data_check_02->space = "4444";

		$check_array = array($data_check_01, $data_check_02);
		
		
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		
		// Verify database is in correct state
		// ========================================================
		
		$ctrl = array("format"=>"array_object");

		try {
			$result = $this->tdb->runSelectQuery(self::$struct, null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$this->assertEquals($check_array, $result);
		

		// Delete rows
		// ========================================================

		try {
			$this->tdb->runDeleteQueryCol(self::$struct, "name", "=", "data_01");
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		// Verify database is in correct state
		// ========================================================	
		
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQuery(self::$struct, null, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$data_check_03 = new stdClass();
		$data_check_03->name = "data_02";
		$data_check_03->text = "Test Text String 02";
		$data_check_03->priv = "2";
		$data_check_03->files = "333";
		$data_check_03->space = "4444";

		$check_array = array($data_check_03);

		$this->assertEquals($check_array, $result);
		
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