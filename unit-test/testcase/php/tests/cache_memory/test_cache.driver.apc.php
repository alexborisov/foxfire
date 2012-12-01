<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - MEMORY CACHE | PERSISTENT | APC
 * Tests the operation of the memory cache when persistent caching is available on the server via APC
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


class core_mCache_driver_apc_ops extends RAZ_testCase {

    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_apc(array('process_id'=>2650));				

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('APC is not active on this server');
		}
	}
	

	function test_set_single_get_single() {
	    	    	    		
		
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
												    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
		    
			// Test positive and negative versions of all PHP
			// data types in a single namespace
		    
			array('ns'=>'ns_1', 'var'=>'var_1', 'val'=>null),
			array('ns'=>'ns_1', 'var'=>'var_2', 'val'=>false),
			array('ns'=>'ns_1', 'var'=>'var_3', 'val'=>true),		    
			array('ns'=>'ns_1', 'var'=>'var_4', 'val'=>(int)0),
			array('ns'=>'ns_1', 'var'=>'var_5', 'val'=>(int)1),
			array('ns'=>'ns_1', 'var'=>'var_6', 'val'=>(int)-1),
			array('ns'=>'ns_1', 'var'=>'var_7', 'val'=>(float)1.7),
			array('ns'=>'ns_1', 'var'=>'var_8', 'val'=>(float)-1.6),
			array('ns'=>'ns_1', 'var'=>'var_9', 'val'=>(string)"foo"),
			array('ns'=>'ns_1', 'var'=>'var_10', 'val'=>array(null, true, false, 1, 1.0, "foo")),	
			array('ns'=>'ns_1', 'var'=>'var_11', 'val'=>$test_obj),
		    
			// Test the mirror image of the same dataset in a second namespace
		    
			array('ns'=>'ns_2', 'var'=>'var_1', 'val'=>$test_obj),
		    	array('ns'=>'ns_2', 'var'=>'var_2', 'val'=>array(1, 1.0, "foo")),
			array('ns'=>'ns_2', 'var'=>'var_3', 'val'=>(string)"foo"),		    
			array('ns'=>'ns_2', 'var'=>'var_4', 'val'=>(float)-1.6),	
		    	array('ns'=>'ns_2', 'var'=>'var_5', 'val'=>(float)1.7),
			array('ns'=>'ns_2', 'var'=>'var_6', 'val'=>(int)-1),	
		    	array('ns'=>'ns_2', 'var'=>'var_7', 'val'=>(int)1),
			array('ns'=>'ns_2', 'var'=>'var_8', 'val'=>(int)0),
			array('ns'=>'ns_2', 'var'=>'var_9', 'val'=>true),			    
			array('ns'=>'ns_2', 'var'=>'var_10', 'val'=>false),		    
			array('ns'=>'ns_2', 'var'=>'var_11', 'val'=>null),
		);
		

		foreach( $test_data as $item ){
		    		
		    
			$check_offset = 1;  // Since the cache has been globally flushed, and the
					    // namespace hasn't been flushed since, offset will be 1
			
			try {
				$this->cls->set($item['ns'], $item['var'], $item['val'], $check_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}		    									
			
		}
		unset($item);
		
		// Check for exception on current offset doesn't match expected offset
		
		try {						
			$this->cls->set('ns_1', 'var_1', 'foo', 99);	
			
			$this->fail("Failed to throw an exception on non-matching offset");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$current_offset = false;
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);				
			
		}
		unset($item);			    	   		
	    
	}
	
	
	function test_set_multi_get_multi() {
	    
	    	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
					
		// Test positive and negative versions of all PHP
		// data types in a single namespace	
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	    
		
		$test_data_a = array(
		    
			'var_1'=>null,
			'var_2'=>false,
			'var_3'=>true,		    
			'var_4'=>(int)0,
			'var_5'=>(int)1,
			'var_6'=>(int)-1,
			'var_7'=>(float)1.7,
			'var_8'=>(float)-1.6,
			'var_9'=>(string)"foo",
			'var_10'=>array(null, true, false, 1, 1.0, "foo"),	
			'var_11', 'val'=>$test_obj,
		);
		
		// Test the mirror image of the same data set in a second namespace
		
		$test_data_b = array(
		    		    
			'var_1'=>$test_obj,
		    	'var_2'=>array(1, 1.0, "foo"),
			'var_3'=>(string)"foo",		    
			'var_4'=>(float)-1.6,	
		    	'var_5'=>(float)1.7,
			'var_6'=>(int)-1,	
		    	'var_7'=>(int)1,
			'var_8'=>(int)0,
			'var_9'=>true,			    
			'var_10'=>false,		    
			'var_11'=>null
		);		
		
		
		// Write keys to cache
		// =====================================================
		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1
			
		try {
			$this->cls->setMulti('ns_1', $test_data_a, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		
		try {
			$this->cls->setMulti('ns_2', $test_data_b, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		
		// Check for exception on current offset doesn't match expected offset
		
		try {						
			$this->cls->setMulti('ns_2', $test_data_b, 99);	
			
			$this->fail("Failed to throw an exception on non-matching offset");			
		}
		catch (FOX_exception $child) {
	
		}
		
		
		// Fetch keys from cache
		// =====================================================
		
		$current_offset = false;
		
		try {
			$result = $this->cls->getMulti('ns_1', array_keys($test_data_a), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		
		$current_offset = false;
		
		try {
			$result = $this->cls->getMulti('ns_2', array_keys($test_data_b), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
						    	   			    
	}
	
	
	function test_flushAll() {

	    
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
	    		
		// Write all possible data types to two different namespaces
		// ==========================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
		    
			// Test positive and negative versions of all PHP
			// data types in a single namespace
		    
			array('ns'=>'ns_1', 'var'=>'var_1', 'val'=>null),
			array('ns'=>'ns_1', 'var'=>'var_2', 'val'=>false),
			array('ns'=>'ns_1', 'var'=>'var_3', 'val'=>true),		    
			array('ns'=>'ns_1', 'var'=>'var_4', 'val'=>(int)0),
			array('ns'=>'ns_1', 'var'=>'var_5', 'val'=>(int)1),
			array('ns'=>'ns_1', 'var'=>'var_6', 'val'=>(int)-1),
			array('ns'=>'ns_1', 'var'=>'var_7', 'val'=>(float)1.7),
			array('ns'=>'ns_1', 'var'=>'var_8', 'val'=>(float)-1.6),
			array('ns'=>'ns_1', 'var'=>'var_9', 'val'=>(string)"foo"),
			array('ns'=>'ns_1', 'var'=>'var_10', 'val'=>array(null, true, false, 1, 1.0, "foo")),	
			array('ns'=>'ns_1', 'var'=>'var_11', 'val'=>$test_obj),
		    
			// Test the mirror image of the same dataset in a second namespace
		    
			array('ns'=>'ns_2', 'var'=>'var_1', 'val'=>$test_obj),
		    	array('ns'=>'ns_2', 'var'=>'var_2', 'val'=>array(1, 1.0, "foo")),
			array('ns'=>'ns_2', 'var'=>'var_3', 'val'=>(string)"foo"),		    
			array('ns'=>'ns_2', 'var'=>'var_4', 'val'=>(float)-1.6),	
		    	array('ns'=>'ns_2', 'var'=>'var_5', 'val'=>(float)1.7),
			array('ns'=>'ns_2', 'var'=>'var_6', 'val'=>(int)-1),	
		    	array('ns'=>'ns_2', 'var'=>'var_7', 'val'=>(int)1),
			array('ns'=>'ns_2', 'var'=>'var_8', 'val'=>(int)0),
			array('ns'=>'ns_2', 'var'=>'var_9', 'val'=>true),			    
			array('ns'=>'ns_2', 'var'=>'var_10', 'val'=>false),		    
			array('ns'=>'ns_2', 'var'=>'var_11', 'val'=>null),
		);
		
		foreach( $test_data as $item ){
		    
			$check_offset = 1;  // Since the cache has been globally flushed, and the
					    // namespace hasn't been flushed since, offset will be 1
			
			try {
				$this->cls->set($item['ns'], $item['var'], $item['val'], $check_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}		    
		}
		unset($item);
		
		
		// Verify all keys are in the cache
		// ===================================================		
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$current_offset = false;			
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}			
						
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);				
			
		}
		unset($item);
		
		
		// Flush the cache
		// ===================================================
		
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		
		// Verify all keys return null and are flagged as invalid
		// ===================================================		
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$current_offset = false;			
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}			
			
			// The cache should report the key as valid
			$this->assertEquals(false, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals(null, $value);
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);				
			
		}
		unset($item);		
	    
	}	
	
	
	function test_flushNamespace() {
	    
	    	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// Write all possible data types to two different namespaces
		// ==========================================================
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	    
		
		$test_data_a = array(
		    
			'var_1'=>null,
			'var_2'=>false,
			'var_3'=>true,		    
			'var_4'=>(int)0,
			'var_5'=>(int)1,
			'var_6'=>(int)-1,
			'var_7'=>(float)1.7,
			'var_8'=>(float)-1.6,
			'var_9'=>(string)"foo",
			'var_10'=>array(null, true, false, 1, 1.0, "foo"),	
			'var_11', 'val'=>$test_obj,
		);
		
		$test_data_b = array(
		    		    
			'var_1'=>$test_obj,
		    	'var_2'=>array(1, 1.0, "foo"),
			'var_3'=>(string)"foo",		    
			'var_4'=>(float)-1.6,	
		    	'var_5'=>(float)1.7,
			'var_6'=>(int)-1,	
		    	'var_7'=>(int)1,
			'var_8'=>(int)0,
			'var_9'=>true,			    
			'var_10'=>false,		    
			'var_11'=>null
		);		
		
		
		// Write keys to cache
		// =====================================================
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1
			
		try {
			$this->cls->setMulti('ns_1', $test_data_a, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		
		try {
			$this->cls->setMulti('ns_2', $test_data_b, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		$current_offset = false;
		
		try {
			$result = $this->cls->getMulti('ns_1', array_keys($test_data_a), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		$current_offset = false;
		
		try {
			$result = $this->cls->getMulti('ns_2', array_keys($test_data_b), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
		
		// Flush one of the namespaces
		// =====================================================
		
		try {
			$new_offset = $this->cls->flushNamespace('ns_1');
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		// Should be (int)2, because this is the first flush of this
		// namespace, following a complete cache flush
		
		$this->assertEquals(2, $new_offset);
		
		
		// Verify the keys in the flushed namespace were cleared
		// =====================================================
		
		$current_offset = false;
						
		try {
			$result = $this->cls->getMulti('ns_1', array_keys($test_data_a), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should be an empty array	
		$this->assertEquals(array(), $result);
		
		// The reported offset should be 2, because ns_1 was flushed
		$this->assertEquals(2, $current_offset);
		
		$current_offset = false;
						
		try {
			$result = $this->cls->getMulti('ns_2', array_keys($test_data_b), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);
		
		// The reported offset should be 1, because ns_2 wasn't flushed
		$this->assertEquals(1, $current_offset);		
								    	   			    
	}
	

	function test_del_single() {
	    	    
	    
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		    
		
		// Write all possible data types to two different namespaces
		// ==========================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
		    
			// Test positive and negative versions of all PHP
			// data types in a single namespace
		    
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_1',	    'val'=>null),
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_2',	    'val'=>false),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_3',	    'val'=>true),		    
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_4',	    'val'=>(int)0),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_5',	    'val'=>(int)1),
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_6',	    'val'=>(int)-1),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_7',	    'val'=>(float)1.7),
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_8',	    'val'=>(float)-1.6),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_9',	    'val'=>(string)"foo"),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_10',    'val'=>array(null, true, false, 1, 1.0, "foo")),	
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_11',    'val'=>$test_obj),
		    
			// Test the mirror image of the same dataset in a second namespace
		    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_1',	    'val'=>$test_obj),
		    	array('delete'=>false,	'ns'=>'ns_2',	'var'=>'var_2',	    'val'=>array(1, 1.0, "foo")),
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_3',	    'val'=>(string)"foo"),		    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_4',	    'val'=>(float)-1.6),	
		    	array('delete'=>false,	'ns'=>'ns_2',	'var'=>'var_5',	    'val'=>(float)1.7),
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_6',	    'val'=>(int)-1),	
		    	array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_7',	    'val'=>(int)1),
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_8',	    'val'=>(int)0),
			array('delete'=>false,	'ns'=>'ns_2',	'var'=>'var_9',	    'val'=>true),			    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_10',    'val'=>false),		    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_11',    'val'=>null)
		    
		);
		
		
		// Write keys to cache
		// =====================================================
		
		foreach( $test_data as $item ){
		    
			$check_offset = 1;  // Since the cache has been globally flushed, and the
					    // namespace hasn't been flushed since, offset will be 1
			
			try {
				$this->cls->set($item['ns'], $item['var'], $item['val'], $check_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}	    
		}
		unset($item);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$current_offset = false;			
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}			
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);				
			
		}
		unset($item);
		
		
		// Delete some keys
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    
			$check_offset = 1;  // Since the cache has been globally flushed, and the
					    // namespace hasn't been flushed since, offset will be 1
				
			if( $item['delete'] == true ){

				try {
					$del_ok = $this->cls->del($item['ns'], $item['var'], $check_offset);
				}
				catch (FOX_exception $child) {

					$this->fail($child->dumpString(1));		    
				}				

				// The cache should report the key as valid
				$this->assertEquals(true, $del_ok);			    			    
			}			
		}
		unset($item);	
		
		
		// Verify the correct keys were deleted
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    
			$valid = false;
			$current_offset = false;						
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}			
						
			if( $item['delete'] == true ){
			    
				// The cache should report the key as invalid
				$this->assertEquals(false, $valid);

				// The returned value should be null
				$this->assertEquals(null, $value);				
			}
			else {
				// The cache should report the key as valid
				$this->assertEquals(true, $valid);

				// The returned value should match the value we set
				$this->assertEquals($item['val'], $value);			    			    
			}
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);
			
		}
		unset($item);
		

		// Check deleting nonexistent key from valid namespace fails
		// =====================================================
		
		$check_offset = 1;
		
		try {
			$del_ok = $this->cls->del('ns_1', 'var_99', $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// The cache should report the key as valid
		$this->assertEquals(false, $del_ok);	
		
		
		// Check deleting valid key from nonexistent namespace fails
		// =====================================================
		
		$check_offset = 1;
		
		try {
			$del_ok = $this->cls->del('ns_99', 'var_2', $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// The cache should report the key as valid
		$this->assertEquals(false, $del_ok);	
		
		
		// Check for exception on mismatched offset
		// =====================================================
		
		try {						
			$del_ok = $this->cls->del('ns_99', 'var_2', 99);
			
			$this->fail("Failed to throw an exception on non-matching offset");			
		}
		catch (FOX_exception $child) {
	
		}		
			
		
	}
	
	
	function test_del_multi() {
	    	 
	    
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}			    
		
		// Write all possible data types to two different namespaces
		// ==========================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
		    
			// Test positive and negative versions of all PHP
			// data types in a single namespace
		    
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_1',	    'val'=>null),
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_2',	    'val'=>false),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_3',	    'val'=>true),		    
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_4',	    'val'=>(int)0),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_5',	    'val'=>(int)1),
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_6',	    'val'=>(int)-1),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_7',	    'val'=>(float)1.7),
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_8',	    'val'=>(float)-1.6),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_9',	    'val'=>(string)"foo"),
			array('delete'=>true,	'ns'=>'ns_1',	'var'=>'var_10',    'val'=>array(null, true, false, 1, 1.0, "foo")),	
			array('delete'=>false,	'ns'=>'ns_1',	'var'=>'var_11',    'val'=>$test_obj),
		    
			// Test the mirror image of the same dataset in a second namespace
		    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_1',	    'val'=>$test_obj),
		    	array('delete'=>false,	'ns'=>'ns_2',	'var'=>'var_2',	    'val'=>array(1, 1.0, "foo")),
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_3',	    'val'=>(string)"foo"),		    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_4',	    'val'=>(float)-1.6),	
		    	array('delete'=>false,	'ns'=>'ns_2',	'var'=>'var_5',	    'val'=>(float)1.7),
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_6',	    'val'=>(int)-1),	
		    	array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_7',	    'val'=>(int)1),
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_8',	    'val'=>(int)0),
			array('delete'=>false,	'ns'=>'ns_2',	'var'=>'var_9',	    'val'=>true),			    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_10',    'val'=>false),		    
			array('delete'=>true,	'ns'=>'ns_2',	'var'=>'var_11',    'val'=>null)
		    
		);
		
		
		foreach( $test_data as $item ){
		    
			$check_offset = 1;  // Since the cache has been globally flushed, and the
					    // namespace hasn't been flushed since, offset will be 1
			
			try {
				$this->cls->set($item['ns'], $item['var'], $item['val'], $check_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}		    
		}
		unset($item);
		
		
		// Verify all keys are in the cache
		// ===================================================		
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$current_offset = false;			
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}			
						
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);				
			
		}
		unset($item);
		
		
		// Delete some keys
		// =====================================================
		
		$del_keys_a = array();
		$del_keys_b = array();
		
		foreach( $test_data as $item ){
		    
			if( $item['delete'] == true ){
			    
				if( $item['ns'] == 'ns_1' ){

					$del_keys_a[] = $item['var'];
				}
				else {
				    
					$del_keys_b[] = $item['var'];	
				}
			}			
		}
		unset($item);	
		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$keys_deleted = $this->cls->delMulti('ns_1', $del_keys_a, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				

		// The cache should report deleting 6 keys
		$this->assertEquals(6, $keys_deleted);	
		
		
		try {
			$keys_deleted = $this->cls->delMulti('ns_2', $del_keys_b, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				

		// The cache should report deleting 8 keys
		$this->assertEquals(8, $keys_deleted);	
		
		
		// Try deleting nonexistent keys
		// =====================================================
		
		try {
			$keys_deleted = $this->cls->delMulti('ns_1', array('var_97','var_98','var_99'), $check_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}	
				
		// The cache should report deleting 0 keys
		$this->assertEquals(0, $keys_deleted);	
		
		
		// Check for exception on mismatched offset
		// =====================================================
		
		try {						
			$keys_deleted = $this->cls->delMulti('ns_1', array('var_97','var_98','var_99'), 99 );
			
			$this->fail("Failed to throw an exception on non-matching offset");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
		// Verify the correct keys were deleted
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    
			$valid = false;
			$current_offset = false;			
			
			try {
				$value = $this->cls->get($item['ns'], $item['var'], $valid, $current_offset);
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}								
		
			if( $item['delete'] == true ){
			    
				// The cache should report the key as invalid
				$this->assertEquals(false, $valid);

				// The returned value should be null
				$this->assertEquals(null, $value);				
			}
			else {
				// The cache should report the key as valid
				$this->assertEquals(true, $valid);

				// The returned value should match the value we set
				$this->assertEquals($item['val'], $value);			    			    
			}
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);
			
		}
		unset($item);		
			
		
	}
	
	
	function test_ns_locking() {
	    	    
	    
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// Write all possible data types to two different namespaces
		// ==========================================================
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	    
		
		$test_data_a = array(
		    
			'var_1'=>null,
			'var_2'=>false,
			'var_3'=>true,		    
			'var_4'=>(int)0,
			'var_5'=>(int)1,
			'var_6'=>(int)-1,
			'var_7'=>(float)1.7,
			'var_8'=>(float)-1.6,
			'var_9'=>(string)"foo",
			'var_10'=>array(null, true, false, 1, 1.0, "foo"),	
			'var_11', 'val'=>$test_obj,
		);
		
		$test_data_b = array(
		    		    
			'var_1'=>$test_obj,
		    	'var_2'=>array(1, 1.0, "foo"),
			'var_3'=>(string)"foo",		    
			'var_4'=>(float)-1.6,	
		    	'var_5'=>(float)1.7,
			'var_6'=>(int)-1,	
		    	'var_7'=>(int)1,
			'var_8'=>(int)0,
			'var_9'=>true,			    
			'var_10'=>false,		    
			'var_11'=>null
		);		
		
		
		// Write keys to cache
		// =====================================================
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1
			
		try {
			$this->cls->setMulti('ns_1', $test_data_a, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		
		try {
			$this->cls->setMulti('ns_2', $test_data_b, $check_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
				
		// Lock ns_1
		// =====================================================		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		// Lock offset should be 1	
		$this->assertEquals(1, $lock_offset);		
		
		
		// EXCEPTION - 'LOCK' by other PID
		// =====================================================		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_1', 5.0);
			
			$this->fail("Failed to throw an exception on LOCK by foreign PID on locked namespace");
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #4 - Namespace locked
			$this->assertEquals(4, $child->data['numeric']);;		    
		}
		
		
		// EXCEPTION - 'SET' by other PID
		// =====================================================		

		$this->cls->process_id = 6900;
		
		try {
			$this->cls->set('ns_1', 'test_1', 'fail');			
			$this->fail("Failed to throw an exception on SET by foreign PID on locked namespace");
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #4 - Namespace locked
			$this->assertEquals(4, $child->data['numeric']);;		    
		}

		
		// EXCEPTION - 'GET' by other PID
		// =====================================================		

		$this->cls->process_id = 6900;
		$valid = false;
		
		try {
			$this->cls->get('ns_1', 'test_1', $valid);
			$this->fail("Failed to throw an exception on GET by foreign PID on locked namespace");
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #4 - Namespace locked
			$this->assertEquals(4, $child->data['numeric']);;		    
		}
		
				
		// Verify the keys are in the cache
		// =====================================================
		
		$this->cls->process_id = 1337;
		$current_offset = false;
		
		try {
			$result = $this->cls->getMulti('ns_1', array_keys($test_data_a), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		
		$current_offset = false;
		
		try {
			$result = $this->cls->getMulti('ns_2', array_keys($test_data_b), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		

	}
	
		
	function tearDown() {

		parent::tearDown();
	}

}



class core_mCache_driver_apc_classFunctions extends RAZ_testCase {
    
    
    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_apc(array('process_id'=>2650));			

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('APC is not active on this server');
		}
	}
	
    
	function test_writeCache_readCache() {
	    
	    	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// Write all possible data types to two different namespaces
		// ==========================================================
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	    
		
		$test_data_a = array(
		    
			'var_1'=>null,
			'var_2'=>false,
			'var_3'=>true,		    
			'var_4'=>(int)0,
			'var_5'=>(int)1,
			'var_6'=>(int)-1,
			'var_7'=>(float)1.7,
			'var_8'=>(float)-1.6,
			'var_9'=>(string)"foo",
			'var_10'=>array(null, true, false, 1, 1.0, "foo"),	
			'var_11', 'val'=>$test_obj,
		);
		
		$test_data_b = array(
		    		    
			'var_1'=>$test_obj,
		    	'var_2'=>array(1, 1.0, "foo"),
			'var_3'=>(string)"foo",		    
			'var_4'=>(float)-1.6,	
		    	'var_5'=>(float)1.7,
			'var_6'=>(int)-1,	
		    	'var_7'=>(int)1,
			'var_8'=>(int)0,
			'var_9'=>true,			    
			'var_10'=>false,		    
			'var_11'=>null
		);		
		
		
		// Write keys to cache
		// =====================================================
		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		try {
			$this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		

		// Verify the keys are in the cache
		// =====================================================
		
		$current_offset = false;
		$valid = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);		
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
				
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		$current_offset = false;
		$valid = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);			
								    	   			    
	}

	
	function test_writeCachePage_readCachePage() {
	    
	    	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// Test positive and negative versions of all PHP
		// data types in a single namespace	
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	    
		
		$test_pages_a = array(
		    
			'p_1'=>array('key_1'=>null),
			'p_2'=>array('key_1'=>false),
			'p_3'=>array('key_1'=>true),		    
			'p_4'=>array('key_1'=>(int)0),
			'p_5'=>array('key_1'=>(int)1),
			'p_6'=>array('key_1'=>(int)-1),
			'p_7'=>array('key_1'=>(float)1.7),
			'p_8'=>array('key_1'=>(float)-1.6),
			'p_9'=>array('key_1'=>(string)"foo"),
			'p_10'=>array('key_1'=>null, 'key_2'=>true, 'key_3'=>false, 'key_4'=>1, 'key_5'=>1.0, 'key_6'=>"foo"),	
			'p_11'=>array('key_1'=>$test_obj),
		);
		
		// Test the mirror image of the same data set in a second namespace
		
		$test_pages_b = array(
		    		    
			'p_1'=>array('key_1'=>$test_obj),
		    	'p_2'=>array('key_1'=>1, 'key_2'=>1.0, 'key_3'=>"foo"),
			'p_3'=>array('key_1'=>(string)"foo"),		    
			'p_4'=>array('key_1'=>(float)-1.6),	
		    	'p_5'=>array('key_1'=>(float)1.7),
			'p_6'=>array('key_1'=>(int)-1),	
		    	'p_7'=>array('key_1'=>(int)1),
			'p_8'=>array('key_1'=>(int)0),
			'p_9'=>array('key_1'=>true),			    
			'p_10'=>array('key_1'=>false),		    
			'p_11'=>array('key_1'=>null)
		);		
		
		
		// Write pages to cache
		// =====================================================
		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$test_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$test_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		
		// Fetch pages from cache
		// =====================================================
		
		$current_offset = false;
		
		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($test_pages_a)), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($test_pages_a, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		$current_offset = false;
		
		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($test_pages_b)), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// Returned keys should match original data set			
		$this->assertEquals($test_pages_b, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
				
						    	   			    
	}
	
	
	
	function test_flushCachePage() {
	    
   	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
	    
		
		// Write all possible data types to two different namespaces
		// ==========================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
		    
			// Test positive and negative versions of all PHP
			// data types in a single namespace
		    
			array('flush'=>true,	'ns'=>'ns_1',	'var'=>'var_1',	    'val'=>array('key_1'=>null)),
			array('flush'=>false,	'ns'=>'ns_1',	'var'=>'var_2',	    'val'=>array('key_1'=>false)),
			array('flush'=>true,	'ns'=>'ns_1',	'var'=>'var_3',	    'val'=>array('key_1'=>true)),		    
			array('flush'=>false,	'ns'=>'ns_1',	'var'=>'var_4',	    'val'=>array('key_1'=>(int)0)),
			array('flush'=>true,	'ns'=>'ns_1',	'var'=>'var_5',	    'val'=>array('key_1'=>(int)1)),
			array('flush'=>false,	'ns'=>'ns_1',	'var'=>'var_6',	    'val'=>array('key_1'=>(int)-1)),
			array('flush'=>true,	'ns'=>'ns_1',	'var'=>'var_7',	    'val'=>array('key_1'=>(float)1.7)),
			array('flush'=>false,	'ns'=>'ns_1',	'var'=>'var_8',	    'val'=>array('key_1'=>(float)-1.6)),
			array('flush'=>true,	'ns'=>'ns_1',	'var'=>'var_9',	    'val'=>array('key_1'=>(string)"foo")),
			array('flush'=>true,	'ns'=>'ns_1',	'var'=>'var_10',    'val'=>array('key_1'=>null, 'key_2'=>true, 'key_3'=>false, 
												 'key_4'=>1, 'key_5'=>1.0, 'key_6'=>"foo")
												),	
			array('flush'=>false,	'ns'=>'ns_1',	'var'=>'var_11',    'val'=>array('key_1'=>$test_obj)),
		    
			// Test the mirror image of the same dataset in a second namespace
		    
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_1',	    'val'=>array('key_1'=>$test_obj)),
		    	array('flush'=>false,	'ns'=>'ns_2',	'var'=>'var_2',	    'val'=>array('key_1'=>1, 'key_2'=>1.0, 'key_3'=>"foo")),
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_3',	    'val'=>array('key_1'=>(string)"foo")),		    
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_4',	    'val'=>array('key_1'=>(float)-1.6)),	
		    	array('flush'=>false,	'ns'=>'ns_2',	'var'=>'var_5',	    'val'=>array('key_1'=>(float)1.7)),
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_6',	    'val'=>array('key_1'=>(int)-1)),	
		    	array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_7',	    'val'=>array('key_1'=>(int)1)),
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_8',	    'val'=>array('key_1'=>(int)0)),
			array('flush'=>false,	'ns'=>'ns_2',	'var'=>'var_9',	    'val'=>array('key_1'=>true)),			    
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_10',    'val'=>array('key_1'=>false)),		    
			array('flush'=>true,	'ns'=>'ns_2',	'var'=>'var_11',    'val'=>array('key_1'=>null))
		    
		);
		
		
		// Write pages to cache
		// =====================================================
		
		$write_pages_a = array();
		$write_pages_b = array();
		
		foreach( $test_data as $item ){		   
			    
			if( $item['ns'] == 'ns_1' ){

				$write_pages_a[$item['var']] = $item['val'];
			}
			else {

				$write_pages_b[$item['var']] = $item['val'];
			}
				
		}
		unset($item);
		

		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$write_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$write_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
				
		
		// Verify the pages are in the cache
		// =====================================================
		
		$current_offset = false;
		
		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($write_pages_a)), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($write_pages_a, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		$current_offset = false;
		
		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($write_pages_b)), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// Returned keys should match original data set			
		$this->assertEquals($write_pages_b, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
				
		// Flush some pages
		// =====================================================
		
		$flush_pages_a = array();
		$flush_pages_b = array();
		
		foreach( $test_data as $item ){
		    
			if( $item['flush'] == true ){
			    
				if( $item['ns'] == 'ns_1' ){

					$flush_pages_a[] = $item['var'];
				}
				else {
				    
					$flush_pages_b[] = $item['var'];	
				}
			}			
		}
		unset($item);		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1
		
		try {
			$pages_deleted = $this->cls->flushCachePage( array('namespace'=>'ns_1', 'pages'=>$flush_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		// The cache engine should return the number of pages deleted
		$this->assertEquals(count($flush_pages_a), $pages_deleted);			
		
		
		try {
			$pages_deleted = $this->cls->flushCachePage( array('namespace'=>'ns_2', 'pages'=>$flush_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// The cache engine should return the number of pages deleted
		$this->assertEquals(count($flush_pages_b), $pages_deleted);
		

		
		// Verify the correct pages were flushed
		// =====================================================
		
		foreach( $test_data as $item ){		    		    		    
			
			$current_offset = false;

			try {
				$result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']), $current_offset );
			}
			catch (FOX_exception $child) {

				$this->fail($child->dumpString(1));		    
			}		

			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);			
						
			if( $item['flush'] == true ){			   

				// The key shouldn't exist in the results array
				$this->assertEquals( array(), $result);				
			}
			else {

				// The returned value should match the value we set	
				$this->assertEquals( array( $item['var']=>$item['val'] ) , $result);					
			}	
		}
		unset($item);
				
						    	   			    
	}
	
	
	function test_lockCache() {
	    
	    	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// Write all possible data types to two different namespaces
		// ==========================================================
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	    
		
		$test_data_a = array(
		    
			'var_1'=>array('key_1'=>null),
			'var_2'=>array('key_1'=>false),
			'var_3'=>array('key_1'=>true),		    
			'var_4'=>array('key_1'=>(int)0),
			'var_5'=>array('key_1'=>(int)1),
			'var_6'=>array('key_1'=>(int)-1),
			'var_7'=>array('key_1'=>(float)1.7),
			'var_8'=>array('key_1'=>(float)-1.6),
			'var_9'=>array('key_1'=>(string)"foo"),
			'var_10'=>array('key_1'=>null, 'key_2'=>true, 'key_3'=>false, 'key_4'=>1, 'key_5'=>1.0, 'key_6'=>"foo"),	
			'var_11'=>array('key_1'=>$test_obj),
		);
		
		$test_data_b = array(
		    		    
			'var_1'=>array('key_1'=>$test_obj),
		    	'var_2'=>array('key_1'=>1, 'key_2'=>1.0, 'key_3'=>"foo"),
			'var_3'=>array('key_1'=>(string)"foo"),		    
			'var_4'=>array('key_1'=>(float)-1.6),	
		    	'var_5'=>array('key_1'=>(float)1.7),
			'var_6'=>array('key_1'=>(int)-1),	
		    	'var_7'=>array('key_1'=>(int)1),
			'var_8'=>array('key_1'=>(int)0),
			'var_9'=>array('key_1'=>true),			    
			'var_10'=>array('key_1'=>false),		    
			'var_11'=>array('key_1'=>null)
		);		
		
		
		// Write keys to cache
		// =====================================================
		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		try {
			$this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		

		// Verify the keys are in the cache
		// =====================================================
		
		$current_offset = false;
		$valid = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);		
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
				
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		$current_offset = false;
		$valid = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
		
		// Lock one of the namespaces
		// =====================================================		
		
		$current_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
		
		// PASS - Trying to lock an already locked namespace
		// with the PID that owns the lock
		// =====================================================	
		
		$current_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $current_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
		
		// EXCEPTION - Trying to lock an already locked namespace
		// with a PID that doesn't own the lock
		// =====================================================	
		
		$this->cls->process_id = 9999;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $current_offset );
			
			$this->fail("Failed to throw an exception on foreign PID attempting to lock already locked namespace");
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #1 - Already locked
			$this->assertEquals(1, $child->data['numeric']);;		    
		}
		
		
		// EXCEPTION - Trying to read from a locked namespace  
		// using a PID that doesn't own the lock
		// =====================================================	
		
		$this->cls->process_id = 9999;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1') );
			
			$this->fail("Failed to throw an exception on foreign PID attempting to read locked namespace");
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #1 - Already locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}

		
		// PASS - Trying to read from a locked namespace with 
		// the PID that owns the lock
		// =====================================================	
		
		$this->cls->process_id = 2650;
		$current_offset = false;
		$valid = false;
				
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid, $current_offset);
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);		
		
		
		// Check other namespace is still unlocked
		// =====================================================		
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);
		
		
		// Release the lock
		// =====================================================
		
		try {
			$this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a) );
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_a, $cache_image);

		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);		
		
		
	}	
	
	
	function test_lockCachePage() {
	    
	    	   	
		try {
			$this->cls->flushAll();
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}			    
		
		// Write all possible data types to two different namespaces
		// ==========================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
		    
			// Test positive and negative versions of all PHP
			// data types in a single namespace
		    
			array('lock'=>true,	'ns'=>'ns_1',	'var'=>'var_1',	    'val'=>array('key_1'=>null)),
			array('lock'=>false,	'ns'=>'ns_1',	'var'=>'var_2',	    'val'=>array('key_1'=>false)),
			array('lock'=>true,	'ns'=>'ns_1',	'var'=>'var_3',	    'val'=>array('key_1'=>true)),		    
			array('lock'=>false,	'ns'=>'ns_1',	'var'=>'var_4',	    'val'=>array('key_1'=>(int)0)),
			array('lock'=>true,	'ns'=>'ns_1',	'var'=>'var_5',	    'val'=>array('key_1'=>(int)1)),
			array('lock'=>false,	'ns'=>'ns_1',	'var'=>'var_6',	    'val'=>array('key_1'=>(int)-1)),
			array('lock'=>true,	'ns'=>'ns_1',	'var'=>'var_7',	    'val'=>array('key_1'=>(float)1.7)),
			array('lock'=>false,	'ns'=>'ns_1',	'var'=>'var_8',	    'val'=>array('key_1'=>(float)-1.6)),
			array('lock'=>true,	'ns'=>'ns_1',	'var'=>'var_9',	    'val'=>array('key_1'=>(string)"foo")),
			array('lock'=>true,	'ns'=>'ns_1',	'var'=>'var_10',    'val'=>array('key_1'=>null, 'key_2'=>true, 'key_3'=>false, 
												 'key_4'=>1, 'key_5'=>1.0, 'key_6'=>"foo")
												),	
			array('lock'=>false,	'ns'=>'ns_1',	'var'=>'var_11',    'val'=>array('key_1'=>$test_obj)),
		    
			// Test the mirror image of the same dataset in a second namespace
		    
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_1',	    'val'=>array('key_1'=>$test_obj)),
		    	array('lock'=>false,	'ns'=>'ns_2',	'var'=>'var_2',	    'val'=>array('key_1'=>1, 'key_2'=>1.0, 'key_3'=>"foo")),
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_3',	    'val'=>array('key_1'=>(string)"foo")),		    
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_4',	    'val'=>array('key_1'=>(float)-1.6)),	
		    	array('lock'=>false,	'ns'=>'ns_2',	'var'=>'var_5',	    'val'=>array('key_1'=>(float)1.7)),
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_6',	    'val'=>array('key_1'=>(int)-1)),	
		    	array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_7',	    'val'=>array('key_1'=>(int)1)),
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_8',	    'val'=>array('key_1'=>(int)0)),
			array('lock'=>false,	'ns'=>'ns_2',	'var'=>'var_9',	    'val'=>array('key_1'=>true)),			    
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_10',    'val'=>array('key_1'=>false)),		    
			array('lock'=>true,	'ns'=>'ns_2',	'var'=>'var_11',    'val'=>array('key_1'=>null))
		    
		);
		
		
		// Write pages to cache
		// =====================================================
		
		$write_pages_a = array();
		$write_pages_b = array();
		
		foreach( $test_data as $item ){		   
			    
			if( $item['ns'] == 'ns_1' ){

				$write_pages_a[$item['var']] = $item['val'];
			}
			else {

				$write_pages_b[$item['var']] = $item['val'];
			}
				
		}
		unset($item);
		
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1
					
		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$write_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
			
		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$write_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
			
		
		// Verify the pages are in the cache
		// =====================================================
		
		$current_offset = false;
					
		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($write_pages_a)), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
				
		// Returned keys should match original data set		
		$this->assertEquals($write_pages_a, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);		
		
		
		$current_offset = false;
					
		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($write_pages_b)), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set			
		$this->assertEquals($write_pages_b, $result);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
				
				
		// Lock some pages
		// =====================================================
		
		$lock_pages_a = array();
		$lock_pages_b = array();
		
		foreach( $test_data as $item ){
		    
			if( $item['lock'] == true ){
			    
				if( $item['ns'] == 'ns_1' ){

					$lock_pages_a[$item['var']] = $item['val'];
				}
				else {
				    
					$lock_pages_b[$item['var']] = $item['val'];	
				}
			}			
		}
		unset($item);		
		
		
		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($lock_pages_a)) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// The cache engine should return the page image
		$this->assertEquals($lock_pages_a, $lock_image);			
		
		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($lock_pages_b)) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		// The cache engine should return true to indicate success
		$this->assertEquals($lock_pages_b, $lock_image);
		
		
		// Verify the correct pages were locked
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    			
						
			if( $item['lock'] == true ){			   

				// If the page is locked, it should throw an exception
				
				try {
					$cache_result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']) ); 
					
					$this->fail("Failed to throw an exception on locked cache page");
				}
				catch (FOX_exception $child) {

					// Should throw exception #2 - Namespace locked
					$this->assertEquals(2, $child->data['numeric']);
				}
			
			}
			else {
				$cache_result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']) ); 
				
				// The returned value should match the value we set	
				$this->assertEquals( array( $item['var']=>$item['val'] ) , $cache_result);					
			}
			
		}
		unset($item);
		
		
		// Try to read mixed locked and unlocked pages
		// =====================================================
						
		$page_names_a = array();
		$page_names_b = array();
		
		foreach( $test_data as $item ){		    
			    
			if( $item['ns'] == 'ns_1' ){

				$page_names_a[] = $item['var'];
			}
			else {

				$page_names_b[] = $item['var'];	
			}
						
		}
		unset($item);	
		
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>$page_names_a) ); 
			
			$this->fail("Failed to throw an exception on locked cache page");
		}
		catch (FOX_exception $child) {

			// Should throw exception #2 - One or more pages locked
			$this->assertEquals(2, $child->data['numeric']);
			
			// Data array should contain locked page PID/time arrays. We only check to
			// make sure the page names are correct.
			$this->assertEquals( array_keys($lock_pages_a), array_keys($child->data['data']) );			
		}
		
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>$page_names_b) ); 
		}
		catch (FOX_exception $child) {

			// Should throw exception #2 - One or more pages locked
			$this->assertEquals(2, $child->data['numeric']);
			
			// Data array should contain locked page PID/time arrays. We only check to
			// make sure the page names are correct.
			$this->assertEquals( array_keys($lock_pages_b), array_keys($child->data['data']) );			
		}
		
		
		// Read only unlocked pages
		// =====================================================		
		
		$unlocked_pages_a = array();
		$unlocked_pages_b = array();
		
		foreach( $test_data as $item ){
		    
			if( $item['lock'] != true ){
			    
				if( $item['ns'] == 'ns_1' ){

					$unlocked_pages_a[$item['var']] = $item['val'];
				}
				else {
				    
					$unlocked_pages_b[$item['var']] = $item['val'];	
				}
			}			
		}
		unset($item);
		
		
		$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($unlocked_pages_a) )); 
		
		$this->assertEquals($unlocked_pages_a, $cache_result);
		
		$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($unlocked_pages_b) )); 
		
		$this->assertEquals($unlocked_pages_b, $cache_result);		
										    	   			    
	}
	
	
	function tearDown() {

		parent::tearDown();
	}	
	
}






?>