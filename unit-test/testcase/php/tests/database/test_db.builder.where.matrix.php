<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - QUERY BUILDERS | WHERE-MATRIX
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


class database_queryBuilders_whereMatrix extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new FOX_db();
		$this->builder = new FOX_queryBuilder($test_db);
		
		$this->struct = array(

			"table" => "fox_test_bw",
			"engine" => "InnoDB",
			"columns" => array(
			    "C1" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "C2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "C3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "C4" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
			    "C5" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),			    
			 )
		);		

	}

	
	// C1 to C3 fully reducible. All other levels partially reducible
	// ============================================================================================
	
	function test_buildWhereMatrix_01(){
	    	
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"E", "C5"=>"G"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"E", "C5"=>"K"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"E", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"F", "C5"=>"I"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"F", "C5"=>"I"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"F", "C5"=>"I"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"F", "C5"=>"I")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s AND C2 = %s AND C3 = %s AND ((C4 = %s AND C5 IN(%s,%s,%s)) OR (C4 = %s AND C5 = %s)))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
				array('escape'=>true, 'val'=>'B'),
		    		array('escape'=>true, 'val'=>'D'),
		    		array('escape'=>true, 'val'=>'E'),
				array('escape'=>true, 'val'=>'G'),		    
		    		array('escape'=>true, 'val'=>'K'),		    
		    		array('escape'=>true, 'val'=>'T'),
		    		array('escape'=>true, 'val'=>'F'),	
		    		array('escape'=>true, 'val'=>'I'),
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
	}
		
	
	// Repeated rows
	// ============================================================================================
	
	function test_buildWhereMatrix_02(){		
		
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I", "C5"=>"J")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s AND C2 = %s AND C3 = %s AND ((C4 = %s AND C5 IN(%s,%s)) OR (C4 = %s AND C5 = %s)))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
				array('escape'=>true, 'val'=>'B'),
		    		array('escape'=>true, 'val'=>'C'),
		    		array('escape'=>true, 'val'=>'D'),
				array('escape'=>true, 'val'=>'E'),		    
		    		array('escape'=>true, 'val'=>'T'),		    
		    		array('escape'=>true, 'val'=>'I'),
		    		array('escape'=>true, 'val'=>'J'),	
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']); 

	}
	
	
	// Null row 
	// ============================================================================================	
	
	function test_buildWhereMatrix_03(){
	    
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array(),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I", "C5"=>"J")
		);


		// Check the safety interlock fires
		
		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->assertEquals(2, $child->data['numeric']);		    
		}
		
		// Check for correct output if the interlock is disabled
		
		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false, 'trap_null'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}			

		$query = " AND TRUE";
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals(array(), $result['params']); 		

		
	}
		
	
	// Null L1 column
	// ============================================================================================	
	
	function test_buildWhereMatrix_04(){
				

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array("C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
				array("C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),		    
				array("C2"=>"B", "C3"=>"C", "C4"=>"I", "C5"=>"J")
		);

		
		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$query = " AND (C2 = %s AND C3 = %s AND ((C4 = %s AND C5 IN(%s,%s)) OR (C4 = %s AND C5 = %s)))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'B'),
				array('escape'=>true, 'val'=>'C'),
		    		array('escape'=>true, 'val'=>'D'),
		    		array('escape'=>true, 'val'=>'E'),
				array('escape'=>true, 'val'=>'T'),		    		    
		    		array('escape'=>true, 'val'=>'I'),
		    		array('escape'=>true, 'val'=>'J'),	
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']); 
		
	}
	
	
	// Null L1 -> L3 columns
	// ============================================================================================
	
	function test_buildWhereMatrix_05(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array("C4"=>"D", "C5"=>"E"),
				array("C4"=>"D", "C5"=>"T"),		    
				array("C4"=>"I", "C5"=>"J")
		);

		
		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			
		$query = " AND ((C4 = %s AND C5 IN(%s,%s)) OR (C4 = %s AND C5 = %s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'D'),
		    		array('escape'=>true, 'val'=>'E'),
				array('escape'=>true, 'val'=>'T'),		    		    
		    		array('escape'=>true, 'val'=>'I'),
		    		array('escape'=>true, 'val'=>'J'),	
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
	}
	
	
	// Null L5 column
	// ============================================================================================	
	
	function test_buildWhereMatrix_06(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"K"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D"),		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I")
		);
		
		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$query = " AND (C1 = %s AND C2 = %s AND C3 = %s AND C4 IN(%s,%s,%s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
		    		array('escape'=>true, 'val'=>'B'),
				array('escape'=>true, 'val'=>'C'),		    		    
		    		array('escape'=>true, 'val'=>'K'),
		    		array('escape'=>true, 'val'=>'D'),
		    		array('escape'=>true, 'val'=>'I'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);	
		
	}

	
	// L3 end nodes that clip L4 branches
	// ============================================================================================
	
	function test_buildWhereMatrix_07(){
    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"K"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"E"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"F"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"H"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"I"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"J")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s AND C2 = %s AND C3 IN(%s,%s,%s,%s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
		    		array('escape'=>true, 'val'=>'B'),
				array('escape'=>true, 'val'=>'K'),		    		    
		    		array('escape'=>true, 'val'=>'E'),
		    		array('escape'=>true, 'val'=>'F'),
		    		array('escape'=>true, 'val'=>'D'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);	
		
	}
		
	
	// L1 end node that clips the entire tree
	// ============================================================================================
	
	function test_buildWhereMatrix_08(){		
		

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(

				array( "C1"=>"A"				 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"H"		 ),
				array( "C1"=>"A", "C2"=>"C", "C3"=>"I"		 ),
				array( "C1"=>"A", "C2"=>"D", "C3"=>"J"		 ),
				array( "C1"=>"A", "C2"=>"E", "C3"=>"K", "C4"=>"H"),
				array( "C1"=>"A", "C2"=>"F", "C3"=>"L", "C4"=>"I"),
				array( "C1"=>"A", "C2"=>"G", "C3"=>"M", "C4"=>"J")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND C1 = %s";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
	}

	
	// Multiple L1 branches that clip all lower levels of the tree
	// ============================================================================================
	
	function test_buildWhereMatrix_09(){
	    
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A"				 ),		    
				array( "C1"=>"B"				 ),
				array( "C1"=>"B", "C2"=>"C", "C3"=>"I"		 ),
				array( "C1"=>"B", "C2"=>"D", "C3"=>"J"		 ),		   
				array( "C1"=>"C"				 ),
				array( "C1"=>"C", "C2"=>"F", "C3"=>"L", "C4"=>"I"),
				array( "C1"=>"C", "C2"=>"G", "C3"=>"M", "C4"=>"J")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND C1 IN(%s,%s,%s)";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
		    		array('escape'=>true, 'val'=>'B'),
				array('escape'=>true, 'val'=>'C'),		    		    		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);	
		
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches.
	// ============================================================================================
	
	function test_buildWhereMatrix_10(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A"				 ),		    
				array( "C1"=>"B", "C2"=>"X"			 ),
				array( "C1"=>"B", "C2"=>"X", "C3"=>"I"		 ),
				array( "C1"=>"B", "C2"=>"X", "C3"=>"J"		 ),		    
				array( "C1"=>"C", "C2"=>"Y"			 ),
				array( "C1"=>"C", "C2"=>"Y", "C3"=>"L", "C4"=>"I"),
				array( "C1"=>"C", "C2"=>"Y", "C3"=>"M", "C4"=>"J")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s OR (C1 = %s AND C2 = %s) OR (C1 = %s AND C2 = %s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
		    		array('escape'=>true, 'val'=>'B'),
				array('escape'=>true, 'val'=>'X'),
		    		array('escape'=>true, 'val'=>'C'),
				array('escape'=>true, 'val'=>'Y'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']); 
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Descending entropy levels on sub-branches.
	// ============================================================================================
	
	function test_buildWhereMatrix_11(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A"					    ),		    
				array(		  "C2"=>"X"				    ),
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"W", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"T", "C2"=>"X", "C3"=>"T", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"Z", "C2"=>"X", "C3"=>"Z", "C4"=>"Z", "C5"=>"K"),		    		    
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"W", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"T", "C2"=>"Y", "C3"=>"T", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"Z", "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"K")
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s OR C2 IN(%s,%s))";
				
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
				array('escape'=>true, 'val'=>'X'),
				array('escape'=>true, 'val'=>'Y'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Ascending entropy levels on sub-branches.
	// ============================================================================================
	
	function test_buildWhereMatrix_12(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A"					    ),		    
				array(		  "C2"=>"X"				    ),
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array( "C1"=>"K", "C2"=>"X", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z"),		    		    
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s OR C2 IN(%s,%s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
				array('escape'=>true, 'val'=>'X'),
				array('escape'=>true, 'val'=>'Y'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
	}
		
	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Reversed null count ordering.
	// ============================================================================================
	
	function test_buildWhereMatrix_13(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A"					    ),		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K", "C2"=>"X",		"C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"X",			   "C5"=>"T"),
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),		    		    
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s OR C2 IN(%s,%s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'A'),
				array('escape'=>true, 'val'=>'X'),
				array('escape'=>true, 'val'=>'Y'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
	}

	
	// Column grouping
	// ============================================================================================
	
	function test_buildWhereMatrix_14(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A", "C2"=>"G"				    ),		    		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K",			"C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"X",			   "C5"=>"T"),
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),		    
		    
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);

		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C2 IN(%s,%s) OR (C2 = %s AND C1 = %s) OR (C4 = %s AND C5 = %s AND C1 = %s))";
		
		$params = array(
				array('escape'=>true, 'val'=>'X'),
				array('escape'=>true, 'val'=>'Y'),		    
		    		array('escape'=>true, 'val'=>'G'),
				array('escape'=>true, 'val'=>'A'),
				array('escape'=>true, 'val'=>'K'),	
		    		array('escape'=>true, 'val'=>'W'),
				array('escape'=>true, 'val'=>'K'),
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);	
		
	}

	
	// Column grouping
	// ============================================================================================
	
	function test_buildWhereMatrix_15(){
		

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"T"					    ),		    
				array( "C1"=>"A", "C2"=>"G"				    ),		    		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K",			"C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"X",			   "C5"=>"T"),
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),		    		    
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);

		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s OR C2 IN(%s,%s) OR (C4 = %s AND C5 = %s AND C1 = %s) OR (C2 = %s AND C1 = %s))";
		
		$params = array(
				array('escape'=>true, 'val'=>'T'),
				array('escape'=>true, 'val'=>'X'),		    
		    		array('escape'=>true, 'val'=>'Y'),
				array('escape'=>true, 'val'=>'K'),
				array('escape'=>true, 'val'=>'W'),	
		    		array('escape'=>true, 'val'=>'K'),
				array('escape'=>true, 'val'=>'G'),
				array('escape'=>true, 'val'=>'A'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
		
	}

	
	// First item key in $end_groups not equal to zero, with only one item in $end_groups array.
	// ============================================================================================
	
	function test_buildWhereMatrix_16(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				array( "C1"=>"A", "C2"=>"G"				    ),
				array( "C1"=>"T"					    ),		    		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K",			"C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"X",			   "C5"=>"T"),
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),		    		    
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);


		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$query = " AND (C1 = %s OR C2 IN(%s,%s) OR (C2 = %s AND C1 = %s) OR (C4 = %s AND C5 = %s AND C1 = %s))";
		
		$params = array(
				array('escape'=>true, 'val'=>'T'),
				array('escape'=>true, 'val'=>'X'),		    
		    		array('escape'=>true, 'val'=>'Y'),
				array('escape'=>true, 'val'=>'G'),
				array('escape'=>true, 'val'=>'A'),	
		    		array('escape'=>true, 'val'=>'K'),
				array('escape'=>true, 'val'=>'W'),
				array('escape'=>true, 'val'=>'K'),		    
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);		
		

	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	
	

}

?>