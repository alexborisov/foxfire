<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - QUERY BUILDERS | WHERE-MATRIX
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


class database_queryBuilders_whereMatrix extends RAZ_testCase {


    	function setUp() {

		parent::setUp();

		$test_db = new BPM_db();
		$this->builder = new BPM_queryBuilder($test_db);
		
		$this->struct = array(

			"table" => "bpm_test_bw",
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A AND C2 = B AND C3 = D AND ((C4 = E AND C5 IN(G,K,T)) OR (C4 = F AND C5 = I)))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A AND C2 = B AND C3 = C AND ((C4 = D AND C5 IN(E,T)) OR (C4 = I AND C5 = J)))";
		$test =  vsprintf($result['where'], $result['params']);

		$this->assertEquals($check, $test);

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
		catch (BPM_exception $child) {
		    
			
			$this->assertEquals(2, $child->data['numeric']);		    
		}
		
		// Check for correct output if the interlock is disabled
		
		try {
			$result = $this->builder->buildWhereMatrix($this->struct, $key_col, $args, $ctrl=array('optimize'=>false, 'trap_null'=>false));
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}			

		$check = " AND TRUE";
		$test =  vsprintf($result['where'], $result['params']);

		$this->assertEquals($check, $test);		

		
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
		catch (BPM_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$check = " AND (C2 = B AND C3 = C AND ((C4 = D AND C5 IN(E,T)) OR (C4 = I AND C5 = J)))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
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
		catch (BPM_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			
		$check = " AND ((C4 = D AND C5 IN(E,T)) OR (C4 = I AND C5 = J))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
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
		catch (BPM_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$check = " AND (C1 = A AND C2 = B AND C3 = C AND C4 IN(K,D,I))";
		$test =  vsprintf($result['where'], $result['params']);
		

		$this->assertEquals($check, $test);	
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A AND C2 = B AND C3 IN(K,E,F,D))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND C1 = A";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND C1 IN(A,B,C)";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR (C1 = B AND C2 = X) OR (C1 = C AND C2 = Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR C2 IN(X,Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR C2 IN(X,Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR C2 IN(X,Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C2 IN(X,Y) OR (C2 = G AND C1 = A) OR (C4 = K AND C5 = W AND C1 = K))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = T OR C2 IN(X,Y) OR (C4 = K AND C5 = W AND C1 = K) OR (C2 = G AND C1 = A))";
		$test =  vsprintf($result['where'], $result['params']);
		
		
		$this->assertEquals($check, $test);
		
		
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
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = T OR C2 IN(X,Y) OR (C2 = G AND C1 = A) OR (C4 = K AND C5 = W AND C1 = K))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);		
		

	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	
	

}

?>