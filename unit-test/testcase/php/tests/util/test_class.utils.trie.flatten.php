<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - TRIE UTILITIES | FLATTEN
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


class util_trie_flatten extends RAZ_testCase {


    	function setUp() {

		parent::setUp();		
	}

	
	// C1 to C3 fully reducible. All other levels partially reducible
	// ============================================================================================
	
	function test_trie_flatten_01(){
	    	
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"E", "C5"=>"G"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"E", "C5"=>"K"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"E", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"F", "C5"=>"I")
		);
		
		$this->assertEquals($check, $result);
		
	}
			
	
	// Null row 
	// ============================================================================================	
	
	function test_trie_flatten_02(){
   
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'*'=>true,
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);			

		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array(),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I", "C5"=>"J")
		);
		
		$this->assertEquals($check, $result);		

		
	}
		
	
	// Null L1 column
	// ============================================================================================	
	
	function test_trie_flatten_03(){
			

		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'*'=>array( 'B'=>array( 'C'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);		

		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array("C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"E"),
				array("C2"=>"B", "C3"=>"C", "C4"=>"D", "C5"=>"T"),		    
				array("C2"=>"B", "C3"=>"C", "C4"=>"I", "C5"=>"J")
		);
		
		$this->assertEquals($check, $result);	
		
	}
	
	
	// Null L1 -> L3 columns
	// ============================================================================================
	
	function test_trie_flatten_04(){


		$columns = array("C1", "C2", "C3", "C4", "C5");
		
		$trie = array(
				'*'=>array( '*'=>array( '*'=>array( 
								    'D'=>array(
										'E'=>true,
										'T'=>true
								    ),
								    'I'=>array(	'J'=>true )
				)))
		);
		
		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array("C4"=>"D", "C5"=>"E"),
				array("C4"=>"D", "C5"=>"T"),		    
				array("C4"=>"I", "C5"=>"J")
		);
		
		$this->assertEquals($check, $result);	
		
	}
	
	
	// Null L5 column, "Missing Key" syntax, end nodes as "true"
	// ============================================================================================	
	
	function test_trie_flatten_05(){


		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'K'=>array(),							    
								    'D'=>array(),
								    'I'=>array()
				)))
		);		
		
		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"K"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D"),		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I")
		);
		
		$this->assertEquals($check, $result);	
		
	}
	
	
	// Null L5 column, "Missing Key" syntax, end nodes as "array"
	// ============================================================================================	
	
	function test_trie_flatten_06(){


		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'K'=>array(),							    
								    'D'=>array(),
								    'I'=>array()
				)))
		);		
		
		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		$check = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"K"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D"),		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I")
		);
		
		$this->assertEquals($check, $result);		
		
	}
	
	
	// Null L5 column, "Wildcard" syntax
	// ============================================================================================	
	
	function test_trie_flatten_07(){


		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>array( 'B'=>array( 'C'=>array( 
								    'K'=>array(	'*'=>true ),							    
								    'D'=>array(	'*'=>true ),
								    'I'=>array(	'*'=>true )
				)))
		);		
		
		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		//echo $cls->dump(); die;
		
		$check = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"K"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"D"),		    
				array( "C1"=>"A", "C2"=>"B", "C3"=>"C", "C4"=>"I")
		);
		
		$this->assertEquals($check, $result);	
		
	}	
	

	
	// L3 end nodes that clip L4 branches
	// ============================================================================================
	
	function test_trie_flatten_08(){
	  

		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array( "C1"=>"A", "C2"=>"B", "C3"=>"K"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"E"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"F"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D"		 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"H"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"I"),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"D", "C4"=>"J")
		);
		
		$this->assertEquals($check, $result);
		
	}
		
	
	// L1 end node that clips the entire tree
	// ============================================================================================
	
	function test_trie_flatten_09(){		
		
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(

				array( "C1"=>"A"				 ),
				array( "C1"=>"A", "C2"=>"B", "C3"=>"H"		 ),
				array( "C1"=>"A", "C2"=>"C", "C3"=>"I"		 ),
				array( "C1"=>"A", "C2"=>"D", "C3"=>"J"		 ),
				array( "C1"=>"A", "C2"=>"E", "C3"=>"K", "C4"=>"H"),
				array( "C1"=>"A", "C2"=>"F", "C3"=>"L", "C4"=>"I"),
				array( "C1"=>"A", "C2"=>"G", "C3"=>"M", "C4"=>"J")
		);
		
		$this->assertEquals($check, $result);
		
	}

	
	// Multiple L1 branches that clip all lower levels of the tree
	// ============================================================================================
	
	function test_trie_flatten_10(){
	    
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				array( "C1"=>"A"				 ),		    
				array( "C1"=>"B"				 ),
				array( "C1"=>"B", "C2"=>"C", "C3"=>"I"		 ),
				array( "C1"=>"B", "C2"=>"D", "C3"=>"J"		 ),		   
				array( "C1"=>"C"				 ),
				array( "C1"=>"C", "C2"=>"F", "C3"=>"L", "C4"=>"I"),
				array( "C1"=>"C", "C2"=>"G", "C3"=>"M", "C4"=>"J")
		);
		
		$this->assertEquals($check, $result);	
		
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches.
	// ============================================================================================
	
	function test_trie_flatten_11(){
	    
	
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				array( "C1"=>"A"				 ),		    
				array( "C1"=>"B", "C2"=>"X"			 ),
				array( "C1"=>"B", "C2"=>"X", "C3"=>"I"		 ),
				array( "C1"=>"B", "C2"=>"X", "C3"=>"J"		 ),		    
				array( "C1"=>"C", "C2"=>"Y"			 ),
				array( "C1"=>"C", "C2"=>"Y", "C3"=>"L", "C4"=>"I"),
				array( "C1"=>"C", "C2"=>"Y", "C3"=>"M", "C4"=>"J")
		);
		
		$this->assertEquals($check, $result);		
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Descending entropy levels on sub-branches.
	// ============================================================================================
	
	function test_trie_flatten_12(){
	    

		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				array( "C1"=>"A"					    ),		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),		    
				array( "C1"=>"W", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"W", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),		    
				array( "C1"=>"T", "C2"=>"X", "C3"=>"T", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"T", "C2"=>"Y", "C3"=>"T", "C4"=>"K", "C5"=>"K"),		    
				array( "C1"=>"Z", "C2"=>"X", "C3"=>"Z", "C4"=>"Z", "C5"=>"K"),		    		    		    
				array( "C1"=>"Z", "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"K")
		);
		
		$this->assertEquals($check, $result);
		
	}

	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Ascending entropy levels on sub-branches.
	// ============================================================================================
	
	function test_trie_flatten_13(){
	    

		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				array( "C1"=>"A"					    ),		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"Y"				    ),			    
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"K", "C5"=>"W"),		    
				array( "C1"=>"K", "C2"=>"X", "C3"=>"K", "C4"=>"T", "C5"=>"T"),		    
				array( "C1"=>"K", "C2"=>"X", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z"),		    		    		    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);
		
		$this->assertEquals($check, $result);
		
	}
		
	
	// Single L1 branch that clips lower branches. Two L2 branches that clip sub-branches with
	// null L1 keys in front of each L2 branch. Reversed null count ordering.
	// ============================================================================================
	
	function test_trie_flatten_14(){
	    

		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				array( "C1"=>"A"					    ),
				array( "C1"=>"K", "C2"=>"X",		"C4"=>"K", "C5"=>"W"),
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),		    
		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",			   "C5"=>"T"),			    
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),	    
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),		    
		    		    
				array(		  "C2"=>"Y"				    ),			    
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);
		
		$this->assertEquals($check, $result);	
		
	}

	
	// Column grouping
	// ============================================================================================
	
	function test_trie_flatten_15(){


		$columns = array("C1", "C2", "C3", "C4", "C5");
		
		$trie = array(	'T'=>true,
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
					    ),											    
				 ),		    
		    		    
		);
		
		
		try {
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				array( "C1"=>"T"					    ),		    
				array( "C1"=>"A", "C2"=>"G"				    ),			    
				array( "C1"=>"K",			"C4"=>"K", "C5"=>"W"),		    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",			   "C5"=>"T"),		    
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),			    
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),		    	    		    
				array(		  "C2"=>"Y"				    ),			    
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);
		
		$this->assertEquals($check, $result);		
		
	}


	// First item key in $end_groups not equal to zero, with only one item in $end_groups array.
	// ============================================================================================
	
	function test_trie_flatten_16(){


		$columns = array("C1", "C2", "C3", "C4", "C5");
		
		$trie = array(	
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
			$cls = new BPM_trie_flatten($trie, $columns, $ctrl=null);
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (BPM_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(	    
				array( "C1"=>"A", "C2"=>"G"				    ),	
				array( "C1"=>"T"					    ),			    
				array( "C1"=>"K",			"C4"=>"K", "C5"=>"W"),		    
				array( "C1"=>"K", "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"K"),		    
				array(		  "C2"=>"X"				    ),
				array(		  "C2"=>"X",			   "C5"=>"T"),		    
				array(		  "C2"=>"X",		"C4"=>"Z"	    ),			    
				array(		  "C2"=>"X",		"C4"=>"K", "C5"=>"K"),		    	    		    
				array(		  "C2"=>"Y"				    ),			    
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"K", "C5"=>"W"),
				array(		  "C2"=>"Y", "C3"=>"K", "C4"=>"T", "C5"=>"T"),
				array(		  "C2"=>"Y", "C3"=>"Z", "C4"=>"Z", "C5"=>"Z")	
		);
		
		$this->assertEquals($check, $result);		
		
	}
	

	function tearDown() {	

		parent::tearDown();		
	}	
	

}

?>