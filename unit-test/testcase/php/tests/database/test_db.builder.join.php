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


class database_queryBuilders_join extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new FOX_queryBuilder($test_db);

	}


	function test_buildSelectQueryJoin(){

	    
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

		$args_primary = array(

			array( "col"=>"col_1", "op"=>"=", "val"=>1)
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

			
		// No columns skipped
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$struct_primary_table = $this->base_prefix . $struct_primary["table"];
		$struct_join_table = $this->base_prefix . $struct_join["table"];

		$check_args = array();
		$query  = "SELECT DISTINCT {$struct_primary_table}.* FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);		

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(3, count($result["types"]));


		// Single column selected using INCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"include", "col"=>"col_1");

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_1 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Multiple columns selected using INCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_2") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_1, {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(2, count($result["types"]));


		// Single column skipped using EXCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"exclude", "col"=>"col_1");

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2, {$struct_primary_table}.col_3 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(2, count($result["types"]));


		// Multiple columns skipped using EXCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Count items, bool true
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>true
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT COUNT(DISTINCT {$struct_primary_table}.*) FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Count items, single primary table column
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>array( array("class"=>$struct_primary, "col"=>"col_1") )
		);

		$query  = "SELECT COUNT(DISTINCT {$struct_primary_table}.col_1) FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns=false, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(0, count($result["types"]));


		// Count items, multiple columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>array(
					array("class"=>$struct_primary, "col"=>"col_1"),
					array("class"=>$struct_join, "col"=>"col_6")
			)
		);

		$query  = "SELECT COUNT(DISTINCT {$struct_primary_table}.col_1), COUNT(DISTINCT {$struct_join_table}.col_6) ";
		$query .= "FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns=false, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(0, count($result["types"]));


		
		// Sort items, primary table used as sort class
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC") ) // Note that the column(s) we are ordering by must be in the
													 // returned data set, but, it is ok to use columns that are
													 // not when testing the string builders.
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_primary_table}.col_1 DESC";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Sort items, primary table used as sort class, arbitrary sort order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>array(1,5,3,2,4) ) )

			// Note that the column(s) we are ordering by must be in the
			// returned data set, but, it is ok to use columns that are
			// not when testing the string builders
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY FIND_IN_SET({$struct_primary_table}.col_1, '1,5,3,2,4')";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));



		// Sort items, primary table used as sort class, multiple sort columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC"),
				       array("class"=>$struct_primary, "col"=>"col_2", "sort"=>"ASC")   // Note that the column(s) we are ordering by must be in the
													// returned data set, but, it is ok to use columns that are
													// not when testing the string builders.
				     )
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_primary_table}.col_1 DESC, {$struct_primary_table}.col_2 ASC";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Sort items, primary table used as sort class, multiple sort columns, arbitrary sort order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC"),
				       array("class"=>$struct_primary, "col"=>"col_2", "sort"=>array(1,5,3,2,4))
				     )
			// Note that the column(s) we are ordering by must be in the
			// returned data set, but, it is ok to use columns that are
			// not when testing the string builders
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_primary_table}.col_1 DESC, FIND_IN_SET({$struct_primary_table}.col_2, '1,5,3,2,4')";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));



		// Sort items, joined table used as sort class
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_join, "col"=>"col_4", "sort"=>"DESC") )	// Note that the column(s) we are ordering by must be in the
													// returned data set, but, it is ok to use columns that are
													// not when testing the string builders.
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_join_table}.col_4 DESC";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));

		
		// Sort items, joined table used as sort class, arbitrary sort order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_join, "col"=>"col_4", "sort"=>array(1,5,3,2,4) ) )

			// Note that the column(s) we are ordering by must be in the
			// returned data set, but, it is ok to use columns that are
			// not when testing the string builders
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY FIND_IN_SET({$struct_join_table}.col_4, '1,5,3,2,4')";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));
		
		

		// Sort items, joined table used as sort class, multiple sort columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_join, "col"=>"col_4", "sort"=>"DESC"),
				       array("class"=>$struct_join, "col"=>"col_5", "sort"=>"ASC")  // Note that the column(s) we are ordering by must be in the
												    // returned data set, but, it is ok to use columns that are
												    // not when testing the string builders.
				     )
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_join_table}.col_4 DESC, {$struct_join_table}.col_5 ASC";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Sort items, joined table used as sort class, multiple sort columns, arbitrary sort order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_join, "col"=>"col_4", "sort"=>"DESC"),
				       array("class"=>$struct_join, "col"=>"col_5", "sort"=>array(1,5,3,2,4))
				     )

			// Note that the column(s) we are ordering by must be in the
			// returned data set, but, it is ok to use columns that are
			// not when testing the string builders
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_join_table}.col_4 DESC, FIND_IN_SET({$struct_join_table}.col_5, '1,5,3,2,4')";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));
		

		// Sort items, primary and joined tables used as sort classes
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("class"=>$struct_join, "col"=>"col_4", "sort"=>"DESC"),
				       array("class"=>$struct_primary, "col"=>"col_2", "sort"=>"ASC")
				     )
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "ORDER BY {$struct_join_table}.col_4 DESC, {$struct_primary_table}.col_2 ASC";
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));


		// Paging
		// ============================================================================================

		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'page'=>5,
			'per_page'=>7
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "LIMIT %d, %d";

		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"

		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	
				array('escape'=>true, 'val'=>28),
				array('escape'=>true, 'val'=>7),		    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));



		// Offset
		// ============================================================================================

		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'per_page'=>7,
			'offset'=>3
		);


		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT DISTINCT {$struct_primary_table}.col_2 FROM {$struct_primary_table} ";
		$query .= "INNER JOIN {$struct_join_table} AS alias_{$struct_join["table"]} ";
		$query .= "ON ({$struct_primary_table}.col_1 = alias_{$struct_join["table"]}.col_4) ";
		$query .= "WHERE 1 = 1 AND {$struct_primary_table}.col_1 = %d AND alias_{$struct_join["table"]}.col_6 >= %s ";
		$query .= "LIMIT %d, %d";

		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"
		
		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),
				array('escape'=>true, 'val'=>6, 'php'=>'string', 'sql'=>'varchar'),	
				array('escape'=>true, 'val'=>3),
				array('escape'=>true, 'val'=>7),		    
		);

		try {
			$result = $this->builder->buildSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(1, count($result["types"]));

	}

	
	function tearDown() {	

		parent::tearDown();		
	}
	
}


?>