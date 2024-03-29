<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - QUERY BUILDERS | WHERE-TRIE
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

		$query = " AND TRUE";

		$this->assertEquals($query, $result['where']);
		$this->assertEquals(array(), $result['params']);

		
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

		$query = " AND C1 = %s";
		
		$params = array(						
				array('escape'=>true, 'val'=>'A'),
		);	
		
		$this->assertEquals($query, $result['where']);
		$this->assertEquals($params, $result['params']);
		
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

		$query = " AND (C2 IN(%s,%s) OR (C2 = %s AND C1 = %s) OR (C4 = %s AND C5 = %s AND C1 = %s))";
		
		$params = array(						
				array('escape'=>true, 'val'=>'Y'),
		    		array('escape'=>true, 'val'=>'X'),		    
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

		$query = " AND (C1 = %s OR C2 IN(%s,%s) OR (C4 = %s AND C5 = %s AND C1 = %s) OR (C2 = %s AND C1 = %s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'T'),
				array('escape'=>true, 'val'=>'Y'),
		    		array('escape'=>true, 'val'=>'X'),
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

		$query = " AND (C1 = %s OR C2 IN(%s,%s) OR (C2 = %s AND C1 = %s) OR (C4 = %s AND C5 = %s AND C1 = %s))";
		
		$params = array(
		    		array('escape'=>true, 'val'=>'T'),
				array('escape'=>true, 'val'=>'Y'),
		    		array('escape'=>true, 'val'=>'X'),
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