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


class database_queryBuilders_select extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new FOX_queryBuilder($test_db);

	}


	function test_buildSelectQuery(){


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


		// No parameters set
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";
		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
			
		$this->assertEquals($query, $result['query']); 		
		$this->assertEquals(3, count($result["types"]));

		
		// Single column selected using INCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"include", "col"=>"col_1");

		$query  = "SELECT col_1 ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";

		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($query, $result['query']); 		
		$this->assertEquals(1, count($result["types"]));


		// Multiple columns selected using INCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_2") );

		$query  = "SELECT col_1, col_2 ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 		
		$this->assertEquals(2, count($result["types"]));


		// Single column skipped using EXCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"exclude", "col"=>"col_1");

		$query  = "SELECT col_2, col_3 ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 		
		$this->assertEquals(2, count($result["types"]));
		

		// Multiple columns skipped using EXCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT col_2 ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 		
		$this->assertEquals(1, count($result["types"]));


		// Single column constraint
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$args = array(

			 array("col"=>"col_1", "op"=>"=", "val"=>1)
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 AND col_1 = %d";

		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),		    
		);
		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 	
		$this->assertEquals(3, count($result["types"]));


		// Multiple column constraints
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$args = array(

			 array("col"=>"col_1", "op"=>"=", "val"=>1),
			 array("col"=>"col_2", "op"=>"=", "val"=>5)
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 AND col_1 = %d AND col_2 = %s";

		$params = array(
				array('escape'=>true, 'val'=>1, 'php'=>'int', 'sql'=>'smallint'),	
				array('escape'=>true, 'val'=>5, 'php'=>'string', 'sql'=>'varchar'),
		);

		try {
			$result = $this->builder->buildSelectQuery($struct, $args, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 
		$this->assertEquals($params, $result['params']); 	
		$this->assertEquals(3, count($result["types"]));


		// Count items, bool true 
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>true
		);

		$query  = "SELECT COUNT(*) ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";
				
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"])); 


		// Count items, single column as string
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>"col_3"
		);

		$query  = "SELECT COUNT(col_3) ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";
		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=false, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(0, count($result["types"])); 


		// Count items, multiple columns as array
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>array("col_1","col_3")
		);

		$query  = "SELECT COUNT(col_1), COUNT(col_3) ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=false, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(0, count($result["types"])); 


		// Sort items, single column
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>"DESC") )
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "ORDER BY col_1 DESC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"])); 


		// Sort items, single column, arbitrary order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>array(1,5,3,2,4)) )
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "ORDER BY FIND_IN_SET(col_1, '1,5,3,2,4')";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"])); 


		// Sort items, multiple columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>"DESC"),
				       array("col"=>"col_3", "sort"=>"ASC")
				     )
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "ORDER BY col_1 DESC, col_3 ASC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"])); 
		
		
		// Sort items, multiple columns, arbitrary sort order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>"DESC"),
				       array("col"=>"col_3", "sort"=>array(1,5,3,2,4))
				     )
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "ORDER BY col_1 DESC, FIND_IN_SET(col_3, '1,5,3,2,4')";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"])); 


		// Group items, single column
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("col"=>"col_1", "sort"=>"DESC") )
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "GROUP BY col_1 DESC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"]));


		// Group items, multiple columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("col"=>"col_1", "sort"=>"DESC"),
					array("col"=>"col_3", "sort"=>"ASC")
				     )
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "GROUP BY col_1 DESC, col_3 ASC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals(3, count($result["types"]));


		// Paging, limit number of returned results
		// ============================================================================================

		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'page'=>1,
			'per_page'=>7
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "LIMIT %d, %d";

		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"
		$params = array(
				array('escape'=>true, 'val'=>0, 'php'=>'int', 'sql'=>'int'),	
				array('escape'=>true, 'val'=>7, 'php'=>'int', 'sql'=>'int'),
		);

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(3, count($result["types"]));


		// Paging, offset by X pages
		// ============================================================================================


		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'page'=>5,
			'per_page'=>7
		);

		$query  = "SELECT * ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 ";
		$query .= "LIMIT %d, %d";

		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"
		$params = array(
				array('escape'=>true, 'val'=>28, 'php'=>'int', 'sql'=>'int'),	
				array('escape'=>true, 'val'=>7, 'php'=>'int', 'sql'=>'int'),
		);

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($query, $result['query']); 	
		$this->assertEquals($params, $result['params']); 		
		$this->assertEquals(3, count($result["types"])); 

		
	}


	function test_buildSelectQueryCol(){


		// This method is essentially a pass-through function to buildSelectQuery(), so very little testing is required
		// --------------------------------------------------------------------------------------------------------------

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


		// Multiple columns skipped, passed as array of strings
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);


		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$query  = "SELECT col_2 ";
		$query .= "FROM {$table} ";
		$query .= "WHERE 1 = 1 AND col_1 <> %d";

		$params = array(
				array('escape'=>true, 'val'=>37, 'php'=>'int', 'sql'=>'smallint'),
		);
	
		try {
			$result = $this->builder->buildSelectQueryCol($struct, "col_1", "<>", 37, $columns, $ctrl);
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