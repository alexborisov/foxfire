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


class database_queryBuilders_delete extends RAZ_testCase {


	var $builder;
	var $base_prefix;
	var $charset;
	var $collate;
	var $charset_collate = '';

    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->charset = $test_db->charset;
		$this->collate = $test_db->collate;

		if ( !empty($this->charset) ){
		    $this->charset_collate = "DEFAULT CHARACTER SET {$this->charset}";
		}

		if ( !empty($this->collate) ){
			$this->charset_collate .= " COLLATE {$this->collate}";
		}

		$this->builder = new FOX_queryBuilder($test_db);

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
		
		$check_args = array(
		    
			'query'=> "DELETE FROM {$table} WHERE 1 = 1 AND col_1 = %d",
			'params'=> array(
					    array('escape'=>true, 'val'=>53, 'php'=>'int', 'sql'=>'smallint')
			)		    
		);

		try {
			$result = $this->builder->buildDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result);


		// Multiple constraints
		// ============================================================================================

		$args = array(

			array("col"=>"col_1", "op"=>"=", "val"=>53),
			array("col"=>"col_2", "op"=>"!=", "val"=>"test_val")
		);
		
		$check_args = array(
		    
			'query'=> "DELETE FROM {$table} WHERE 1 = 1 AND col_1 = %d AND col_2 != %s",
			'params'=> array(
					    array('escape'=>true, 'val'=>53, 'php'=>'int', 'sql'=>'smallint'),
					    array('escape'=>true, 'val'=>'test_val', 'php'=>'string', 'sql'=>'varchar')			    
			)		    
		);		

		try {
			$result = $this->builder->buildDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}	

		$this->assertEquals($check_args, $result);

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
		
		$check_args = array(
		    
			'query'=> "DELETE FROM {$table} WHERE 1 = 1 AND col_1 <> %d",
			'params'=> array(
					    array('escape'=>true, 'val'=>31, 'php'=>'int', 'sql'=>'smallint')		    
			)		    
		);		

		try {
			$result = $this->builder->buildDeleteQueryCol($struct, "col_1", "<>", 31);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}					

		$this->assertEquals($check_args, $result);
		

	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	
	
}

?>