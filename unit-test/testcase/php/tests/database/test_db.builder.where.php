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


class database_queryBuilders_where extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();
		$this->builder = new FOX_queryBuilder($test_db);

	}


	function test_buildWhere(){

		$struct = array(

			"table" => "fox_test_bw",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_4" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false),
			    "col_5" =>	array(	"php"=>"int",	    "sql"=>"mediumint","format"=>"%d", "width"=>7,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false),
			    "col_6" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false),
			    "col_7" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false)
			 )
		);


		// Prefixes
		// ============================================================================================

		$args = array(

			array( "col"=>"col_1", "op"=>">=", "val"=>1),
			array( "col"=>"col_2", "op"=>"<=", "val"=>2),
			array( "col"=>"col_3", "op"=>">", "val"=>3),
		);

		$check_string = " AND test_col_1 >= %d AND test_col_2 <= %s AND test_col_3 > %s";

		try {
			$result = $this->builder->buildWhere($struct, $args, $caller, $prefix="test_");
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_string, $result['where']);



		// Simple ints
		// ============================================================================================

		$args = array(

			 array( "col"=>"col_1", "op"=>">=", "val"=>1),
			 array( "col"=>"col_2", "op"=>"<=", "val"=>2),
			 array( "col"=>"col_3", "op"=>">", "val"=>3),
			 array( "col"=>"col_4", "op"=>"<", "val"=>4),
			 array( "col"=>"col_5", "op"=>"=", "val"=>5),
			 array( "col"=>"col_6", "op"=>"!=", "val"=>6),
			 array( "col"=>"col_7", "op"=>"<>", "val"=>7),
		);

		$check_string = " AND col_1 >= %d AND col_2 <= %s AND col_3 > %s AND col_4 < %d AND col_5 = %d AND col_6 != %d AND col_7 <> %d";
		$check_array = array(1, 2, 3, 4, 5, 6, 7);

		try {
			$result = $this->builder->buildWhere($struct, $args, $caller, $prefix=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($check_string, $result['where']);
		$this->assertEquals($check_array, $result['params']);


		// Simple strings
		// ============================================================================================

		$args = array(

			// Applying operators like ">" to a string is a valid test, because the user
			// may be testing against *date* objects, which are passed as strings

			array( "col"=>"col_1", "op"=>">=", "val"=>"s_1"),
			array( "col"=>"col_2", "op"=>"<=", "val"=>"s_2"),
			array( "col"=>"col_3", "op"=>">", "val"=>"s_3"),
			array( "col"=>"col_4", "op"=>"<", "val"=>"s_4"),
			array( "col"=>"col_5", "op"=>"=", "val"=>"s_5"),
			array( "col"=>"col_6", "op"=>"!=", "val"=>"s_6"),
			array( "col"=>"col_7", "op"=>"<>", "val"=>"s_7"),
		);

		$check_string = " AND col_1 >= %d AND col_2 <= %s AND col_3 > %s AND col_4 < %d AND col_5 = %d AND col_6 != %d AND col_7 <> %d";
		$check_array = array("s_1", "s_2", "s_3", "s_4", "s_5", "s_6", "s_7");

		try {
			$result = $this->builder->buildWhere($struct, $args, $caller, $prefix=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_string, $result['where']);
		$this->assertEquals($check_array, $result['params']);



		// Arrays of ints
		// ============================================================================================

		$args = array(

			// Only equality and inequality operators are valid for arrays.

			array( "col"=>"col_1", "op"=>"=", "val"=>array(1, 2, 3) ),
			array( "col"=>"col_2", "op"=>"!=", "val"=>array(4, 5, 6) ),
			array( "col"=>"col_3", "op"=>"<>", "val"=>array(7, 8, 9) )
		);

		$check_string = " AND col_1 IN(%d, %d, %d) AND col_2 NOT IN(%s, %s, %s) AND col_3 NOT IN(%s, %s, %s)";
		$check_array = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
		
		try {
			$result = $this->builder->buildWhere($struct, $args, $caller, $prefix=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_string, $result['where']);
		$this->assertEquals($check_array, $result['params']);


		// Arrays of strings
		// ============================================================================================

		$args = array(

			// Only equality and inequality operators are valid for arrays.

			array( "col"=>"col_1", "op"=>"=", "val"=>array("s_1", "s_2", "s_3") ),
			array( "col"=>"col_2", "op"=>"!=", "val"=>array("s_4", "s_5", "s_6") ),
			array( "col"=>"col_3", "op"=>"<>", "val"=>array("s_7", "s_8", "s_9") )
		);

		$check_string = " AND col_1 IN(%d, %d, %d) AND col_2 NOT IN(%s, %s, %s) AND col_3 NOT IN(%s, %s, %s)";
		$check_array = array("s_1", "s_2", "s_3", "s_4", "s_5", "s_6", "s_7", "s_8", "s_9");

		try {
			$result = $this->builder->buildWhere($struct, $args, $caller, $prefix=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_string, $result['where']);
		$this->assertEquals($check_array, $result['params']);

				
	}


	function test_buildWhereMulti(){

	    
		$struct = array(

			"table" => "fox_test_bw",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_4" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false),
			    "col_5" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false),
			    "col_6" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false),
			    "col_7" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false)
			 )
		);

		// Prefixes
		// ============================================================================================

		$args = array(
				array(
					array( "col"=>"col_1", "op"=>">=", "val"=>1),
					array( "col"=>"col_2", "op"=>"<=", "val"=>2),
					array( "col"=>"col_3", "op"=>">", "val"=>3),
				),
				array(
					array( "col"=>"col_1", "op"=>"=", "val"=>9),
					array( "col"=>"col_2", "op"=>"!=", "val"=>6),
					array( "col"=>"col_3", "op"=>"<", "val"=>1),
				),
				array(
					array( "col"=>"col_5", "op"=>"<>", "val"=>5),
					array( "col"=>"col_6", "op"=>"=", "val"=>8),
					array( "col"=>"col_7", "op"=>"<>", "val"=>11),
				)
		);

		$check_string  = " AND (test_col_1 >= %d AND test_col_2 <= %s AND test_col_3 > %s)";
		$check_string .= " OR (test_col_1 = %d AND test_col_2 != %s AND test_col_3 < %s)";
		$check_string .= " OR (test_col_5 <> %d AND test_col_6 = %d AND test_col_7 <> %d)";

		$check_array = array(1, 2, 3, 9, 6, 1, 5, 8, 11);

		try {
			$result = $this->builder->buildWhereMulti($struct, $args, $caller, $prefix="test_");
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		


		$this->assertEquals($check_string, $result['where']);
		$this->assertEquals($check_array, $result['params']);

		
	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	

}


?>