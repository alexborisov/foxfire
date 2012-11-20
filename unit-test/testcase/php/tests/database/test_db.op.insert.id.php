<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - RESULT FORMATTERS
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


class database_insertID_InnoDB extends RAZ_testCase {

    
	static $struct = array(

		"table" => "bpm_test_InnoDB_getInsertID",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	"index"=>true)
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


	function test_singleInsert(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_01 = array("name"=>"data_01", "text"=>"Test Text String 01", "priv"=>11);
		$data_02 = array("name"=>"data_02", "text"=>"Test Text String 02", "priv"=>12);
		$data_03 = array("name"=>"data_03", "text"=>"Test Text String 03", "priv"=>13);
		$data_04 = array("name"=>"data_04", "text"=>"Test Text String 04", "priv"=>14);

		$insert_ids = array();

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_01, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_02, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		$insert_ids[] = $this->tdb->insert_id;

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_03, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_04, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		$check_array = array(1,2,3,4);

		$this->assertEquals($check_array, $insert_ids);
		
	}


	function test_multiInsert(){


		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_01 = array (
				array("name"=>"data_01", "text"=>"Test Text String 01", "priv"=>11),
				array("name"=>"data_02", "text"=>"Test Text String 02", "priv"=>12),
				array("name"=>"data_03", "text"=>"Test Text String 03", "priv"=>13)
		);

		$data_02 = array("name"=>"data_04", "text"=>"Test Text String 04", "priv"=>14);

		$insert_ids = array();

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_01, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		$this->assertEquals(3, $result);    // runInsertQueryMulti() returns number of rows affected (should be int 3)

		try {
			$result = $this->tdb->runInsertQuery(self::$struct, $data_02, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		$this->assertEquals(1, $result);   // runInsertQuery() returns number of rows affected (should be int 1)

		$check_array = array(1,4);

		$this->assertEquals($check_array, $insert_ids);

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


class database_insertID_MyISAM extends RAZ_testCase {

	static $struct = array(

		"table" => "bpm_test_MyISAM_getInsertID",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	"index"=>true)
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


	function test_singleInsert(){


		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_01 = array("name"=>"data_01", "text"=>"Test Text String 01", "priv"=>11);
		$data_02 = array("name"=>"data_02", "text"=>"Test Text String 02", "priv"=>12);
		$data_03 = array("name"=>"data_03", "text"=>"Test Text String 03", "priv"=>13);
		$data_04 = array("name"=>"data_04", "text"=>"Test Text String 04", "priv"=>14);

		$insert_ids = array();

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_01, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_02, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_03, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		try {
			$this->tdb->runInsertQuery(self::$struct, $data_04, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		$check_array = array(1,2,3,4);

		$this->assertEquals($check_array, $insert_ids);

	}


	function test_multiInsert(){


		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$data_01 = array (
				array("name"=>"data_01", "text"=>"Test Text String 01", "priv"=>11),
				array("name"=>"data_02", "text"=>"Test Text String 02", "priv"=>12),
				array("name"=>"data_03", "text"=>"Test Text String 03", "priv"=>13)
		);

		$data_02 = array("name"=>"data_04", "text"=>"Test Text String 04", "priv"=>14);

		$insert_ids = array();

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$result = $this->tdb->runInsertQueryMulti(self::$struct, $data_01, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		$this->assertEquals(3, $result);    // runInsertQueryMulti() returns number of rows affected (should be int 3)

		try {
			$result = $this->tdb->runInsertQuery(self::$struct, $data_02, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$insert_ids[] = $this->tdb->insert_id;

		$this->assertEquals(1, $result);   // runInsertQuery() returns number of rows affected (should be int 1)

		$check_array = array(1,4);

		$this->assertEquals($check_array, $insert_ids);

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


class database_resultOrdering extends RAZ_testCase {


	static $struct = array(

		"table" => "bpm_test_resultFormatters",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "test" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>false),
		    "position" => array( "php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>true)
		 )
	);


	// NOTE: this test fixture uses a complicated data set and is designed to test for 'quirks' on
	// different SQL server implementations. As such, we do the data load-in in the setup method,
	// and also read-back the data to make sure it was loaded properly. This is one of the few cases
	// where we don't follow the "each test method must work in isolation" rule.
	
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

		// Load table with data and verify it was correctly loaded
		$data_insert = array(
			array(	"name"=>"data_01",  "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112,   "position"=>1 ),
			array(	"name"=>"data_02",  "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223,   "position"=>2 ),
			array(	"name"=>"data_03",  "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334,   "position"=>3 ),
			array(	"name"=>"data_04",  "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445,   "position"=>4 ),
			array(	"name"=>"data_05",  "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556,   "position"=>5 ),
			array(	"name"=>"data_06",  "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667,   "position"=>6 ),
			array(	"name"=>"data_07",  "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778,   "position"=>7 ),
			array(	"name"=>"data_08",  "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889,   "position"=>8 )
		);

		$data_check = array();

		foreach($data_insert as $row){

			$row_obj = new stdClass();

			foreach($row as $key => $value){

				$row_obj->{$key} = $value;
			}

			$data_check[] = $row_obj;
		}

		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "!=", '3', $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}				

		$this->assertEquals($data_check, $result);
		
	}


	function test_ASC(){

		
		$check_array = array(
			array(	"id"=>1,    "name"=>"data_01",  "test"=>"red",	    "priv"=>1,  "files"=>111,   "space"=>112,   "position"=>1 ),
			array(	"id"=>2,    "name"=>"data_02",  "test"=>"green",    "priv"=>1,  "files"=>222,   "space"=>223,   "position"=>2 ),
			array(	"id"=>3,    "name"=>"data_03",  "test"=>"blue",	    "priv"=>1,  "files"=>333,   "space"=>334,   "position"=>3 ),
			array(	"id"=>4,    "name"=>"data_04",  "test"=>"pink",	    "priv"=>1,  "files"=>444,   "space"=>445,   "position"=>4 ),
			array(	"id"=>5,    "name"=>"data_05",  "test"=>"black",    "priv"=>1,  "files"=>555,   "space"=>556,   "position"=>5 ),
			array(	"id"=>6,    "name"=>"data_06",  "test"=>"red",	    "priv"=>2,  "files"=>666,   "space"=>667,   "position"=>6 ),
			array(	"id"=>7,    "name"=>"data_07",  "test"=>"black",    "priv"=>2,  "files"=>777,   "space"=>778,   "position"=>7 ),
			array(	"id"=>8,    "name"=>"data_08",  "test"=>"black",    "priv"=>2,  "files"=>888,   "space"=>889,   "position"=>8 )
		);
		

		// Set constraints so the query returns a single string, set format as single variable
		$ctrl = array(
				"format"=>"array_array",
				'sort'=>array(
						array("col"=>"position", "sort"=>"ASC")
				 )
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'id', ">", 0, null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		

		$this->assertEquals($check_array, $result);

	}


	function test_DESC(){



		$check_array = array(
			array(	"id"=>8,    "name"=>"data_08",  "test"=>"black",    "priv"=> 2, "files"=>888,   "space"=>889,   "position"=>8),
			array(	"id"=>7,    "name"=>"data_07",  "test"=>"black",    "priv"=> 2, "files"=>777,   "space"=>778,   "position"=>7),
			array(	"id"=>6,    "name"=>"data_06",  "test"=>"red",	    "priv"=> 2, "files"=>666,   "space"=>667,   "position"=>6),
			array(	"id"=>5,    "name"=>"data_05",  "test"=>"black",    "priv"=> 1, "files"=>555,   "space"=>556,   "position"=>5),
			array(	"id"=>4,    "name"=>"data_04",  "test"=>"pink",	    "priv"=> 1, "files"=>444,   "space"=>445,   "position"=>4),
			array(	"id"=>3,    "name"=>"data_03",  "test"=>"blue",	    "priv"=> 1, "files"=>333,   "space"=>334,   "position"=>3),
			array(	"id"=>2,    "name"=>"data_02",  "test"=>"green",    "priv"=> 1, "files"=>222,   "space"=>223,   "position"=>2),
			array(	"id"=>1,    "name"=>"data_01",  "test"=>"red",	    "priv"=> 1, "files"=>111,   "space"=>112,   "position"=>1)			
		);

		// Set constraints so the query returns a single string, set format as single variable
		$ctrl = array(
				"format"=>"array_array",
				'sort'=>array(
						array("col"=>"position", "sort"=>"DESC")
				 )
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'id', ">", 0, null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}				

		$this->assertEquals($check_array, $result);
		

	}


	function test_ARB_allKeysSet(){


		$check_array = array(
			array(	"id"=>1,    "name"=>"data_01",	"test"=>"red",	    "priv"=>1,	"files"=>111,	"space"=>112,	"position"=>1 ),
			array(	"id"=>8,    "name"=>"data_08",	"test"=>"black",    "priv"=>2,	"files"=>888,	"space"=>889,	"position"=>8 ),
			array(	"id"=>3,    "name"=>"data_03",	"test"=>"blue",	    "priv"=>1,	"files"=>333,	"space"=>334,	"position"=>3 ),
			array(	"id"=>7,    "name"=>"data_07",	"test"=>"black",    "priv"=>2,	"files"=>777,	"space"=>778,	"position"=>7 ),
			array(	"id"=>6,    "name"=>"data_06",	"test"=>"red",	    "priv"=>2,	"files"=>666,	"space"=>667,	"position"=>6 ),
			array(	"id"=>4,    "name"=>"data_04",	"test"=>"pink",	    "priv"=>1,	"files"=>444,	"space"=>445,	"position"=>4 ),
			array(	"id"=>5,    "name"=>"data_05",	"test"=>"black",    "priv"=>1,	"files"=>555,	"space"=>556,	"position"=>5 ),
			array(	"id"=>2,    "name"=>"data_02",	"test"=>"green",    "priv"=>1,	"files"=>222,	"space"=>223,	"position"=>2 )	
		);

		// Set constraints so the query returns a single string, set format as single variable
		$ctrl = array(
				"format"=>"array_array",
				'sort'=>array(
						array("col"=>"position", "sort"=>array(1,8,3,7,6,4,5,2) )
				 )
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'id', ">", 0, null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals($check_array, $result);
		

	}

	
	function test_ARB_partialKeys(){


		$check_array = array(

			// The items with no specified position in the sort array will appear at the front of the result
			// set (because their position in the sort order maps to "0"), ordered by their primary key ('id')
		    
		   	array(	"id"=>2,    "name"=>"data_02",	"test"=>"green",    "priv"=>1,	"files"=>222,	"space"=>223,	"position"=>2 ),
			array(	"id"=>4,    "name"=>"data_04",	"test"=>"pink",	    "priv"=>1,	"files"=>444,	"space"=>445,	"position"=>4 ),
			array(	"id"=>5,    "name"=>"data_05",	"test"=>"black",    "priv"=>1,	"files"=>555,	"space"=>556,	"position"=>5 ),

			// The remaining items will be ordered based on their position in the sort array
		    
			array(	"id"=>1,    "name"=>"data_01",	"test"=>"red",	    "priv"=>1,	"files"=>111,	"space"=>112,	"position"=>1 ),
			array(	"id"=>8,    "name"=>"data_08",	"test"=>"black",    "priv"=>2,	"files"=>888,	"space"=>889,	"position"=>8 ),
			array(	"id"=>3,    "name"=>"data_03",	"test"=>"blue",	    "priv"=>1,	"files"=>333,	"space"=>334,	"position"=>3 ),
			array(	"id"=>7,    "name"=>"data_07",	"test"=>"black",    "priv"=>2,	"files"=>777,	"space"=>778,	"position"=>7 ),
			array(	"id"=>6,    "name"=>"data_06",	"test"=>"red",	    "priv"=>2,	"files"=>666,	"space"=>667,	"position"=>6 )
		);

		// Set constraints so the query returns a single string, set format as single variable
		$ctrl = array(
				"format"=>"array_array",
				'sort'=>array(
						array("col"=>"position", "sort"=>array(1,8,3,7,6) )
				 )
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'id', ">", 0, null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		

		$this->assertEquals($check_array, $result);

	}


	function test_ARB_multiKey(){


		$check_array = array(
			array(	"id"=>1,    "name"=>"data_01",	"test"=>"red",	    "priv"=>1,	"files"=>111,	"space"=>112,	"position"=>1 ),
			array(	"id"=>3,    "name"=>"data_03",	"test"=>"blue",	    "priv"=>1,	"files"=>333,	"space"=>334,	"position"=>3 ),
			array(	"id"=>4,    "name"=>"data_04",	"test"=>"pink",	    "priv"=>1,	"files"=>444,	"space"=>445,	"position"=>4 ),
			array(	"id"=>5,    "name"=>"data_05",	"test"=>"black",    "priv"=>1,	"files"=>555,	"space"=>556,	"position"=>5 ),
			array(	"id"=>2,    "name"=>"data_02",	"test"=>"green",    "priv"=>1,	"files"=>222,	"space"=>223,	"position"=>2 ),
			array(	"id"=>8,    "name"=>"data_08",	"test"=>"black",    "priv"=>2,	"files"=>888,	"space"=>889,	"position"=>8 ),
			array(	"id"=>7,    "name"=>"data_07",	"test"=>"black",    "priv"=>2,	"files"=>777,	"space"=>778,	"position"=>7 ),
			array(	"id"=>6,    "name"=>"data_06",	"test"=>"red",	    "priv"=>2,	"files"=>666,	"space"=>667,	"position"=>6 )
		);

		// Set constraints so the query returns a single string, set format as single variable
		$ctrl = array(
				"format"=>"array_array",
				'sort'=>array(
						array("col"=>"priv", "sort"=>"ASC" ),
						array("col"=>"position", "sort"=>array(1,8,3,7,6,4,5,2) )
				 )
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'id', ">", 0, null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals($check_array, $result);

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