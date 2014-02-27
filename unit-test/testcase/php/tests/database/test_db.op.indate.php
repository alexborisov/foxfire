<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DATABASE - INDATE
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

class database_runIndateQuerySingle extends RAZ_testCase {


	static $struct = array(

		"table" => "fox_test_runIndateQuery",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"",	     "auto_inc"=>false, "default"=>null,  "index"=>false),
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


	function test_runIndateQuery(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		// Indate first item using auto-increment, id should be "1"
		
		$data_insert_01 = array("name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333 );

		try {
			$result = $this->tdb->runIndateQuery(self::$struct, $data_insert_01, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
				
		// The database should return (int)1 to show 1 row was affected
		
		$this->assertEquals(1, $result);

		try {
			$result = $this->tdb->runIndateQuery(self::$struct, $data_insert_01, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}				

		// This query will fail because we're trying to insert another row with the same "name" variable, and
		// we have it set in the table as a UNIQUE key. The database should return (int)0
		
		$this->assertEquals(0, $result);

		try {
			$result = $this->tdb->runIndateQuery(self::$struct, $data_insert_01, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// This query will fail because we're trying to insert another row with the same "name" variable, and
		// we have it set in the table as a UNIQUE key. The database should return (int)0
		
		$this->assertEquals(0, $result);

		// Indate second item using auto-increment
				
		$data_insert_02 = array( "name"=>"data_02", "text"=>"Test Text String 02", "priv"=>1, "files"=>222, "space"=>3333);

		try {
			$result = $this->tdb->runIndateQuery(self::$struct, $data_insert_02, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}				

		// The database should return (int)1 to show 1 row was affected. This item will actually have id=4
		// because the previous two failed queries used id's 2 and 3 and the db's cache will not have reset yet.
		
		$this->assertEquals(1, $result);

		// Indate third item, specifying first item's primary key. If the indate is working properly,
		// this data item should overwrite the first data item
		
		$data_insert_02 = array( "id"=>1, "name"=>"data_03", "text"=>"Test Text String 03", "priv"=>1, "files"=>222, "space"=>3333 );

		$data_check_01 = new stdClass();
		$data_check_01->id = 1;
		$data_check_01->name = "data_03";
		$data_check_01->text = "Test Text String 03";
		$data_check_01->priv = "1";
		$data_check_01->files = "222";
		$data_check_01->space = "3333";

		try {
			$result = $this->tdb->runIndateQuery(self::$struct, $data_insert_02, $columns=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// The database will return int(2), because mySQL returns int(2) when it UPDATES a row as
		// part of an INSERT-UPDATE clause
		
		$this->assertEquals(2, $result);
		
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'id', "=", "1", $columns=null, $ctrl);
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



?>