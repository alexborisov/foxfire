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

class database_resultFormatters extends RAZ_testCase {


	static $struct = array(

		"table" => "bpm_test_resultFormatters",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "test" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>false)
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


	function test_var(){
	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
		
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		// Test formatter
		// ========================================================		   
	    
		$columns = array("mode"=>"include", "col"=>"name");
		$ctrl = array("format"=>"var");

		$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", 'data_02', $columns, $ctrl);

		$this->assertEquals("data_02", $result);		

	}


	function test_col(){
	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Load table with data and verify it was correctly loaded
		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
		
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================
		
		$data_check = array( "data_01", "data_02", "data_03", "data_04", "data_05");

		// Set constraints so the query returns multiple strings from a single column, set format as column
		$columns = array("mode"=>"include", "col"=>"name");
		$ctrl = array("format"=>"col");

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", 1, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($data_check, $result);

	}

	
	function test_row_object(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Load table with data and verify it was correctly loaded
		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
		
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = new stdClass();
		$data_check->name = "data_03";
		$data_check->test = "blue";
		$data_check->priv = 1;
		$data_check->files = 333;
		$data_check->space = 334;		

		// Set constraints so the query returns multiple strings from a single column, set format as column
		$columns = array("mode"=>"exclude", "col"=>"id");
		$ctrl = array("format"=>"row_object");

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", "data_03", $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals($data_check, $result);

	}

	function test_row_array(){

		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Load table with data and verify it was correctly loaded
		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
		
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array("name" => "data_03", "test" => "blue", "priv" => 1, "files" => 333, "space" => 334);

		// Set constraints so the query returns all the results in the table
		$columns = array("mode"=>"exclude", "col"=>"id");
		$ctrl = array("format"=>"row_array");

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'name', "=", "data_03", $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($data_check, $result);		

	}

	
	function test_array_key_object_single(){

		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$data_insert = array(
			array(	"name"=>"data_01",  "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",  "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",  "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",  "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",  "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556)
		);
		
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array();

		// Build check array
		foreach($data_insert as $row){

			$obj = new stdClass();

			foreach($row as $key => $val){

				$obj->{$key} = $val;
			}

			$data_check[$row["name"]] = $obj;
		}

		// Set constraints so the query returns multiple strings from a single column, set format as column
		$columns = array("mode"=>"exclude", "col"=>"id");
		$ctrl = array(
				"format"=>"array_key_object",
				"key_col"=>"name"
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", 1, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals($data_check, $result);

	}
	

	function test_array_key_object_multi(){

		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$c111 = new stdClass();
		$c111->name = "data_01"; $c111->priv = 1; $c111->space = 112;

		$c666 = new stdClass();
		$c666->name = "data_06"; $c666->priv = 2; $c666->space = 667;

		$c222 = new stdClass();
		$c222->name = "data_02"; $c222->priv = 1; $c222->space = 223;

		$c333 = new stdClass();
		$c333->name = "data_03"; $c333->priv = 1; $c333->space = 334;

		$c444 = new stdClass();
		$c444->name = "data_04"; $c444->priv = 1; $c444->space = 445;

		$c555 = new stdClass();
		$c555->name = "data_05"; $c555->priv = 1; $c555->space = 556;

		$c777 = new stdClass();
		$c777->name = "data_07"; $c777->priv = 2; $c777->space = 778;

		$c888 = new stdClass();
		$c888->name = "data_08"; $c888->priv = 2; $c888->space = 889;


		$data_check = array(

			"red"=>array(
					"111"=>$c111, "666"=>$c666
			),
			"green"=>array(
					"222"=>$c222
			),
			"blue"=>array(
					"333"=>$c333
			),
			"pink"=>array(
					"444"=>$c444
			),
			"black"=>array(
					"555"=>$c555, "777"=>$c777, "888"=>$c888
			)
		);

		// Set constraints so the query returns multiple strings from a single column, set format as column
		$columns = array("mode"=>"exclude", "col"=>"id");
		$ctrl = array(
				"format"=>"array_key_object",
				"key_col"=>array("test", "files")
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "!=", 3, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals($data_check, $result);			

	}

	
	function test_array_key_array_single(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",  "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",  "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",  "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",  "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",  "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556)
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array();

		// Build check array
		foreach($data_insert as $row){

			$data_check[$row["name"]] = $row;
		}

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_key_array",
				"key_col"=>"name"
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", 1, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($data_check, $result);
		

	}


	function test_array_key_array_multi(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array(

			"red"=>array(
					"111"=>array("name" => "data_01", "priv" => 1, "space" => 112),
					"666"=>array("name" => "data_06", "priv" => 2, "space" => 667)
			),
			"green"=>array(
					"222"=>array("name" => "data_02", "priv" => 1, "space" => 223)
			),
			"blue"=>array(
					"333"=>array("name" => "data_03", "priv" => 1, "space" => 334)
			),
			"pink"=>array(
					"444"=>array("name" => "data_04", "priv" => 1, "space" => 445)
			),
			"black"=>array(
					"555"=>array("name" => "data_05", "priv" => 1, "space" => 556),
					"777"=>array("name" => "data_07", "priv" => 2, "space" => 778),
					"888"=>array("name" => "data_08", "priv" => 2, "space" => 889)
			),
		);

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_key_array",
				"key_col"=>array("test", "files")
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "!=", 3, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($data_check, $result);
		

	}


	function test_array_key_array_grouped(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array(

			"red"=>array(1,2),
			"green"=>array(1),
			"blue"=>array(1),
			"pink"=>array(1),
			"black"=>array(1,2,2)	// NOTE: this is correct behavior. There are two rows in the test array
						// that map to "test"="black" and "priv"="2"
		);

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_key_array_grouped",
				"key_col"=>array("test", "priv")
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "!=", 3, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($data_check, $result);

	}


	function test_array_key_array_true(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array(

			"red"=>array(1=>true, 2=>true),
			"green"=>array(1=>true),
			"blue"=>array(1=>true),
			"pink"=>array(1=>true),
			"black"=>array(1=>true,2=>true)	// NOTE: that there are two rows in the test array
							// that map to "test"="black" and "priv"="2"
		);

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_key_array_true",
				"key_col"=>array("test", "priv")
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "!=", 3, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($data_check, $result);
		

	}


	function test_array_key_array_false(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array(

			"red"=>array(1=>false, 2=>false),
			"green"=>array(1=>false),
			"blue"=>array(1=>false),
			"pink"=>array(1=>false),
			"black"=>array(1=>false,2=>false)   // NOTE: that there are two rows in the test array
							    // that map to "test"="black" and "priv"="2"
		);

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_key_array_false",
				"key_col"=>array("test", "priv")
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "!=", 3, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($data_check, $result);
		

	}


	function test_array_key_single(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",    "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",    "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",    "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",    "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",    "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556),
			array(	"name"=>"data_06",    "test"=>"red",	"priv"=>2,  "files"=>666,   "space"=>667),
			array(	"name"=>"data_07",    "test"=>"black",	"priv"=>2,  "files"=>777,   "space"=>778),
			array(	"name"=>"data_08",    "test"=>"black",	"priv"=>2,  "files"=>888,   "space"=>889),
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array(
			"data_01"=>"red",
			"data_02"=>"green",
			"data_03"=>"blue",
			"data_04"=>"pink",
			"data_05"=>"black"
		);

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"include", "col"=>array("name", "test") );
		
		$ctrl = array(
				"format"=>"array_key_single",
				"key_col"=>"name",
				"val_col"=>"test"
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", 1, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals($data_check, $result);
		

	}

	function test_array_object(){

	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",  "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",  "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",  "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",  "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",  "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556)
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array();

		// Build check array
		foreach($data_insert as $row){

			$obj = new stdClass();

			foreach($row as $key => $val){

				$obj->{$key} = $val;
			}

			$data_check[] = $obj;
		}

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_object",
				"key_col"=>"name"
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", 1, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($data_check, $result);
		

	}

	function test_array_array(){
	    
	    
		try {
			$this->tdb->runTruncateTable(self::$struct);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}	

		$data_insert = array(
			array(	"name"=>"data_01",  "test"=>"red",	"priv"=>1,  "files"=>111,   "space"=>112),
			array(	"name"=>"data_02",  "test"=>"green",	"priv"=>1,  "files"=>222,   "space"=>223),
			array(	"name"=>"data_03",  "test"=>"blue",	"priv"=>1,  "files"=>333,   "space"=>334),
			array(	"name"=>"data_04",  "test"=>"pink",	"priv"=>1,  "files"=>444,   "space"=>445),
			array(	"name"=>"data_05",  "test"=>"black",	"priv"=>1,  "files"=>555,   "space"=>556)
		);
				
		// Write test data to db
		// ========================================================
		
		$columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct, $data_insert, $columns);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Test formatter
		// ========================================================

		$data_check = array();

		// Build check array
		foreach($data_insert as $row){

			$data_check[] = $row;
		}

		// Set constraints so the query returns multiple strings from a single column, set format as column
		
		$columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array(
				"format"=>"array_array",
				"key_col"=>"name"
		);

		try {
			$result = $this->tdb->runSelectQueryCol(self::$struct, 'priv', "=", 1, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($data_check, $result);
		

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