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


class database_queryBuilders_joinLeft extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new FOX_queryBuilder($test_db);

	}

	
	function test_buildSelectQueryLeftJoin(){

	    
		// Primary array
		// ===================================================

		$struct_primary = array(

			"table" => "test_a",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
			 )
		);

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);



		// Join array
		// ===================================================

		$struct_join = array(

			"table" => "test_b",
			"engine" => "InnoDB",
			"columns" => array(
			    "col_4" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_5" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "col_6" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
			 )
		);


		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		


		// No columns skipped Joining two tables
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1";
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		
		
		// No columns skipped Joining three tables
		// ============================================================================================
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				),
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6, ";
		$check_args[0] .= "t3.col_4 AS t3col_4, t3.col_5 AS t3col_5, t3.col_6 AS t3col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t3 ";
		$check_args[0] .= "ON (t1.col_1 = t3.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1";
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]); 			
		$this->assertEquals(9, count($result["types"]));
		
		
		// No columns skipped Joining two tables with arguments
		// ============================================================================================
		
		$args_primary = array(

				    array( "col"=>"col_1", "op"=>"=", "val"=>1)
		);

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$args_join = array(

			array( "col"=>"col_6", "op"=>">=", "val"=>6)
		);
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				    )
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}			

		$this->assertEquals($check_args, $result["query"]); 		
		$this->assertEquals(6, count($result["types"]));
		
		
		// No columns skipped Joining two tables with arguments primary table using set alias
		// ============================================================================================
		
		$args_primary = array(

				    array( "col"=>"col_1", "op"=>"=", "val"=>1)
		);

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary, "alias"=>"t_1");
		
		$args_join = array(

			array( "col"=>"col_6", "op"=>">=", "val"=>6)
		);
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				    )
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT t_1.col_1 AS t_1col_1, t_1.col_2 AS t_1col_2, t_1.col_3 AS t_1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t_1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t_1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t_1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
				
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		
		
		// No columns skipped Joining two tables with arguments both tables use set aliases
		// ============================================================================================
		
		$args_primary = array(

				    array( "col"=>"col_1", "op"=>"=", "val"=>1)
		);

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary, "alias"=>"t_1");
		
		$args_join = array( 
		    
				array( "col"=>"col_6", "op"=>">=", "val"=>6)  
		);
		
		$join = array( 	
		    
			    array( "class"=>$struct_join, "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),"args"=>$args_join, "alias"=>"t_2" )  
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT t_1.col_1 AS t_1col_1, t_1.col_2 AS t_1col_2, t_1.col_3 AS t_1col_3, t_2.col_4 AS t_2col_4, t_2.col_5 AS t_2col_5, t_2.col_6 AS t_2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t_1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t_2 ";
		$check_args[0] .= "ON (t_1.col_1 = t_2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t_1.col_1 = %d AND t_2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;		
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]); 		
		$this->assertEquals(6, count($result["types"]));
		
		
		// Single column selected from each table with column and table aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);
		
		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("table_alias"=>"t2", "col_name"=>"col_4", "col_alias"=>"col2")
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, t2.col_4 AS col2 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(2, count($result["types"]));
		
		
		// Single column selected from each table without column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);
		
		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1"),
			    array("table_alias"=>"t2", "col_name"=>"col_4")
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t2.col_4 AS t2col_4 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(2, count($result["types"]));

		
		// Single column selected from each table without table aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);
		
		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$columns = array(
			    array("col_name"=>"col_1", "col_alias"=>"col1"),
			    array("col_name"=>"col_4", "col_alias"=>"col2")
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT col_1 AS col1, col_4 AS col2 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
								
		
		// Single column selected from each table without column and table aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);
		
		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$columns = array(
			    array("col_name"=>"col_1"),
			    array("col_name"=>"col_4")
		);
		
		$check_args = array();
		$check_args[0]  = "SELECT col_1, col_4 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		

		// Count on column of joined table with table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("table_alias"=>"t2", "col_name"=>"col_4", "col_alias"=>"col4", "count"=>true)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, COUNT( t2.col_4 ) AS col4 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($check_args, $result["query"]);
		

		// Count on column of joined table with table aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1"),
			    array("table_alias"=>"t2", "col_name"=>"col_4", "count"=>true)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, COUNT( t2.col_4 ) AS t2col_4 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		

		// Count on column of joined table without table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("col_name"=>"col_1"),
			    array("col_name"=>"col_4", "count"=>true)
		);

		$check_args = array();
		$check_args[0]  = "SELECT col_1, COUNT( col_4 ) ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		
		
		// Sum on column of joined table with table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("table_alias"=>"t2", "col_name"=>"col_4", "col_alias"=>"col4", "sum"=>true)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, SUM( t2.col_4 ) AS col4 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		
			
		// Sum on column of joined table without table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("col_name"=>"col_1"),
			    array("col_name"=>"col_4", "sum"=>true)
		);

		$check_args = array();
		$check_args[0]  = "SELECT col_1, SUM( col_4 ) ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		
		
		// Sum on multiple columns of joined table with table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("col_alias"=>"total", 
					"sum"=> array(
					    array("table_alias"=>"t2", "col_name"=>"col_4", "op"=>"+"), 
					    array("table_alias"=>"t2", "col_name"=>"col_5", "op"=>"+")
					)
				)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, SUM( t2.col_4 + t2.col_5 ) AS total ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		

		// Sum on multiple columns of joined table and one of the sum colums is a count column with table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("col_alias"=>"total", 
					"sum"=> array(
					    array("table_alias"=>"t2", "col_name"=>"col_4", "op"=>"+"), 
					    array("table_alias"=>"t2", "col_name"=>"col_5", "op"=>"+", "count"=>true)
					)
				)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, SUM( t2.col_4 + COUNT( t2.col_5 ) ) AS total ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		
		
		// Sum on multiple columns of joined table and one of the sum colums uses a column alias with table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("col_alias"=>"total", 
					"sum"=> array(
					    array("table_alias"=>"t2", "col_name"=>"col_4", "op"=>"+"), 
					    array("col_alias"=>"col5", "op"=>"+")
					)
				)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, SUM( t2.col_4 + col5 ) AS total ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		
		
		// Negative sum on multiple columns of joined table with table and column aliases
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array(
			    array("table_alias"=>"t1", "col_name"=>"col_1", "col_alias"=>"col1"),
			    array("col_alias"=>"total", 
					"sum"=> array(
					    array("table_alias"=>"t2", "col_name"=>"col_4", "op"=>"-"), 
					    array("table_alias"=>"t2", "col_name"=>"col_5", "op"=>"-")
					)
				)
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS col1, SUM(  - t2.col_4 - t2.col_5 ) AS total ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";		
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s";
		$check_args[1]  = 1;
		$check_args[2]  = 6;

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		

		// Sort by one column no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC") ) 
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "ORDER BY t1.col_1 DESC";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		

		// Sort by one column alias no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array( "col_alias"=>"t1col_1", "sort"=>"DESC") ) 
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "ORDER BY t1col_1 DESC";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		
		
		// Sort by two columns no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC"),
					array("class"=>$struct_join, "col"=>"col_4", "sort"=>"ASC")) 
				);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "ORDER BY t1.col_1 DESC, t2.col_4 ASC";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		
		
		// Sort by position in array for one column no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>array(5,4,3,2,1)))					
				);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "ORDER BY FIND_IN_SET(t1.col_1, '5,4,3,2,1')";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		

		// Group by one column no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC") ) 
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "GROUP BY t1.col_1 DESC";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		

		// Group by one column alias no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("class"=>$struct_primary, "col_alias"=>"t1col_1", "sort"=>"DESC") ) 
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "GROUP BY t1col_1 DESC";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		
		
		// Group by two columns no columns skipped Joining two tables
		// ============================================================================================

		$primary = array( "class"=>$struct_primary, "args"=>$args_primary);
		
		$join = array( 
				array(
				    "class"=>$struct_join,
				    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
				    "args"=>$args_join
				)
		);
		
		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC"),
					array("class"=>$struct_join, "col"=>"col_4", "sort"=>"ASC")
			    ) 
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "GROUP BY t1.col_1 DESC, t2.col_4 ASC";
		$check_args[1]  = 1;
		$check_args[2]  = 6;
		
		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]); 
		$this->assertEquals(6, count($result["types"]));
		

		// Paging
		// ============================================================================================

		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'page'=>5,
			'per_page'=>7
		);

		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "LIMIT %d, %d";
		$check_args[1]  = 1;
		$check_args[2]  = 6;		
		$check_args[3]  = 28;
		$check_args[4]  = 7;		
		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Offset
		// ============================================================================================

		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'per_page'=>7,
			'offset'=>3
		);


		$check_args = array();
		$check_args[0]  = "SELECT t1.col_1 AS t1col_1, t1.col_2 AS t1col_2, t1.col_3 AS t1col_3, t2.col_4 AS t2col_4, t2.col_5 AS t2col_5, t2.col_6 AS t2col_6 ";
		$check_args[0] .= "FROM {$struct_primary_table} AS t1 ";
		$check_args[0] .= "LEFT JOIN {$struct_join_table} AS t2 ";
		$check_args[0] .= "ON (t1.col_1 = t2.col_4) ";
		$check_args[0] .= "WHERE 1 = 1 AND t1.col_1 = %d AND t2.col_6 >= %s ";
		$check_args[0] .= "LIMIT %d, %d";
		$check_args[1]  = 1;
		$check_args[2]  = 6;		
		$check_args[3]  = 3;
		$check_args[4]  = 7;	

		try {
			$result = $this->builder->buildSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);	
		
	}

	
	function tearDown() {	

		parent::tearDown();		
	}	

}


?>