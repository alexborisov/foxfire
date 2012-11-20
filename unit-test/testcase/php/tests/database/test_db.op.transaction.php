<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DATABASE TRANSACTIONS
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

class database_innodb_transactions extends RAZ_testCase {


	static $struct = array(

		"table" => "bpm_test_transactions_A",
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


	function test_begin_insert_commit_select(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

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

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		try {
			$this->tdb->commitTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		try {
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
						
		$this->assertEquals($data_check_01, $result[0]);

	}


	function test_begin_insert_commit(){

		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

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

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		try {
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_01', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		try {
			$this->tdb->commitTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		    
		    
		$this->assertEquals($data_check_01, $result[0]);

	}


	function test_begin_insert_rollback_select(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_insert_01 = array(
		    
			array( "name"=>"data_01",   "text"=>"Test Text String 01",  "priv"=>1,	"files"=>222,	"space"=>3333 ),
			array( "name"=>"data_02",   "text"=>"Test Text String 02",  "priv"=>1,	"files"=>333,	"space"=>4444 )			    
		);

		$data_insert_02 = array(

			array( "name"=>"data_03",   "text"=>"Test Text String 03",  "priv"=>1,	"files"=>555,	"space"=>6666 ),
			array( "name"=>"data_04",   "text"=>"Test Text String 04",  "priv"=>1,	"files"=>666,	"space"=>7777 )
		);

		$data_check = array(

			array( "name"=>"data_01",   "text"=>"Test Text String 01",  "priv"=>1,	"files"=>222,	"space"=>3333 ),
			array( "name"=>"data_02",   "text"=>"Test Text String 02",  "priv"=>1,	"files"=>333,	"space"=>4444 ),
			array( "name"=>"data_03",   "text"=>"Test Text String 03",  "priv"=>1,	"files"=>555,	"space"=>6666 ),
			array( "name"=>"data_04",   "text"=>"Test Text String 04",  "priv"=>1,	"files"=>666,	"space"=>7777 )
		);	

		$columns = array("mode"=>"exclude", "col"=>"id");


		// Start a transaction, add $data_insert_01 to the 
		// table, commit transaction
		// ========================================================
		
		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert_01, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		try {
			$this->tdb->commitTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		  
		// Check table is in correct state
		// ========================================================
		
		try {
			$ctrl = array("format"=>"array_array");
			$result = $this->tdb->runSelectQueryCol(self::$struct, $col = 'priv', $op = "=", $val = '1', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$this->assertEquals($data_insert_01, $result);


		// Start a transaction, add $data_insert_02 to the table
		// ========================================================
		
		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		    try {
			    $this->tdb->runInsertQueryMulti(self::$struct, $data_insert_02, $columns, $ctrl=null);
		    }
		    catch (BPM_exception $child) {

			    $this->fail($child->dumpString(1));	
		    }

		    try {
			    $ctrl = array("format"=>"array_array");
			    $result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", '1', $columns, $ctrl);
		    }
		    catch (BPM_exception $child) {

			    $this->fail($child->dumpString(1));	
		    }		
		    
		    // Contents of table should match contents of $data_insert_01 plus $data_insert_02 ($data_check)
		    $this->assertEquals($data_check, $result);
		    

		// Rollback the transaction
		// ========================================================
		    
		try {
			$this->tdb->rollbackTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		// Verify table is in correct state
		// ========================================================
		
		try {
			$ctrl = array("format"=>"array_array");
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", '1', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		// Contents of the table should match contents of $data_insert_01 again
		$this->assertEquals($data_insert_01, $result);
	
	}


	function test_double_begin_fail(){

	    
		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	    

		try {
			$this->tdb->beginTransaction();
			
			// Execution will halt on the previous line if beginTransaction() throws an exception
			$this->fail("beginTransaction() failed to throw an exception");			
		}
		catch (BPM_exception $child) {

			if($child->data['numeric'] != 1){
				$this->fail($child->dumpString(1));	
			}
		}				
		
	}

	
	function test_commit_without_begin_fail(){

	    
		try {
			$this->tdb->commitTransaction();
			
			// Execution will halt on the previous line if beginTransaction() throws an exception
			$this->fail("commitTransaction() failed to throw an exception");				
		}
		catch (BPM_exception $child) {

			if($child->data['numeric'] != 1){
				$this->fail($child->dumpString(1));	
			}	
		}	    
		

	}

	
	function test_double_commit_fail(){


		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		try {
			$this->tdb->commitTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		try {
			$this->tdb->commitTransaction();
			
			// Execution will halt on the previous line if beginTransaction() throws an exception
			$this->fail("commitTransaction() failed to throw an exception");				
		}
		catch (BPM_exception $child) {

			if($child->data['numeric'] != 1){
				$this->fail($child->dumpString(1));	
			}	
		}

	}


	function test_rollback_without_begin_fail(){


		try {
			$this->tdb->rollbackTransaction();
			
			// Execution will halt on the previous line if beginTransaction() throws an exception
			$this->fail("rollbackTransaction() failed to throw an exception");				
		}
		catch (BPM_exception $child) {

			if($child->data['numeric'] != 1){
				$this->fail($child->dumpString(1));	
			}	
		}


	}


	function test_double_rollback_fail(){


		try {
			$this->tdb->beginTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		try {
			$this->tdb->rollbackTransaction();
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		try {
			$this->tdb->rollbackTransaction();
			
			// Execution will halt on the previous line if beginTransaction() throws an exception
			$this->fail("rollbackTransaction() failed to throw an exception");				
		}
		catch (BPM_exception $child) {

			if($child->data['numeric'] != 1){
				$this->fail($child->dumpString(1));	
			}	
		}

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
