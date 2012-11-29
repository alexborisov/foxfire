<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DATASTORE VALIDATORS
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

class core_datastore_validators extends RAZ_testCase {

	
    	function setUp() {
	    
		parent::setUp();
					
	}
	
	
       /**
	* Test fixture for test_validateKey() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateKey() {
	    
	    			
		$struct = array(

			"table" => "FOX_dataStore_validators",
			"engine" => "InnoDB",
			"cache_namespace" => "FOX_dataStore_validators",
			"cache_strategy" => "paged",
			"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
			"columns" => array(
			    "X3" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X1" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X0" =>	array(	"php"=>"serialize", "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>"",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
			)
		);
		
		$cls = new FOX_dataStore_validator($struct);
		
		
		// SCALAR INT
		// #################################################################
		
		
		// PASS on scalar int when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>7
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// FAIL on string when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>'foo'
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on int equivalent string when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>'17'
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on null when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>null
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on int array when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>array(1,2)
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on empty array when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>array()
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		
		// ARRAY INT
		// #################################################################		
		
		
		// PASS on array int when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array(7)
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// PASS on empty array when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array()
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);		
		
		
		// FAIL on null when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>null
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on scalar int when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>7
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on array string when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array('foo','bar')
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);			
		
		
		// FAIL on array mixed when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array(1,'bar',7,8)
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);	
		
		
		// FAIL on int equivalent string keys
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array(1,'2',7,8)
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		
		// SCALAR STRING
		// #################################################################
		
		
		// PASS on scalar string when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>'foo'
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// FAIL on int when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>7
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on int equivalent string when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>'17'
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on null when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>null
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on string array when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>array('foo','bar')
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on empty array when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>array()
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		
		// ARRAY STRING
		// #################################################################		
		
		
		// PASS on array string when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array('foo')
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// PASS on empty array when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array()
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);		
		
		
		// FAIL on null when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>null
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on scalar string when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>'foo'
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on array int when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array(1,2)
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);			
		
		
		// FAIL on array mixed when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array('foo','bar',7,'baz')
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);	
		
		
		// FAIL on int equivalent string keys
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array('A','2','B','C')
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);	
		
		
	}
	
		
       /**
	* Test fixture for test_validateTrie() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateTrie_control() {
 
	    
		$struct = array(

			"table" => "FOX_dataStore_validators",
			"engine" => "InnoDB",
			"cache_namespace" => "FOX_dataStore_validators",
			"cache_strategy" => "paged",
			"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
			"columns" => array(
			    "X3" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X1" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X0" =>	array(	"php"=>"serialize", "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>"",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
			)
		);
		
		$cls = new FOX_dataStore_validator($struct);	
		
		$ctrl = array(
			'order'=>3,
			'mode'=>'control',
			'allow_wildcard'=>false,
			'clip_order'=>false		    
		);		
		
		
		// PASS - Valid control trie
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				2=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);	

		
		// PASS - Empty control trie
		// ####################################################################
	    
		$data = array();
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// FAIL - Wrong control trie data type
		// ####################################################################
	    
		$data = "foo";
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
		
		// FAIL - Invalid L1 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	"F"=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				2=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Invalid L2 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   1=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				2=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
		
		// FAIL - Invalid L2 node value
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>'foo'				    
				),
				2=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Invalid L3 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				'F'=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Invalid L3 node value
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				2=>'X'
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);							
		
		
		// FAIL - wildcards when not allowed
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				'*'=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		
		// PASS - wildcards when allowed
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'control',
			'allow_wildcard'=>true,
			'clip_order'=>false		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				'*'=>true
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// FAIL - wildcard with invalid value
		// ####################################################################		
		
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				'*'=>'foo'
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
		
	}
	
	
	function tearDown() {
	   
		parent::tearDown();
	}

    
}

?>