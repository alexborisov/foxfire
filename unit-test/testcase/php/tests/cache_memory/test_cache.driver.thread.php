<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - MEMORY CACHE | TRANSIENT | THREAD
 * Tests the operation of the memory cache when using the transient thread cache
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


class core_mCache_driver_thread_ops extends RAZ_testCase {

    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_thread();				

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Thread cache is not active on this server');
		}
	}
	

	function test_set_single_get_single() {
	    	    
	    
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);		
	    
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
		    
			$set_ok = $this->cls->set($item['ns'], $item['var'], $item['val']);
			
			// The cache engine should return true to indicate the key was set
			$this->assertEquals(true, $set_ok);		    
		}
		unset($item);
		
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);	
			
		}
		unset($item);			    	   		
	    
	}
	
	
	function test_set_multi_get_multi() {
	    
	    	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);
		
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
		
		$set_ok = $this->cls->setMulti('ns_1', $test_data_a);
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);	
		
		$set_ok = $this->cls->setMulti('ns_2', $test_data_b);
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);
		
		
		// Fetch keys from cache
		// =====================================================
		
		$result = $this->cls->getMulti('ns_1', array_keys($test_data_a) );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $result);
		
		$result = $this->cls->getMulti('ns_2', array_keys($test_data_b) );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);		
						    	   			    
	}
	
	
	function test_flushAll() {

	    
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);		
	    
		
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
		    
			$set_ok = $this->cls->set($item['ns'], $item['var'], $item['val']);
			
			// The cache engine should return true to indicate the key was set
			$this->assertEquals(true, $set_ok);		    
		}
		unset($item);
		
		
		// Verify all keys are in the cache
		// ===================================================
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);
			
		}
		unset($item);	
		
		// Flush the cache
		// ===================================================
		
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);	
		
		
		// Verify all keys return null and are flagged as invalid
		// ===================================================
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
			
			// The cache should report the key as valid
			$this->assertEquals(false, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals(null, $value);
			
		}
		unset($item);			
	    
	}	
	
	
	function test_flushNamespace() {
	    
	    	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);
		
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
		
		$set_ok = $this->cls->setMulti('ns_1', $test_data_a);
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);	
		
		$set_ok = $this->cls->setMulti('ns_2', $test_data_b);
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		$result = $this->cls->getMulti('ns_1', array_keys($test_data_a) );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $result);
		
		$result = $this->cls->getMulti('ns_2', array_keys($test_data_b) );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);
		
		
		// Flush one of the namespaces
		// =====================================================
		
		$flush_ok = $this->cls->flushNamespace('ns_1');
		
		$this->assertEquals(true, $flush_ok);
		
		
		// Verify the keys in the flushed namespace were cleared
		// =====================================================
		
		$result = $this->cls->getMulti('ns_1', array_keys($test_data_a) );
		
		// Returned keys should be an empty array	
		$this->assertEquals(array(), $result);
		
		$result = $this->cls->getMulti('ns_2', array_keys($test_data_b) );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $result);	
								    	   			    
	}
	

	function test_del_single() {
	    	    
	    
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);		
	    
		
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
		    
			$set_ok = $this->cls->set($item['ns'], $item['var'], $item['val']);
			
			// The cache engine should return true to indicate the key was set
			$this->assertEquals(true, $set_ok);		    
		}
		unset($item);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);	
			
		}
		unset($item);	
		
		
		// Delete some keys
		// =====================================================
		
		foreach( $test_data as $item ){
		    
			if( $item['delete'] == true ){
			    
				$del_ok = $this->cls->del($item['ns'], $item['var']);

				// The cache should report the key as valid
				$this->assertEquals(true, $del_ok);			    			    
			}			
		}
		unset($item);	
		
		
		// Verify the correct keys were deleted
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
						
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
		}
		unset($item);	
		
		
		// Check deleting nonexistent key from valid namespace fails
		// =====================================================
		
		$del_ok = $this->cls->del('ns_1', 'var_99');

		// The cache should report the key as valid
		$this->assertEquals(false, $del_ok);	
		
		
		// Check deleting valid key from nonexistent namespace fails
		// =====================================================
		
		$del_ok = $this->cls->del('ns_99', 'var_2');

		// The cache should report the key as valid
		$this->assertEquals(false, $del_ok);		
			
		
	}
	
	
	function test_del_multi() {
	    	    
	    
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);		
	    
		
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
		    
			$set_ok = $this->cls->set($item['ns'], $item['var'], $item['val']);
			
			// The cache engine should return true to indicate the key was set
			$this->assertEquals(true, $set_ok);		    
		}
		unset($item);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['val'], $value);	
			
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
		
		
		$keys_deleted = $this->cls->delMulti('ns_1', $del_keys_a);

		// The cache should report deleting 6 keys
		$this->assertEquals(6, $keys_deleted);	
		
		
		$keys_deleted = $this->cls->delMulti('ns_2', $del_keys_b);

		// The cache should report deleting 8 keys
		$this->assertEquals(8, $keys_deleted);	
		
		
		// Try deleting nonexistent keys
		$keys_deleted = $this->cls->delMulti('ns_1', array('var_97','var_98','var_99') );

		// The cache should report deleting 0 keys
		$this->assertEquals(0, $keys_deleted);			
				
		
		// Verify the correct keys were deleted
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    
			$valid = false;
			$value = $this->cls->get($item['ns'], $item['var'], $valid);
						
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
		}
		unset($item);		
			
		
	}
	
		
	function tearDown() {

		parent::tearDown();
	}

}

class core_mCache_driver_thread_classFunctions extends RAZ_testCase {
    
    
    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_thread();			

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Thread cache is not active on this server');
		}
	}
	
    
	function test_writeCache_readCache() {
	    
	    	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);
		
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
		
		$set_ok = $this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a) );
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);	
		
		$set_ok = $this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b) );
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_1') );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_2') );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);
		
								    	   			    
	}

	
	function test_writeCachePage_readCachePage() {
	    
	    	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);
		
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
		
		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$test_pages_a) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);			
		
		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$test_pages_b) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);
		
		
		// Fetch pages from cache
		// =====================================================
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($test_pages_a)) );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_pages_a, $result);
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($test_pages_b)) );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_pages_b, $result);		
						    	   			    
	}
	
	
	
	function test_flushCachePage() {
	    
   	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);		
	    
		
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
		

		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$write_pages_a) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);			
		
		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$write_pages_b) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);		
		
		
		
		// Verify the pages are in the cache
		// =====================================================
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($write_pages_a)) );
		
		// Returned keys should match original data set		
		$this->assertEquals($write_pages_a, $result);
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($write_pages_b)) );
		
		// Returned keys should match original data set			
		$this->assertEquals($write_pages_b, $result);
		
				
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
		
		$flush_ok = $this->cls->flushCachePage( array('namespace'=>'ns_1', 'pages'=>$flush_pages_a) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $flush_ok);			
		
		$flush_ok = $this->cls->flushCachePage( array('namespace'=>'ns_2', 'pages'=>$flush_pages_b) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $flush_ok);
		

		
		// Verify the correct pages were flushed
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    
			$cache_result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']) ); 
						
			if( $item['flush'] == true ){			   

				// The key shouldn't exist in the results array
				$this->assertEquals( array() , $cache_result);				
			}
			else {

				// The returned value should match the value we set	
				$this->assertEquals( array( $item['var']=>$item['val'] ) , $cache_result);					
			}	
		}
		unset($item);
				
						    	   			    
	}
	
	
	function test_lockCache() {
	    
	    	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);
		
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
		
		$set_ok = $this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a) );
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);	
		
		$set_ok = $this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b) );
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_1') );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_2') );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);
		
		
		// Lock one of the namespaces
		// =====================================================		
		
		$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1') );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);
		
		
		// Check attempting to lock an already locked namespace
		// =====================================================	
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1') );
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #4 - Already locked
			$this->assertEquals(4, $child->data['numeric']);;		    
		}
		
		
		// Check attempting to read a locked namespace
		// =====================================================	
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1') );
		}
		catch (FOX_exception $child) {
		    
			// Should throw exception #2 - Namespace locked
			$this->assertEquals(2, $child->data['numeric']);;		    
		}
		
		// Check other namespace is still unlocked
		// =====================================================		
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_2') );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);
		
		
		// Release the lock
		// =====================================================
		
		$set_ok = $this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a) );
		
		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);
		
		
		// Verify the keys are in the cache
		// =====================================================
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_1') );
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_2') );
		
		// Returned keys should match original data set			
		$this->assertEquals($test_data_b, $cache_image);		
		
		
	}	
	
	
	function test_lockCachePage() {
	    
	    	   	
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);		
	    
		
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
		

		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$write_pages_a) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);			
		
		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$write_pages_b) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);		
		
		
		
		// Verify the pages are in the cache
		// =====================================================
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($write_pages_a)) );
		
		// Returned keys should match original data set		
		$this->assertEquals($write_pages_a, $result);
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($write_pages_b)) );
		
		// Returned keys should match original data set			
		$this->assertEquals($write_pages_b, $result);
		
				
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
		
		$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($lock_pages_a)) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals($lock_pages_a, $lock_image);			
		
		$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($lock_pages_b)) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals($lock_pages_b, $lock_image);
		
		
		// Verify the correct pages were locked
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    			
						
			if( $item['lock'] == true ){			   

				// If the page is locked, it should throw an exception
				
				try {
					$cache_result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']) ); 
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