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


abstract class core_mCache_driver_classFunctions extends RAZ_testCase {
    
    
    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_apc(array('process_id'=>2650));			

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('APC is not active on this server');
		}
	}
	
    
	function test_writeCache_readCache() {
	    
	    
	    	// NOTE: lockNamespace() is not used directly on a monolithic cache. Instead, 
		// the cache is locked using lockCache() and the lock is cleared by either
		// flushCache(), writeCache(), or saveCache(). Use of lockCache() on monolithic
		// caches is blocked by exception in descendent abstract class "FOX_db_base".
	    
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

		
		// NOTE: if there is currently a lock on a monolithic cache namespace, 
		// and if the PID that owns the lock writes to the cache using writeCache() 
		// it will clear that lock. If a foreign PID tries to use writeCache()
		// on a namespace locked by a different PID, it will trigger an exception.
					
		
		// Lock ns_1 as PID #1337 and ns_2 as PID #6900
		// ########################################################		

		$this->cls->process_id = 1337;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);
		
		
		$this->cls->process_id = 6900;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_2', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);
		
		
		// Verify PID #6900 can't write to ns_1
		// =====================================================	
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1
		
		$this->cls->process_id = 6900;
		
		try {
			$this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a, 'check_offset'=>$check_offset) );
			
			$fail_msg  = "Failed to throw an exception on writeCache() by foreign ";
			$fail_msg .= "PID on locked namespace";
			
			$this->fail($fail_msg);			
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// Verify PID #1337 can write to ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;

		try {
			$this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}			

		// Verify PID #1337 can't write to ns_2
		// =====================================================	
		
		$this->cls->process_id = 1337;
		
		try {
			$this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b, 'check_offset'=>$check_offset) );
			
			$fail_msg  = "Failed to throw an exception on writeCache() by foreign ";
			$fail_msg .= "PID on locked namespace";
			
			$this->fail($fail_msg);				
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}		
		
		// Verify PID #6900 can write to ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;

		try {
			$this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
	
		// Lock ns_1 as PID #1337 and ns_2 as PID #6900
		// (locks were cleared by previous successful writes)
		// ########################################################		

		$this->cls->process_id = 1337;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);
		
		
		$this->cls->process_id = 6900;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_2', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);		
		
		
		// Verify PID #1337 can read from ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;
				
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
		
		
		// Verify PID #6900 can't read from ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid, $current_offset );
			
			$fail_msg  = "Failed to throw an exception on readCache() by foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);	
			
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}		
		
		// Verify PID #6900 can read from ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;
				
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
		
		
		// Verify PID #1337 can't read from ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid, $current_offset );
			
			$fail_msg  = "Failed to throw an exception on readCache() by foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);	
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// Write test data to ns_1 and ns_2, clearing locks
		// ########################################################
		
		$this->cls->process_id = 1337;
				
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>$test_data_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}			

		$this->cls->process_id = 6900;
		
		try {
			$this->cls->writeCache( array('namespace'=>'ns_2', 'image'=>$test_data_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
				
		// Verify PID #1337 can read from ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;
				
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
		
		
		// Verify PID #6900 can read from ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;
				
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
		
		
		// Verify PID #1337 can read from ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;
				
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
		
		
		// Verify PID #6900 can read from ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;				
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
		
		// Lock ns_1 as PID #1337 and ns_2 as PID #6900
		// ########################################################		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		$this->assertEquals(1, $lock_offset);		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);
		
		
		// Verify PID #1337 can write to ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$test_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		
		// Verify PID #6900 can't write to ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$test_pages_a, 'check_offset'=>$check_offset) );
			
			$fail_msg  = "Failed to throw an exception on writeCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);	

		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// Verify PID #6900 can write to ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;
		$check_offset = 1;  

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$test_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		
		// Verify PID #1337 can't write to ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;
		$check_offset = 1;  

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$test_pages_b, 'check_offset'=>$check_offset) );
			
			$fail_msg  = "Failed to throw an exception on writeCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);	
			
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// Verify PID #1337 can read from ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;		
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
		
		
		// Verify PID #6900 can't read from ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;

		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($test_pages_a)), $current_offset );
			
			$fail_msg  = "Failed to throw an exception on readCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);				
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// Verify PID #6900 can read from ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;		
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
		
		
		// Verify PID #1337 can't read from ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;

		try {
			$result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($test_pages_b)), $current_offset );
			
			$fail_msg  = "Failed to throw an exception on readCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);					
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// Unlock ns_1 as PID #1337
		// =====================================================	

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);	
		
		
		// Unlock ns_2 as PID #6900
		// =====================================================	

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);		

			
		// Verify PID #6900 can write to ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$test_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
				
		// Verify PID #1337 can write to ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$test_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		
		// Verify PID #6900 can read from ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;		
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
		
		
		// Verify PID #1337 can read from ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;		
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
		
		
		// Lock ns_1 as PID #1337 and ns_2 as PID #6900
		// ########################################################		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		$this->assertEquals(1, $lock_offset);		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);
		
		
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
		
		
		// Verify PID #6900 can't flush pages from ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;
		$check_offset = 1; 

		try {
			$pages_deleted = $this->cls->flushCachePage( array('namespace'=>'ns_1', 'pages'=>$flush_pages_a, 'check_offset'=>$check_offset) );
			
			$fail_msg  = "Failed to throw an exception on flushCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);	
			
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		// Verify PID #1337 can flush pages from ns_1
		// =====================================================		

		$this->cls->process_id = 1337;
		
		try {
			$pages_deleted = $this->cls->flushCachePage( array('namespace'=>'ns_1', 'pages'=>$flush_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		// The cache engine should return the number of pages deleted
		$this->assertEquals(count($flush_pages_a), $pages_deleted);			
		
		
		// Verify PID #1337 can't flush pages from ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;

		try {
			$pages_deleted = $this->cls->flushCachePage( array('namespace'=>'ns_2', 'pages'=>$flush_pages_b, 'check_offset'=>$check_offset) );
			
			$fail_msg  = "Failed to throw an exception on flushCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace";
			
			$this->fail($fail_msg);	
			
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		// Verify PID #6900 can flush pages from ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;		
		
		try {
			$pages_deleted = $this->cls->flushCachePage( array('namespace'=>'ns_2', 'pages'=>$flush_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
		
		// The cache engine should return the number of pages deleted
		$this->assertEquals(count($flush_pages_b), $pages_deleted);
		
		
		// Unlock ns_1 as PID #1337
		// =====================================================		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);	
		
		
		// Unlock ns_2 as PID #6900
		// =====================================================		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);
		
		
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
		
		
		$test_data_c = array(
		    		    
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
		
		try {
			$this->cls->writeCache( array('namespace'=>'ns_3', 'image'=>$test_data_c, 'check_offset'=>$check_offset) );
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
		
		$current_offset = false;
		$valid = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_3'), $valid, $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		

		// Returned keys should match original data set			
		$this->assertEquals($test_data_c, $cache_image);
		
		// The cache should be valid
		$this->assertEquals(true, $valid);
		
		// The reported offset should be 1
		$this->assertEquals(1, $current_offset);
		
		
		// Lock ns_1 as PID #1337 and ns_2 as PID #6900
		// ########################################################		

		$this->cls->process_id = 1337;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);		
		$this->assertEquals(1, $current_offset);
		
		
		$this->cls->process_id = 6900;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_2', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_b, $cache_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// PASS - PID #1337 attempting to lock ns_1
		// =====================================================	
		
		$this->cls->process_id = 1337;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// EXCEPTION - PID #6900 attempting to lock ns_1
		// =====================================================	
		
		$this->cls->process_id = 6900;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1', 'seconds'=>5.0), $lock_offset );
			
			$fail_msg  = "Failed to throw an exception on lockCache() by a foreign ";
			$fail_msg .= "PID on a namespace currently locked by a different PID";
			
			$this->fail($fail_msg);				

		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// EXCEPTION - PID #6900 attempting to read ns_1
		// =====================================================	
		
		$this->cls->process_id = 6900;

		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid);
			
			$fail_msg  = "Failed to throw an exception on readCache() by a foreign ";
			$fail_msg .= "PID on a locked namespace.";
			
			$this->fail($fail_msg);	
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// PASS - PID #6900 attempting to lock ns_2
		// =====================================================	
		
		$this->cls->process_id = 6900;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_2', 'seconds'=>5.0), $lock_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_b, $cache_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// EXCEPTION - PID #1337 attempting to lock ns_2
		// =====================================================	
		
		$this->cls->process_id = 1337;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->lockCache( array('namespace'=>'ns_2', 'seconds'=>5.0), $lock_offset );
			
			$fail_msg  = "Failed to throw an exception on lockCache() by a foreign ";
			$fail_msg .= "PID on a locked namespace.";
			
			$this->fail($fail_msg);				
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
			
		
		// EXCEPTION - PID #1337 attempting to read ns_2
		// =====================================================	
		
		$this->cls->process_id = 1337;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid);
			
			$fail_msg  = "Failed to throw an exception on readCache() by a foreign ";
			$fail_msg .= "PID on a locked namespace.";
			
			$this->fail($fail_msg);				
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}
		
		
		// PASS - PID #2650 attempting to read ns_3
		// =====================================================	
		
		$this->cls->process_id = 6900;
		$current_offset = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_3'), $valid, $current_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_c, $cache_image);
		$this->assertEquals(1, $current_offset);
		
		
		// Unlock ns_1 as PID #1337
		// =====================================================		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);	
		
		
		// Unlock ns_2 as PID #6900
		// =====================================================		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);
		
		
		// PASS - PID #2650 attempting to read ns_1
		// =====================================================	
		
		$this->cls->process_id = 2650;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid, $current_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_a, $cache_image);
		
		$this->assertEquals(1, $current_offset);	
		
		
		// PASS - PID #2650 attempting to read ns_2
		// =====================================================	
		
		$this->cls->process_id = 2650;
		$lock_offset = false;
		
		try {
			$cache_image = $this->cls->readCache( array('namespace'=>'ns_2'), $valid, $current_offset );			
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
		
		// Returned keys should match original data set		
		$this->assertEquals($test_data_b, $cache_image);		
		$this->assertEquals(1, $current_offset);		
		
		
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
		$this->assertEquals(1, $current_offset);
				
		
		// Lock ns_1 as PID #1337 and ns_2 as PID #6900
		// ########################################################		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
		
		$this->assertEquals(1, $lock_offset);		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->lockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);
		
		
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
		
		
		// Verify PID #1337 can lock pages in ns_1
		// =====================================================
		
		$this->cls->process_id = 1337;
		$current_offset = false;
		
		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($lock_pages_a), 'seconds'=>5.0), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
				
		// The cache engine should return the page images
		$this->assertEquals($lock_pages_a, $lock_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// Verify PID #6900 can't lock pages in ns_1
		// =====================================================
		
		$this->cls->process_id = 6900;

		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($lock_pages_a), 'seconds'=>5.0) );
			
			$fail_msg  = "Failed to throw an exception on lockCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace.";
			
			$this->fail($fail_msg);				

		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}		
		
		
		// Verify PID #6900 can lock pages in ns_2
		// =====================================================
		
		$this->cls->process_id = 6900;
		$current_offset = false;
		
		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($lock_pages_b), 'seconds'=>5.0), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
				
		// The cache engine should return the page images
		$this->assertEquals($lock_pages_b, $lock_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// Verify PID #1337 can't lock pages in ns_2
		// =====================================================
		
		$this->cls->process_id = 1337;

		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($lock_pages_b), 'seconds'=>5.0) );
			
			$fail_msg  = "Failed to throw an exception on lockCachePage() by a foreign ";
			$fail_msg .= "PID on a locked namespace.";
			
			$this->fail($fail_msg);	
		}
		catch (FOX_exception $child) {

			// Should throw exception #1 - Namespace locked
			$this->assertEquals(1, $child->data['numeric']);		    
		}		

		
		// Unlock ns_1 as PID #1337
		// =====================================================		

		$this->cls->process_id = 1337;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_1', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}				
			
		$this->assertEquals(1, $lock_offset);	
		
		
		// Unlock ns_2 as PID #6900
		// =====================================================		

		$this->cls->process_id = 6900;
		
		try {
			$lock_offset = $this->cls->unlockNamespace('ns_2', 5.0);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertEquals(1, $lock_offset);			
		
		
		// Verify the correct pages were locked
		// =====================================================
		
		foreach( $test_data as $item ){
		    		    		    			
			$current_offset = false;

			if( $item['lock'] == true ){			   

				// If the page is locked, it should throw an exception
				
				try {
					$cache_result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']), $current_offset ); 
					
					$this->fail("Failed to throw an exception on readCachePage() on an a locked cache page");
				}
				catch (FOX_exception $child) {

					// Should throw exception #3 - One or more pages are currently locked
					$this->assertEquals(3, $child->data['numeric']);
				}
			
			}
			else {
				$cache_result = $this->cls->readCachePage( array('namespace'=>$item['ns'], 'pages'=>$item['var']), $current_offset ); 
				
				// The returned value should match the value we set	
				$this->assertEquals( array( $item['var']=>$item['val'] ) , $cache_result);					
			}
			
			// The reported offset should be 1
			$this->assertEquals(1, $current_offset);
			
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
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>$page_names_a), $current_offset); 			
			$this->fail("Failed to throw an exception on readCachePage() on an a locked cache page");
		}
		catch (FOX_exception $child) {

			// Should throw exception #3 - One or more pages are currently locked
			$this->assertEquals(3, $child->data['numeric']);
			
			// Data array should contain locked page PID/time arrays. We only check to
			// make sure the page names are correct.
			$this->assertEquals( array_keys($lock_pages_a), array_keys($child->data['data']) );			
		}				
		
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>$page_names_b), $current_offset); 
			$this->fail("Failed to throw an exception on readCachePage() on an a locked cache page");
		}
		catch (FOX_exception $child) {

			// Should throw exception #3 - One or more pages locked
			$this->assertEquals(3, $child->data['numeric']);
			
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
		
		
		$current_offset = false;
			
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($unlocked_pages_a)), $current_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($unlocked_pages_a, $cache_result);		
		$this->assertEquals(1, $current_offset);		
		
		
		$current_offset = false;
		
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($unlocked_pages_b)), $current_offset); 
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
				
		$this->assertEquals($unlocked_pages_b, $cache_result);			
		$this->assertEquals(1, $current_offset);
		
		
		
		// Verify PID #1337 can lock pages it already owns
		// =====================================================
		
		$this->cls->process_id = 1337;
		$current_offset = false;
		
		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($lock_pages_a), 'seconds'=>5.0), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
				
		// The cache engine should return the page images
		$this->assertEquals($lock_pages_a, $lock_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// Verify PID #6900 can't lock pages that PID #1337 owns
		// =====================================================
		
		$this->cls->process_id = 6900;

		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($lock_pages_a), 'seconds'=>5.0) );
			
			$fail_msg  = "Failed to throw an exception on lockCachePage() on a cache ";
			$fail_msg .= "page already locked by a foreign PID.";
			
			$this->fail($fail_msg);	
		}
		catch (FOX_exception $child) {

			// Should throw exception #3 - One or more pages locked
			$this->assertEquals(3, $child->data['numeric']);		    
		}		
		
		
		// Verify PID #6900 can lock pages it already owns
		// =====================================================
		
		$this->cls->process_id = 6900;
		$current_offset = false;
		
		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($lock_pages_b), 'seconds'=>5.0), $current_offset );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
				
		// The cache engine should return the page images
		$this->assertEquals($lock_pages_b, $lock_image);		
		$this->assertEquals(1, $current_offset);
		
		
		// Verify PID #1337 can't lock pages that PID #6900 owns
		// =====================================================
		
		$this->cls->process_id = 1337;

		try {
			$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($lock_pages_b), 'seconds'=>5.0) );
			
			$fail_msg  = "Failed to throw an exception on lockCachePage() on a cache ";
			$fail_msg .= "pages already locked by a foreign PID.";
			
			$this->fail($fail_msg);	
			
		}
		catch (FOX_exception $child) {

			// Should throw exception #3 - One or more pages locked
			$this->assertEquals(3, $child->data['numeric']);		    
		}
		
		
		// Clear PID #1337's locks by writing to its pages
		// =====================================================
		
		$this->cls->process_id = 1337;
		
		$check_offset = 1;  // Since the cache has been globally flushed, and the
				    // namespace hasn't been flushed since, offset will be 1

		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$write_pages_a, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		
		// Clear PID #6900's locks by writing to its pages
		// =====================================================
		
		$this->cls->process_id = 6900;
		
		try {
			$this->cls->writeCachePage( array('namespace'=>'ns_2', 'pages'=>$write_pages_b, 'check_offset'=>$check_offset) );
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
			
		
		// Check the cache pages are now unlocked
		// =====================================================
		
		$this->cls->process_id = 2650;		
		$current_offset = false;
			
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($unlocked_pages_a)), $current_offset);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}

		$this->assertEquals($unlocked_pages_a, $cache_result);		
		$this->assertEquals(1, $current_offset);		
		
		
		$current_offset = false;
		
		try {
			$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_2', 'pages'=>array_keys($unlocked_pages_b)), $current_offset); 
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}		
				
		$this->assertEquals($unlocked_pages_b, $cache_result);			
		$this->assertEquals(1, $current_offset);
		
		
	}
	
	
	function tearDown() {

		parent::tearDown();
	}	
	
}

?>