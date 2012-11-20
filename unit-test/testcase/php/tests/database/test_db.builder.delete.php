<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DB QUERY STRING BUILDERS
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


class database_queryBuilders_delete extends RAZ_testCase {


	var $builder;
	var $base_prefix;
	var $charset;
	var $collate;
	var $charset_collate = '';

    	function setUp() {

		parent::setUp();

		$test_db = new BPM_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->charset = $test_db->charset;
		$this->collate = $test_db->collate;

		if ( !empty($this->charset) ){
		    $this->charset_collate = "DEFAULT CHARACTER SET {$this->charset}";
		}

		if ( !empty($this->collate) ){
			$this->charset_collate .= " COLLATE {$this->collate}";
		}

		$this->builder = new BPM_queryBuilder($test_db);

	}


	function test_buildDeleteQuery(){


		$struct = array(

			"table" => "test_a",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
			 )
		);


		
		$table = $this->base_prefix . $struct["table"];


		// Single constraint
		// ============================================================================================

		$args = array(

			array("col"=>"col_1", "op"=>"=", "val"=>53)
		);

		$check_args = array();
		$check_args[0] = "DELETE FROM {$table} WHERE 1 = 1 AND col_1 = %d";

		$check_array = array(53);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildDeleteQuery($struct, $args);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);


		// Multiple constraints
		// ============================================================================================

		$args = array(

			array("col"=>"col_1", "op"=>"=", "val"=>53),
			array("col"=>"col_2", "op"=>"!=", "val"=>"test_val")
		);

		$check_args = array();
		$check_args[0] = "DELETE FROM {$table} WHERE 1 = 1 AND col_1 = %d AND col_2 != %s";

		$check_array = array(53, "test_val");
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildDeleteQuery($struct, $args);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}	

		$this->assertEquals($check_args, $result["query"]);

	}


	function test_buildDeleteQueryCol(){


		$struct = array(

			"table" => "test_a",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
			 )
		);


		
		$table = $this->base_prefix . $struct["table"];

		$check_args = array();
		$check_args[0] = "DELETE FROM {$table} WHERE 1 = 1 AND col_1 <> %d";

		$check_array = array(31);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildDeleteQueryCol($struct, "col_1", "<>", 31);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}					

		$this->assertEquals($check_args, $result["query"]);
		

	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	
	
}

?>