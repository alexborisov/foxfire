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
	* Test fixture for validateKey() method
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
		
		
		// EXCEPTION - invalid 'type' parameter
		// ####################################################################
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'fail',
							    'format'=>'array',
							    'var'=>array('A','2','B','C')
			));
			
			// Execution will halt on the previous line if validateKey() throws an exception
			$this->fail("Method validateKey() failed to throw an exception on invalid 'type' parameter");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
		// EXCEPTION - invalid 'format' parameter
		// ####################################################################
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'fail',
							    'var'=>array('A','2','B','C')
			));
			
			// Execution will halt on the previous line if validateKey() throws an exception
			$this->fail("Method validateKey() failed to throw an exception on invalid 'format' parameter");			
		}
		catch (FOX_exception $child) {
	
		}		
		
	}
	
		
       /**
	* Test fixture for validateTrie() method, 'control' mode
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
		
		
		// EXCEPTION - Invalid order
		// ####################################################################
	    
		$ctrl = array(
			'order'=>7,
			'mode'=>'control',
			'allow_wildcard'=>true,
			'clip_order'=>false		    
		);	
		
		$data = "foo";
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
			
			// Execution will halt on the previous line if validateTrie() throws an exception
			$this->fail("Method validateTrie() failed to throw an exception on invalid class order");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
	}
	
	
       /**
	* Test fixture for validateTrie() method, 'data' mode
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateTrie_data() {
 
	    
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
		
		
		// PASS - Valid data trie
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	1=>(string)"foo",
							5=>null				    
					    ),	
					    'Y'=>array()				    
				)
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);	

		
		// FAIL - Empty data trie
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array();
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);			
				
		
		// FAIL - Wrong data trie data type
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = "foo";
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
		
		// FAIL - Branches terminating above clip plane
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	1=>(string)"foo",
							5=>null				    
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
		
		
		// FAIL - Branches terminating below clip plane, but not at L1
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>1		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	1=>(string)"foo",
							5=>null				    
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
		
		
		// FAIL - Invalid L1 node data type
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	"F"=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				)
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
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array(
				1=>array(   1=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				)
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
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>'foo'				    
				)
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
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);
		
		$data = array(
				1=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
				'F'=>array(   'X'=>array(	1=>true,
							5=>true				    
					    ),	
					    'Y'=>true				    
				),
		);
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);									
							
		
		// EXCEPTION - Wildcards in 'data' mode
		// ####################################################################
	    
		$ctrl = array(
			'order'=>3,
			'mode'=>'data',
			'allow_wildcard'=>true,
			'clip_order'=>2		    
		);	
		
		$data = "foo";
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
			
			// Execution will halt on the previous line if validateTrie() throws an exception
			$this->fail("Method validateTrie() failed to throw an exception on wildcards in 'data' mode");			
		}
		catch (FOX_exception $child) {
	
		}
		
		
		// EXCEPTION - Invalid order
		// ####################################################################
	    
		$ctrl = array(
			'order'=>7,
			'mode'=>'data',
			'allow_wildcard'=>false,
			'clip_order'=>2		    
		);	
		
		$data = "foo";
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
			
			// Execution will halt on the previous line if validateTrie() throws an exception
			$this->fail("Method validateTrie() failed to throw an exception on invalid class order");			
		}
		catch (FOX_exception $child) {
	
		}		
				
	}
	
	
       /**
	* Test fixture for validateMatrixRow() method (invalid control params)
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateMatrixRow_controlParams() {

	    
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
		
		
		// EXCEPTION - Invalid end_node_format
		// ####################################################################
	    
		$ctrl = array(
					'end_node_format'=>'FAIL'		    
		);	
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>1, 'X0'=>array('foo',17,null) );
		
		try {			
			$cls->validateMatrixRow($row, $ctrl);
			
			// Execution will halt on the previous line if validateMatrixRow() throws an exception
			$this->fail("Method validateMatrixRow() failed to throw an exception on invalid 'end_node_format' param");			
		}
		catch (FOX_exception $child) {
	
		}	
		
		
		// EXCEPTION - Invalid ['array_ctrl']['mode'] format
		// ####################################################################	
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'FAIL'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>array(1=>true, 2=>'foo') );
		
		try {			
			$cls->validateMatrixRow($row, $ctrl);
			
			// Execution will halt on the previous line if validateMatrixRow() throws an exception
			$this->fail("Method validateMatrixRow() failed to throw an exception on invalid ['array_ctrl']['mode'] param");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
	}
	
	
       /**
	* Test fixture for validateMatrixRow() method, 'scalar' mode
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateMatrixRow_scalar() {

	    
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
		
		
		// PASS - X3->X0 walk
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>1, 'X0'=>array('foo',17,null) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);	

		
		// PASS - X3->X1 walk
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>1 );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);			
		
		
		// PASS - X3->X2 walk
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y');
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// PASS - X3->X3 walk
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1);
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);			
		
		
		// PASS - Empty row (allows SELECT *)
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array();
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);		
		
		
		// PASS - Null keys, when allowed
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X1'=>1, 'X0'=>array('foo',17,null) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// FAIL - Null keys, when not allowed
		// ####################################################################
	    
		$ctrl = array(	'required_keys'=>array('X3','X2','X1'),
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X1'=>1, 'X0'=>array('foo',17,null) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);
		
		
		// PASS - Foreign keys, when allowed
		// ####################################################################
	    
		$ctrl = array(	'allow_foreign_keys'=>true,
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X1'=>1, 'X0'=>array('foo',17,null), 'X12'=>true );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);		
							
		
		// FAIL - Foreign keys, by implication, when not allowed
		// ####################################################################
	    
		$ctrl = array(	'allow_foreign_keys'=>false,
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X1'=>1, 'X0'=>array('foo',17,null), 'X12'=>true );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);
		
		
		// FAIL - Foreign keys, by definition, when not allowed
		// ####################################################################
	    
		$ctrl = array(	'allow_foreign_keys'=>false,
				'allowed_keys'=>array('X3','X2','X1'),
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>1, 'X0'=>array('foo',17,null) ); 
		
		// Fails on 'X0' even though its in the class definition array, because
		// its not present in the 'allowed_keys' array
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Incorrect 'X1' data type
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>'1', 'X0'=>array('foo',17,null) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
		
		// FAIL - Incorrect 'X2' data type
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>1, 'X2'=>2, 'X1'=>1, 'X0'=>array('foo',17,null) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Incorrect 'X3' data type
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'scalar'		    
		);
		
		$row = array('X3'=>"F", 'X2'=>'Y', 'X1'=>1, 'X0'=>array('foo',17,null) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
	}
	
	
       /**
	* Test fixture for validateMatrixRow() method, 'array' mode
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateMatrixRow_array() {

	    
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
		
		
		// PASS - X3->X1 walk, 'normal' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>array(1=>true, 2=>'foo') );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);	
		
		
		// PASS - X3->X1 walk, 'inverse' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>array(1,2) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// PASS - X3->X1 walk, null keys allowed, 'normal' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X1'=>array(1=>true, 2=>'foo') );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);	
		
		
		// PASS - X3->X2 walk, 'normal' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>array('A'=>true, 'B'=>'foo') );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// PASS - X3->X2 walk, 'inverse' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>array('A','B') );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// PASS - X3->X2 walk, null keys allowed, 'normal' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X2'=>array('A'=>true, 'B'=>'foo') );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);
		
		
		// PASS - X3->X3 walk, 'normal' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>array(1=>true, 2=>'foo') );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);	
		
		
		// PASS - X3->X3 walk, 'inverse' end node keys
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>array(1,2) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);		
		
		
		
		// PASS - Empty row (allows SELECT *)
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array'		    
		);
		
		$row = array();
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);		
		
		
		// PASS - Foreign keys, when allowed
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'allow_foreign_keys'=>true,
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'F', 'X1'=>array(1=>true, 2=>'foo'), 'X12'=>5 );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals(true, $result);				
							
		
		// FAIL - Foreign keys, by implication, when not allowed
		// ####################################################################
	    
		$ctrl = array(	'allow_foreign_keys'=>false,
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'F', 'X1'=>array(1=>true, 2=>'foo'), 'X12'=>5 );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);
		
		
		// FAIL - Foreign keys, by definition, when not allowed
		// ####################################################################
	    
		$ctrl = array(	'allow_foreign_keys'=>false,
				'allowed_keys'=>array('X3','X2','X1'),
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>array(1=>true, 2=>'foo'), 'X0'=>array('foo',17,null) ); 
		
		// Fails on 'X0' even though its in the class definition array, because
		// its not present in the 'allowed_keys' array
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
				
		
		// FAIL - Incorrect 'X1' data type, 'normal' format
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>array('F'=>true, 2=>true) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);		
		
		
		// FAIL - Incorrect 'X1' data type, 'inverse' format
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>array('1', 2) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Non-array 'X1' data type, 'normal' mode
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'normal'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>true );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Non-array 'X1' data type, 'inverse' mode
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'Y', 'X1'=>true );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);			
		
		
		// FAIL - Incorrect 'X2' data type
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>1, 'X2'=>'2', 'X1'=>array(1, 2) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
		// FAIL - Incorrect 'X3' data type
		// ####################################################################
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>"1", 'X2'=>'Y', 'X1'=>array(1, 2) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
		
	}
	
	
       /**
	* Test fixture for validateMatrixRow() method, 'trie' mode, 'control' trie
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateMatrixRow_trie_control() {

	    
		$struct = array(

			"table" => "FOX_dataStore_validators",
			"engine" => "InnoDB",
			"cache_namespace" => "FOX_dataStore_validators",
			"cache_strategy" => "paged",
			"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
			"columns" => array(
			    "X5" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
			    "X4" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),			    
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
	    
		$ctrl = array(
				'end_node_format'=>'array',
				'array_ctrl'=>array(
						    'mode'=>'inverse'
				)		    
		);
		
		$row = array('X3'=>"1", 'X2'=>'Y', 'X1'=>array(1, 2) );
		
		try {			
			$result = $cls->validateMatrixRow($row, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertNotEquals(true, $result);	
		
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
		
		
		// EXCEPTION - Invalid order
		// ####################################################################
	    
		$ctrl = array(
			'order'=>7,
			'mode'=>'control',
			'allow_wildcard'=>true,
			'clip_order'=>false		    
		);	
		
		$data = "foo";
		
		try {			
			$result = $cls->validateTrie($data, $ctrl);
			
			// Execution will halt on the previous line if validateTrie() throws an exception
			$this->fail("Method validateTrie() failed to throw an exception on invalid class order");			
		}
		catch (FOX_exception $child) {
	
		}		
		
	    
	}
	
	
	function tearDown() {
	   
		parent::tearDown();
	}

    
}

?>