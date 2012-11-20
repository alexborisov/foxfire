<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DB TABLE OPERATIONS
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

class database_runAddTable_indexPrimaryKey extends RAZ_testCase {


	static $struct = array(

		"table" => "bpm_test_runAddTable_indexPrimaryKey",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	 "sql"=>"smallint", "format"=>"%d", "width"=>6,	    "flags"=>"NOT NULL",    "auto_inc"=>false,	"default"=>null,  "index"=>"PRIMARY"),
		    "text" =>	array(	"php"=>"string", "sql"=>"varchar",  "format"=>"%s", "width"=>250,   "flags"=>null,	    "auto_inc"=>false,	"default"=>null,  "index"=>false)
		 )
	);


    	function setUp() {

		parent::setUp();

		$this->tdb = new BPM_db();

		try {
			$this->tdb->runAddTable(self::$struct);
		}
		catch (BPM_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct);
				}
				catch (BPM_exception $child) {

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



	function test_runAddTable_indexPrimaryKey(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$data_insert_01 = new stdClass();
		$data_insert_01->id = "1";
		$data_insert_01->text = "Test Text String 01";

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns=null, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(1, $result);

		$data_insert_02 = new stdClass();
		$data_insert_02->id = "2";
		$data_insert_02->text = "Test Text String 02";

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_02, $columns=null, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(1, $result);

		$data_insert_03 = new stdClass();
		$data_insert_03->id = "1";
		$data_insert_03->text = "This should fail";

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_03, $columns=null, $ctrl=null);
			
			// Execution *should* halt before this line, because runInsertQueryMulti() will throw an exception
			$this->fail("runInsertQueryMulti() failed to throw an exception");
			
		}
		catch (BPM_exception $child) {

			$this->assertEquals(2, $child->data["numeric"] );	
		}

		$data_check_01 = new stdClass();
		$data_check_01->id = "1";
		$data_check_01->text = "Test Text String 01";

		$data_check_02 = new stdClass();
		$data_check_02->id = "2";
		$data_check_02->text = "Test Text String 02";
				
		try {
			$ctrl = array("format"=>"array_object");
			$result_a = $this->tdb->runSelectQueryCol(self::$struct, 'id', "=", '1', $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals($data_check_01, $result_a[0]);	
		
		try {
			$ctrl = array("format"=>"array_object");
			$result_b = $this->tdb->runSelectQueryCol(self::$struct, 'id', "=", '2', $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
			
		$this->assertEquals($data_check_02, $result_b[0]);

	}


	function tearDown() {
		
		$this->tdb = new BPM_db();
		
		try {
			$this->tdb->runDropTable(self::$struct);
		}
		catch (BPM_exception $child) {
		    		    
			$this->fail("Error while dropping database table. Error code: " . $child->data['numeric']);			    
		}		

		parent::tearDown();
	}

}



class database_runAddTable_indexUnique extends RAZ_testCase {


	static $struct = array(

		"table" => "bpm_test_runAddTable_indexUnique",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint", "format"=>"%d", "width"=>6,	"flags"=>"NOT NULL",	"auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",  "format"=>"%s", "width"=>250,	"flags"=>"NOT NULL",	"auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",  "format"=>"%s", "width"=>250,	"flags"=>null,		"auto_inc"=>false, "default"=>null,  "index"=>false)
		 )
	);

    	function setUp() {

		parent::setUp();

		$this->tdb = new BPM_db();

		try {
			$this->tdb->runAddTable(self::$struct);
		}
		catch (BPM_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct);
				}
				catch (BPM_exception $child) {

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


	function test_runAddTable_indexUnique(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}					
		
		// Insert unique row
		// ========================================================	
		
		$data_insert_01 = new stdClass();
		$data_insert_01->name = "data_01";
		$data_insert_01->text = "Test Text String 01";
		
		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
			
			$this->fail($child->dumpString(1));
		}
		
		// Insert unique row
		// ========================================================	
		
		$data_insert_02 = new stdClass();
		$data_insert_02->name = "data_02";
		$data_insert_02->text = "Test Text String 02";
		
		$data_check_02 = new stdClass();
		$data_check_02->name = "data_02";
		$data_check_02->text = "Test Text String 02";
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_02, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));
		}
		
		// Insert duplicate row (should fail)
		// ========================================================	
		
		$data_insert_03 = new stdClass();
		$data_insert_03->name = "data_01";
		$data_insert_03->text = "This should fail";
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_03, $columns, $ctrl=null);
			
			// Execution will halt on the previous line if runInsertQueryMulti() throws an exception
			$this->fail("runInsertQueryMulti() failed to throw exception on duplicate entry");
		}
		catch (BPM_exception $child) {

			if($child->data['numeric'] != 2){
				$this->fail($child->dumpString(1));	
			}			
		}		

		// Check table is in correct state
		// ========================================================	
		
		try {
			$ctrl=array("format"=>"array_object");
			$result_a = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals($data_check_01, $result_a[0]);
		
		try {
			$ctrl=array("format"=>"array_object");
			$result_b = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_02', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
							
		$this->assertEquals($data_check_02, $result_b[0]);

	}

	
	function tearDown() {

		$this->tdb = new BPM_db();
		
		try {
			$this->tdb->runDropTable(self::$struct);
		}
		catch (BPM_exception $child) {
		    		    
			$this->fail("Error while dropping database table. Error code: " . $child->data['numeric']);			    
		}		

		parent::tearDown();
	}

}



class database_runTruncateTable extends RAZ_testCase {


	static $struct = array(

		"table" => "bpm_test_runTruncateTable",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
		 )
	);


    	function setUp() {

		parent::setUp();

		$this->tdb = new BPM_db();

		try {
			$this->tdb->runAddTable(self::$struct);
		}
		catch (BPM_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct);
				}
				catch (BPM_exception $child) {

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


	function test_runTruncateTable(){


		// Load table with data
		// ========================================================	
	    
		$data_insert_01 = array( 
			array( "name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333 )
		);

		$data_check_01 = new stdClass();
		$data_check_01->name = "data_01";
		$data_check_01->text = "Test Text String 01";
		$data_check_01->priv = 1;
		$data_check_01->files = 222;
		$data_check_01->space = 3333;

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals(1, $result);

		// Check table is in correct state
		// ========================================================	
		
		try {
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals($data_check_01, $result[0]);

		// Truncate the table
		// ========================================================	
		
		try {
			$result = $this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals(true, $result);

		// Check table is empty
		// ========================================================	
		
		try {
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$this->assertEquals(null, $result[0]);

	}

	
	function tearDown() {
		
		$this->tdb = new BPM_db();
		
		try {
			$this->tdb->runDropTable(self::$struct);
		}
		catch (BPM_exception $child) {
		    		    
			$this->fail("Error while dropping database table. Error code: " . $child->data['numeric']);			    
		}		

		parent::tearDown();
		
	}

}


?>