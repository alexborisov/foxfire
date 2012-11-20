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


class database_queryBuilders_select extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new BPM_db();

		$this->base_prefix = $test_db->base_prefix;
		$this->builder = new BPM_queryBuilder($test_db);

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

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";
		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
			
		$this->assertEquals($check_args, $result["query"]);

		
		// Single column selected using INCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"include", "col"=>"col_1");

		$check_args = array();
		$check_args[0]  = "SELECT col_1 ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";

		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$this->assertEquals($check_args, $result["query"]);


		// Multiple columns selected using INCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"include", "col"=>array("col_1", "col_2") );

		$check_args = array();
		$check_args[0]  = "SELECT col_1, col_2 ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Single column skipped using EXCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"exclude", "col"=>"col_1");

		$check_args = array();
		$check_args[0]  = "SELECT col_2, col_3 ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		

		// Multiple columns skipped using EXCLUDE mode
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

		$check_args = array();
		$check_args[0]  = "SELECT col_2 ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Single column constraint
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null
		);

		$args = array(

			 array("col"=>"col_1", "op"=>"=", "val"=>1)
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 AND col_1 = %d";

		$check_array = array(1);
		$check_args = array_merge($check_args, $check_array);
		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals($check_args, $result["query"]);


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

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 AND col_1 = %d AND col_2 = %s";

		$check_array = array(1, 5);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildSelectQuery($struct, $args, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Count items, bool true 
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>true
		);

		$check_args = array();
		$check_args[0]  = "SELECT COUNT(*) ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";
				
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$this->assertEquals($check_args, $result["query"]);


		// Count items, single column as string
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>"col_3"
		);

		$check_args = array();
		$check_args[0]  = "SELECT COUNT(col_3) ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";
		
		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=false, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				

		$this->assertEquals($check_args, $result["query"]);


		// Count items, multiple columns as array
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'count'=>array("col_1","col_3")
		);

		$check_args = array();
		$check_args[0]  = "SELECT COUNT(col_1), COUNT(col_3) ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=false, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Sort items, single column
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>"DESC") )
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "ORDER BY col_1 DESC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Sort items, single column, arbitrary order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>array(1,5,3,2,4)) )
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "ORDER BY FIND_IN_SET(col_1, '1,5,3,2,4')";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Sort items, multiple columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>"DESC"),
				       array("col"=>"col_3", "sort"=>"ASC")
				     )
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "ORDER BY col_1 DESC, col_3 ASC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);
		
		
		// Sort items, multiple columns, arbitrary sort order
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'sort'=>array( array("col"=>"col_1", "sort"=>"DESC"),
				       array("col"=>"col_3", "sort"=>array(1,5,3,2,4))
				     )
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "ORDER BY col_1 DESC, FIND_IN_SET(col_3, '1,5,3,2,4')";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Group items, single column
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("col"=>"col_1", "sort"=>"DESC") )
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "GROUP BY col_1 DESC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Group items, multiple columns
		// ============================================================================================

		$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
			'page'=>null,
			'per_page'=>null,
			'group'=>array( array("col"=>"col_1", "sort"=>"DESC"),
					array("col"=>"col_3", "sort"=>"ASC")
				     )
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "GROUP BY col_1 DESC, col_3 ASC";

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Paging, limit number of returned results
		// ============================================================================================

		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'page'=>1,
			'per_page'=>7
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "LIMIT %d, %d";

		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"
		$check_array = array(0, 7);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);


		// Paging, offset by X pages
		// ============================================================================================


		$ctrl = array(
			// Not setting the "sort" flag is completely valid. The db will just page through the
			// items in whatever order they are stored in the table.
			'page'=>5,
			'per_page'=>7
		);

		$check_args = array();
		$check_args[0]  = "SELECT * ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 ";
		$check_args[0] .= "LIMIT %d, %d";

		// SQL format for LIMIT construct: "LIMIT [offset from zero], [max records to return]"
		$check_array = array(28, 7);
		$check_args = array_merge($check_args, $check_array);

		try {
			$result = $this->builder->buildSelectQuery($struct, $args=null, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($check_args, $result["query"]);

		
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

		$check_args = array();
		$check_args[0]  = "SELECT col_2 ";
		$check_args[0] .= "FROM {$table} ";
		$check_args[0] .= "WHERE 1 = 1 AND col_1 <> %d";

		$check_array = array(37);
		$check_args = array_merge($check_args, $check_array);

		
		try {
			$result = $this->builder->buildSelectQueryCol($struct, "col_1", "<>", 37, $columns, $ctrl);
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