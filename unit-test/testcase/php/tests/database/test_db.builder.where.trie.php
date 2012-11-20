<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - QUERY BUILDERS | WHERE-TRIE
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class database_queryBuilders_whereTrie extends RAZ_testCase {


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
	
	function test_buildWhereTrie_01(){
	    	
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    'E'=>array(
										'G'=>true,
										'K'=>true,
										'T'=>true
								    ),
								    'F'=>array(	'I'=>true )
				)))
		);  


		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A AND C2 = B AND C3 = D AND ((C4 = E AND C5 IN(G,K,T)) OR (C4 = F AND C5 = I)))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
	}
		
	
	// Repeated rows
	// ============================================================================================
	
	function test_buildWhereTrie_02(){		
	
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);		


		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A AND C2 = B AND C3 = C AND ((C4 = D AND C5 IN(E,T)) OR (C4 = I AND C5 = J)))";
		$test =  vsprintf($result['where'], $result['params']);

		$this->assertEquals($check, $test);

	}
	
	
	// Null row 
	// ============================================================================================	
	
	function test_buildWhereTrie_03(){
   
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'*'=>true,
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);			

		// Check the safety interlock fires
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->assertEquals(2, $child->data['numeric']);		    
		}
		
		// Check for correct output if the interlock is disabled
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false, 'trap_null'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}			

		$check = " AND TRUE";
		$test =  vsprintf($result['where'], $result['params']);

		$this->assertEquals($check, $test);		

		
	}
		
	
	// Null L1 column
	// ============================================================================================	
	
	function test_buildWhereTrie_04(){
			

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'*'=>array( 'B'=>array( 'C'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);		

		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$check = " AND (C2 = B AND C3 = C AND ((C4 = D AND C5 IN(E,T)) OR (C4 = I AND C5 = J)))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
	}
	
	
	// Null L1 -> L3 columns
	// ============================================================================================
	
	function test_buildWhereTrie_05(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");
		
		$args = array(
				'*'=>array( '*'=>array( '*'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);
		
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			
		$check = " AND ((C4 = D AND C5 IN(E,T)) OR (C4 = I AND C5 = J))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
	}
	
	
	// Null L5 column, "Missing Key" syntax, end nodes as "true"
	// ============================================================================================	
	
	function test_buildWhereTrie_06(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'K'=>true,							    
								    'D'=>true,
								    'I'=>true
				)))
		);		
		
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$check = " AND (C1 = A AND C2 = B AND C3 = C AND C4 IN(K,D,I))";
		$test =  vsprintf($result['where'], $result['params']);
		

		$this->assertEquals($check, $test);	
		
	}
	
	
	// Null L5 column, "Missing Key" syntax, end nodes as "array"
	// ============================================================================================	
	
	function test_buildWhereTrie_07(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'K'=>array(),							    
								    'D'=>array(),
								    'I'=>array()
				)))
		);		
		
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$check = " AND (C1 = A AND C2 = B AND C3 = C AND C4 IN(K,D,I))";
		$test =  vsprintf($result['where'], $result['params']);
		

		$this->assertEquals($check, $test);	
		
	}
	
	
	// Null L5 column, "Wildcard" syntax
	// ============================================================================================	
	
	function test_buildWhereTrie_08(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'K'=>array(	'*'=>true ),							    
								    'D'=>array(	'*'=>true ),
								    'I'=>array(	'*'=>true )
				)))
		);		
		
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			
			$this->fail($child->dumpString(1));		    
		}
			

		$check = " AND (C1 = A AND C2 = B AND C3 = C AND C4 IN(K,D,I))";
		$test =  vsprintf($result['where'], $result['params']);
		

		$this->assertEquals($check, $test);	
		
	}	
	

	
	// L3 end nodes that clip L4 branches
	// ============================================================================================
	
	function test_buildWhereTrie_09(){
	  

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array( 'B'=>array(						
							'K'=>true,
							'E'=>true,
							'F'=>true,						
							'D'=>array( 
								    '*'=>true,
								    'H'=>true,
								    'I'=>true,
								    'J'=>true,							    							    
							 )
				))
		);
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A AND C2 = B AND C3 IN(K,E,F,D))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
	}
		
	
	// L1 end node that clips the entire tree
	// ============================================================================================
	
	function test_buildWhereTrie_10(){		
		
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>array(
					    '*'=>true,
					    'B'=>array(	'H'=>true ),	
					    'C'=>array(	'I'=>true ),
					    'D'=>array(	'J'=>true ),	
					    'E'=>array(	'K'=>array( 'H'=>true )),
					    'F'=>array(	'L'=>array( 'I'=>true )),
					    'G'=>array(	'M'=>array( 'J'=>true ))
				 )
		);		

		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND C1 = A";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
	}

	
	// Multiple L1 branches that clip all lower levels of the tree
	// ============================================================================================
	
	function test_buildWhereTrie_11(){
	    
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>true,
				'B'=>array(
					    '*'=>true,			    
					    'C'=>array(	'I'=>true ),
					    'D'=>array(	'J'=>true )
				 ),
				'C'=>array(
					    '*'=>true,			    
					    'F'=>array(	'L'=>array( 'I'=>true )),
					    'G'=>array(	'M'=>array( 'J'=>true ))
				 )		    
		);
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND C1 IN(A,B,C)";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches.
	// ============================================================================================
	
	function test_buildWhereTrie_12(){
	    
	
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>true,
				'B'=>array( 'X'=>array(
							'*'=>true,
							'I'=>true,
							'J'=>true						
				)),
				'C'=>array( 'Y'=>array(
							'*'=>true,
							'L'=>array( 'I'=>true ),						
							'M'=>array( 'J'=>true )						
				))		    
		);
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>false));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR (C1 = B AND C2 = X) OR (C1 = C AND C2 = Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Descending entropy levels on sub-branches.
	// ============================================================================================
	
	function test_buildWhereTrie_13(){
	    
	    
		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>true,
				'*'=>array(
					    'X'=>true,
					    'Y'=>true
				),			
				'K'=>array(		    
					    'X'=>array(	'K'=>array( 'K'=>array(	'K'=>true ))),
					    'Y'=>array(	'K'=>array( 'K'=>array(	'K'=>true )))				    
				 ),
				'W'=>array(		    
					    'X'=>array(	'K'=>array( 'K'=>array(	'K'=>true ))),
					    'Y'=>array(	'K'=>array( 'K'=>array(	'K'=>true )))				    
				 ),
				'T'=>array(		    
					    'X'=>array(	'T'=>array( 'K'=>array(	'K'=>true ))),
					    'Y'=>array(	'T'=>array( 'K'=>array(	'K'=>true )))				    
				 ),		    
				'Z'=>array(		    
					    'X'=>array(	'Z'=>array( 'Z'=>array(	'K'=>true ))),
					    'Y'=>array(	'Z'=>array( 'Z'=>array(	'K'=>true )))				    
				 )		    		    
		);
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR C2 IN(X,Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Ascending entropy levels on sub-branches.
	// ============================================================================================
	
	function test_buildWhereTrie_14(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");
		
		$args = array(
				'A'=>true,
				'*'=>array(
					    'X'=>true,
					    'Y'=>true
				),			
				'K'=>array(		    
					    'X'=>array(	
							'K'=>array( 
								    'K'=>array(	
										'K'=>true,
										'W'=>true
	    							    ),
								    'T'=>array(	'T'=>true )							    
							),
							'Z'=>array( 'Z'=>array( 'Z'=>true )) 						
					     ),	
					    'Y'=>array(	
							'K'=>array( 
								    'K'=>array(	
										'K'=>true,
										'W'=>true
	    							    ),
								    'T'=>array(	'T'=>true )							    
							),
							'Z'=>array( 'Z'=>array( 'Z'=>true )) 						
					     ),					    
				 )		    		    
		);		


		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR C2 IN(X,Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);
		
	}
		
	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Reversed null count ordering.
	// ============================================================================================
	
	function test_buildWhereTrie_15(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(
				'A'=>true,
				'K'=>array(		    
					    'X'=>array(	'*'=>array( 'K'=>array(	'W'=>true ))),
					    'Y'=>array(	'K'=>array( 'K'=>array(	'K'=>true )))											    
				 ),
				'*'=>array(		    
					    'X'=>array(	'*'=>array( 
								    '*'=>array(	
										'*'=>true,
										'T'=>true 
								    ),						
								    'Z'=>array(	'*'=>true ),
								    'K'=>array(	'K'=>true )						
							)						
					    ),
					    'Y'=>array(	
							'*'=>true,
							'K'=>array( 
								    'K'=>array(	'W'=>true ),
								    'T'=>array(	'T'=>true )							    
							 ),
							'Z'=>array( 'Z'=>array(	'Z'=>true ))						
					    )											    
				 )		    		    		    
		);		

		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = A OR C2 IN(X,Y))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
	}

	
	// Column grouping
	// ============================================================================================
	
	function test_buildWhereTrie_16(){


		$key_col = array("C1", "C2", "C3", "C4", "C5");
		
		$args = array(
				'A'=>array( 'G'=>true ),			
				'K'=>array(
					    '*'=>array(	'*'=>array( 'K'=>array(	'W'=>true ))),				    
					    'Y'=>array(	'K'=>array( 'K'=>array(	'K'=>true ))),
					    
				 ),
				'*'=>array(
					    'Y'=>array(
							'*'=>true,
							'K'=>array(
								    'K'=>array(	'W'=>true ),
								    'T'=>array(	'T'=>true )						
							 ),
							'Z'=>array( 'Z'=>array(	'Z'=>true ))						
					     ),
					    'X'=>array(	'*'=>array(
								    '*'=>array(
										'*'=>true,
										'T'=>true
								     ),
								    'K'=>array( 'K'=>true ),
								    'Z'=>true						
					     ))
				 )		    
		);		

		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C2 IN(Y,X) OR (C2 = G AND C1 = A) OR (C4 = K AND C5 = W AND C1 = K))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);	
		
	}

	
	// Column grouping
	// ============================================================================================
	
	function test_buildWhereTrie_17(){
		

		$key_col = array("C1", "C2", "C3", "C4", "C5");
		
		
		$args = array(	'T'=>true,
				'A'=>array( 'G'=>true),
				'K'=>array(		    
					    '*'=>array(	'*'=>array( 'K'=>array(	'W'=>true ))),
					    'Y'=>array(	'K'=>array( 'K'=>array(	'K'=>true ))),											    
				 ),
				'*'=>array(
					    'X'=>array(	'*'=>array( 
								    '*'=>array(	
										'*'=>true,
										'T'=>true 
								    ),						
								    'Z'=>array(	'*'=>true ),
								    'K'=>array(	'K'=>true )						
							),
						
					    ),				    
					    'Y'=>array(	
							'*'=>true,
							'K'=>array( 
								    'K'=>array(	'W'=>true ),
								    'T'=>array(	'T'=>true )							    
							 ),
							'Z'=>array( 'Z'=>array(	'Z'=>true ))						
					    )											    
				 )	    
		    		    
		);		

		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = T OR C2 IN(Y,X) OR (C4 = K AND C5 = W AND C1 = K) OR (C2 = G AND C1 = A))";
		$test =  vsprintf($result['where'], $result['params']);
		
		
		$this->assertEquals($check, $test);
		
		
	}

	
	// First item key in $end_groups not equal to zero, with only one item in $end_groups array.
	// ============================================================================================
	
	function test_buildWhereTrie_18(){
	    

		$key_col = array("C1", "C2", "C3", "C4", "C5");

		$args = array(	
				'A'=>array( 'G'=>true),
				'T'=>true,
				'K'=>array(		    
					    '*'=>array(	'*'=>array( 'K'=>array(	'W'=>true ))),
					    'Y'=>array(	'K'=>array( 'K'=>array(	'K'=>true )))											    
				 ),
				'*'=>array(		    
					    'X'=>array(	'*'=>array( 
								    '*'=>array(	
										'*'=>true,
										'T'=>true 
								    ),						
								    'Z'=>array(	'*'=>true ),
								    'K'=>array(	'K'=>true )						
							)						
					    ),
					    'Y'=>array(	
							'*'=>true,
							'K'=>array( 
								    'K'=>array(	'W'=>true ),
								    'T'=>array(	'T'=>true )							    
							 ),
							'Z'=>array( 'Z'=>array(	'Z'=>true ))						
					    )											    
				 )		    		    		    
		);
		
		try {
			$result = $this->builder->buildWhereTrie($this->struct, $key_col, $args, $ctrl=array('optimize'=>true));
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}						

		$check = " AND (C1 = T OR C2 IN(Y,X) OR (C2 = G AND C1 = A) OR (C4 = K AND C5 = W AND C1 = K))";
		$test =  vsprintf($result['where'], $result['params']);
		
		$this->assertEquals($check, $test);		
		

	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	
	

}

?>