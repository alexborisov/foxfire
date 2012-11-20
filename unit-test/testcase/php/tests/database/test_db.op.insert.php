<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DATABASE - INSERT
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

class database_runInsertQuery extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runInsertQuery",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>true),
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


	function test_runInsertQuery(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_insert_01 = array( "name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333 );

		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		$data_check_01->priv = "1";
		$data_check_01->files = "222";
		$data_check_01->space = "3333";

		$columns = array("mode"=>"exclude", "col"=>"id");
		
		try {
			$rows_affected = $this->tdb->runInsertQuery(self::$struct, $data_insert_01, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// The database should return (int)1 to show that 1 row was affected by this query
		
		$this->assertEquals(1, $rows_affected);
			
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals($data_check_01, $result[0]);

		
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


class database_runInsertQueryMulti extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runInsertQueryMulti",
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


	function test_runInsertQueryMulti(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		
		$data_insert = array(
			array(	"id"=>1,    "name"=>"data_01",	"text"=>"Test Text String 01",	"priv"=>1,  "files"=>1101,  "space"=>11117),
			array(	"id"=>2,    "name"=>"data_02",	"text"=>"Test Text String 02",	"priv"=>1,  "files"=>2220,  "space"=>22272),
			array(	"id"=>3,    "name"=>"data_03",	"text"=>"Test Text String 03",	"priv"=>1,  "files"=>3033,  "space"=>33733),
			array(	"id"=>4,    "name"=>"data_04",	"text"=>"Test Text String 04",	"priv"=>1,  "files"=>0444,  "space"=>47444),
			array(	"id"=>5,    "name"=>"data_05",	"text"=>"Test Text String 05",	"priv"=>1,  "files"=>5505,  "space"=>55755)
		);


		$data_check = array();

		foreach($data_insert as $row){

			$row_obj = new stdClass();

			foreach($row as $key => $value){

				$row_obj->{$key} = $value;
			}

			$data_check[] = $row_obj;
		}

		try {
			$rows_affected = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(5, $rows_affected);
		
		
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", '1', $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}				

		$this->assertEquals($data_check, $result);

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


class database_runInsertQueryMultiMultiAutoInc extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runInsertQueryMulti",
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


	function test_runInsertQueryMulti(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_insert_first = array(
			array("name" => "data_01", "text" => "Test Text String 01", "priv" => 1, "files" => 1101, "space" => 11117),
			array("name" => "data_02", "text" => "Test Text String 02", "priv" => 1, "files" => 2220, "space" => 22272),
			array("name" => "data_03", "text" => "Test Text String 03", "priv" => 1, "files" => 3033, "space" => 33733),
			array("name" => "data_04", "text" => "Test Text String 04", "priv" => 1, "files" => 0444, "space" => 47444),
			array("name" => "data_05", "text" => "Test Text String 05", "priv" => 1, "files" => 5505, "space" => 55755)
		);
		
		$data_insert_second = array(
			array("name" => "data_06", "text" => "Test Text String 06", "priv" => 1, "files" => 1101, "space" => 11117),
			array("name" => "data_07", "text" => "Test Text String 07", "priv" => 1, "files" => 2220, "space" => 22272),
			array("name" => "data_08", "text" => "Test Text String 08", "priv" => 1, "files" => 3033, "space" => 33733),
		);		

		$data_combined = array_merge($data_insert_first, $data_insert_second);

		$data_check = array();

		foreach($data_combined as $row){

			$row_obj = new stdClass();

			foreach($row as $key => $value){

				$row_obj->{$key} = $value;
			}

			$data_check[] = $row_obj;
		}

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_first, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}				
		
		// The insert_id should be the id of the FIRST row inserted
		$this->assertEquals(1, $this->tdb->insert_id);
		
		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_second, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
						
		// The insert_id should be the id of the FIRST row inserted
		$this->assertEquals(6, $this->tdb->insert_id);		

		$ctrl = array("format"=>"array_object");

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", '1', $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}				

		$this->assertEquals($data_check, $result);

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