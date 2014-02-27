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


class database_queryBuilders_table extends RAZ_testCase {


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


	function test_buildAddTable_indexUnique(){


		$struct = array(

			"table" => "fox_test_index_unique",
			"engine" => "InnoDB",
			"columns" => array(
			    "name" =>	array(	"php"=>"string", "sql"=>"varchar", "format"=>"%s", "width"=>250,    "flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE")
			 )
		);

		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( name varchar(250), UNIQUE KEY name (name) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_string, $result);
		
	}


	function test_buildAddTable_indexPrimaryKey(){

		$struct = array(

			"table" => "fox_test_index_primary",
			"engine" => "InnoDB",
			"columns" => array(
			    "id" =>	array(	"php"=>"int", "sql"=>"smallint", "format"=>"%d", "width"=>6, "flags"=>null, "auto_inc"=>false,  "default"=>null,  "index"=>"PRIMARY")
			 )
		);

		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( id smallint(6), PRIMARY KEY (id) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}


	function test_buildAddTable_indexFullText(){

		$struct = array(

			"table" => "fox_test_index_primary",
			"engine" => "MyISAM",
			"columns" => array(
			    "id" =>	array(	"php"=>"int", "sql"=>"smallint", "format"=>"%d", "width"=>6, "flags"=>null, "auto_inc"=>false,  "default"=>null,  "index"=>"FULLTEXT")
			 )
		);

		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( id smallint(6), FULLTEXT KEY (id) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}


	function test_buildAddTable_indexDefault(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>true),
			    "files" =>	array(	"php"=>"int", "sql"=>"mediumint", "format"=>"%d", "width"=>7, "flags"=>null, "auto_inc"=>false, "default"=>null,   "index"=>false)
			 )
		);


		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2), files mediumint(7), KEY priv (priv) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}


	function test_buildAddTable_flagAutoIncrement(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>null, "auto_inc"=>true, "default"=>null,   "index"=>true)
			 )
		);


		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2) AUTO_INCREMENT, KEY priv (priv) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);
		
	}


	
	function test_buildAddTable_flagDefaultValue(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>null, "auto_inc"=>false, "default"=>"test_val", "index"=>true)
			 )
		);

		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2) DEFAULT 'test_val', KEY priv (priv) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		$result = $this->builder->buildAddTable($struct);

		$this->assertEquals($check_string, $result);

	}


	
	function test_buildAddTable_flagNullOK(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null, "index"=>false)
			 )
		);

		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2) NOT NULL ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}

	function test_buildAddTable_compositeKey(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,
							"index"=>array("name"=>"composite_1", "col"=>array("col_1", "col_2"), "index"=>"UNIQUE"))
			 )
		);

		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2) NOT NULL, UNIQUE KEY composite_1 (col_1, col_2) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}

	function test_buildAddTable_compositeKey_secondaryIndex_A(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,
							"index"=>array("name"=>"composite_1", "col"=>array("col_1", "col_2"), "index"=>"PRIMARY", "this_row"=>"UNIQUE") )
			 )
		);

		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2) NOT NULL, PRIMARY KEY composite_1 (col_1, col_2), UNIQUE KEY priv (priv) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}

	function test_buildAddTable_compositeKey_secondaryIndex_B(){

		$struct = array(

			"table" => "fox_test_index_default",
			"engine" => "InnoDB",
			"columns" => array(
			    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,
							"index"=>array("name"=>"composite_1", "col"=>array("col_1", "col_2"), "index"=>"UNIQUE", "this_row"=>true) )
			 )
		);
		
		$table_name = $this->base_prefix . $struct["table"];

		$check_string = "CREATE TABLE {$table_name} ( priv tinyint(2) NOT NULL, UNIQUE KEY composite_1 (col_1, col_2), KEY priv (priv) ) ENGINE={$struct["engine"]} {$this->charset_collate};";
		
		try {
			$result = $this->builder->buildAddTable($struct);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_string, $result);

	}

	
	function tearDown() {	

		parent::tearDown();		
	}	

}


?>