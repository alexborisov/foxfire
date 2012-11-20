<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DATABASE - UPDATE
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

class database_runUpdateQuery extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runUpdateQuery",
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


	function test_runUpdateQuery(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		// Load table with data
		// ========================================================
		
		$data_insert_01 = array(
			array( "name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333 )
		);

		try {
			$columns = array("mode"=>"exclude", "col"=>"id");
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		// Check table is in correct state
		// ========================================================
		
		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		$data_check_01->priv = "1";
		$data_check_01->files = "222";
		$data_check_01->space = "3333";
		
		try {
			$columns = array("mode"=>"exclude", "col"=>"id");
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals($data_check_01, $result[0]);

		// Update a row
		// ========================================================
		
		$data_update = array( "name"=>"data_01", "text"=>"Test Text String 02", "priv"=>3, "files"=>444, "space"=>5555 );
			
		try {
			$args = array(
				array("col"=>"name", "op"=>"=", "val"=>"data_01")
			);

			$columns = array("mode"=>"exclude", "col"=>"space");

			$this->tdb->runUpdateQuery(self::$struct, $data_update, $args, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Check table is in correct state
		// ========================================================
		
		$data_check_02 = new stdClass();
		$data_check_02->name = "data_01";
		$data_check_02->text = "Test Text String 02";
		$data_check_02->priv = "3";
		$data_check_02->files = "444";
		$data_check_02->space = "3333";

		try {
			$columns = array("mode"=>"exclude", "col"=>"id");
			$ctrl = array("format"=>"array_object");

			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);

		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals($data_check_02, $result[0]);

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


class database_runUpdateQuery_rowsAffected extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runUpdateQuery",
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


	function test_runUpdateQuery(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		// Load table with data
		// ========================================================
		
		$data_insert = array(
			array( "name"=>"data_01",   "text"=>"Test Text String 01",  "priv"=>1,	"files"=>1101,	"space"=>11117 ),
			array( "name"=>"data_02",   "text"=>"Test Text String 02",  "priv"=>1,	"files"=>2220,	"space"=>22272 ),
			array( "name"=>"data_03",   "text"=>"Test Text String 03",  "priv"=>1,	"files"=>3033,	"space"=>33733 ),
			array( "name"=>"data_04",   "text"=>"Test Text String 04",  "priv"=>1,	"files"=>0444,	"space"=>47444 ),
			array( "name"=>"data_05",   "text"=>"Test Text String 05",  "priv"=>1,	"files"=>5505,	"space"=>55755 )
		);

		try {
			$columns = array("mode"=>"exclude", "col"=>"id");
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		// Update all rows in the table (no need to verify state, 
		// we're only checking the number of rows affected)
		// ========================================================
		
		$data_update = array("files" => "9999");

		try {
			$args = array(
				array("col"=>"priv", "op"=>"=", "val"=>1)
			);

			$rows_changed = $this->tdb->runUpdateQuery(self::$struct, $data_update, $args, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(5, $rows_changed);
				

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



class database_runUpdateQueryCol extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runUpdateQueryCol",
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


	function test_runUpdateQueryCol(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_insert_01 = array(
			array( "name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333 )
		);

		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		$data_check_01->priv = "1";
		$data_check_01->files = "222";
		$data_check_01->space = "3333";

		try {
			$columns = array("mode"=>"exclude", "col"=>"id");
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		try {
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals($data_check_01, $result[0]);


		$data_update = array( "name"=>"data_01", "text"=>"Test Text String 02", "priv"=>3, "files"=>444, "space"=>5555 );

		try {
			$columns = array("mode"=>"exclude", "col"=>"space");
			$this->tdb->runUpdateQueryCol(self::$struct, $data_update, 'name', "=", 'data_01', $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$data_check_02 = new stdClass();
		$data_check_02->name = "data_01";
		$data_check_02->text = "Test Text String 02";
		$data_check_02->priv = "3";
		$data_check_02->files = "444";
		$data_check_02->space = "3333";

		try {
			$columns = array("mode"=>"exclude", "col"=>"id");
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals($data_check_02, $result[0]);

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