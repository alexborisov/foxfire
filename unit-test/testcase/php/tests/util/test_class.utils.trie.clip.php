<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - TRIE UTILITIES | FLATTEN
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


class util_trie_clip extends RAZ_testCase {


    	function setUp() {

		parent::setUp();		
	}

	
	// Irreducible trie, end nodes set to (bool)true
	// ============================================================================================
	
	function test_trie_clip_01(){
	    	
	    
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
			$cls = new FOX_trie_clip($trie, $columns, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		// Should return the original trie with end nodes set to empty arrays
		
		$check = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    'E'=>array(
										'G'=>array(),
										'K'=>array(),
										'T'=>array()
								    ),
								    'F'=>array(	'I'=>array() )
				)))
		); 
		
		$this->assertEquals($check, $result);
		
	}
			

	// Irreducible trie, end nodes set to (array)
	// ============================================================================================
	
	function test_trie_clip_02(){
	    	
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    'E'=>array(
										'G'=>array(),
										'K'=>array(),
										'T'=>array()
								    ),
								    'F'=>array(	'I'=>array() )
				)))
		);  


		try {
			$cls = new FOX_trie_clip($trie, $columns, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		// Should return the original trie with end nodes set to empty arrays
		
		$check = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    'E'=>array(
										'G'=>array(),
										'K'=>array(),
										'T'=>array()
								    ),
								    'F'=>array(	'I'=>array() )
				)))
		); 
		
		$this->assertEquals($check, $result);
		
	}	
	
	
	// Reducible end nodes
	// ============================================================================================
	
	function test_trie_clip_03(){
	    	
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    'E'=>array(
										'*'=>array(),
										'*'=>array(),
										'T'=>array()
								    ),
								    'F'=>array(	'*'=>array() )
				)))
		);  


		try {
			$cls = new FOX_trie_clip($trie, $columns, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		// Should return the original trie with end nodes set to empty arrays
		
		$check = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    'E'=>array(),
								    'F'=>array()
				)))
		); 
		
		$this->assertEquals($check, $result);
		
	}		
	
	
	// Reducible branch
	// ============================================================================================
	
	function test_trie_clip_04(){
	    	
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>array( 'B'=>array( 'D'=>array( 
								    '*'=>array(
										'G'=>array(),
										'K'=>array(),
										'T'=>array()
								    ),
								    'F'=>array(	'I'=>array() )
				)))
		);  


		try {
			$cls = new FOX_trie_clip($trie, $columns, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		// Should return the original trie with end nodes set to empty arrays
		
		$check = array(
				'A'=>array( 'B'=>array( 'D'=>array()))
		); 
		
		$this->assertEquals($check, $result);
		
	}
	

	// Intersecting reducible branches
	// ============================================================================================
	
	function test_trie_clip_05(){
	    	
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'A'=>true,			
				'K'=>array(		    
					    'X'=>array(	
							'V'=>array( 
								    'K'=>array(	
										'K'=>true,
										'W'=>true
	    							    ),
								    '*'=>array(	'T'=>true )							    
							),
							'*'=>array( 'Z'=>array( '*'=>true )) 						
					     ),	
					    'Y'=>array(	
							'K'=>array( 
								    '*'=>array(	
										'K'=>true,
										'W'=>true
	    							    ),
								    'T'=>array(	'*'=>true )							    
							),
							'Z'=>array( 'Z'=>array( 'Z'=>true )) 						
					     ),					    
				 )		    		    
		);	


		try {
			$cls = new FOX_trie_clip($trie, $columns, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		$check = array(
				'A'=>array(),			
				'K'=>array(		    
					    'X'=>array(),	
					    'Y'=>array(	
							'K'=>array(),
							'Z'=>array( 'Z'=>array( 'Z'=>array() )) 						
					     ),					    
				 )		    		    
		);
		
		$this->assertEquals($check, $result);
		
	}
	
	
	// Wildcard root node
	// ============================================================================================
	
	function test_trie_clip_06(){
	    	
	    
		$columns = array("C1", "C2", "C3", "C4", "C5");

		$trie = array(
				'*'=>true,			
				'K'=>array(		    
					    'X'=>array(	
							'V'=>array( 
								    'K'=>array(	
										'K'=>true,
										'W'=>true
	    							    ),
								    '*'=>array(	'T'=>true )							    
							),
							'*'=>array( 'Z'=>array( '*'=>true )) 						
					     ),	
					    'Y'=>array(	
							'K'=>array( 
								    '*'=>array(	
										'K'=>true,
										'W'=>true
	    							    ),
								    'T'=>array(	'*'=>true )							    
							),
							'Z'=>array( 'Z'=>array( 'Z'=>true )) 						
					     ),					    
				 )		    		    
		);	


		try {
			$cls = new FOX_trie_clip($trie, $columns, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}				
		
		try {
			$result = $cls->render();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		

		// Reduces to the universal set
		
		$check = array();
		
		$this->assertEquals($check, $result);
		
	}
	
	
	function tearDown() {	

		parent::tearDown();		
	}	
	

}

?>