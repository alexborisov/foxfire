<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - MEMORY CACHE | TRANSIENT | LOOPBACK
 * Tests the operation of the memory cache when no other cache drivers are active on the system
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


class core_mCache_driver_loopback_ops extends RAZ_testCase {

    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new BPM_mCache_driver_loopback();				

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Loopback cache is not active on this server');
		}
	}
	

	function test_set_single_get_single() {
	    	    
	    
		$set_ok = $this->cls->set('ns_1', 'var_1', 'test_value');

		// The cache engine should return true to indicate the key was set
		$this->assertEquals(true, $set_ok);
		
		 
		// Try to fetch the key we just set
		$valid = false;
		$value = $this->cls->get('ns_1', 'var_1', $valid);

		// The cache should report the key as not valid valid
		$this->assertEquals(false, $valid);

		// The returned value should be null
		$this->assertEquals(null, $value);	
					    	   		
	    
	}
	
	
	function test_set_multi_get_multi() {
	    
	    	   	
		// Write keys to cache
		// =====================================================
		
		$test_data_a = array(
		    
			'var_1'=>null,
			'var_2'=>false,
			'var_3'=>true,		    
			'var_4'=>(int)0,
			'var_5'=>(int)1,
			'var_6'=>(int)-1,
		);
		
		$set_ok = $this->cls->setMulti('ns_1', $test_data_a);
		
		// The cache engine should return true to indicate the keys were set
		$this->assertEquals(true, $set_ok);	
		
				
		// Fetch keys from cache
		// =====================================================
		
		$result = $this->cls->getMulti('ns_1', array_keys($test_data_a) );
		
		// Returned keys should be an empty array	
		$this->assertEquals(array(), $result);
				
						    	   			    
	}
	
	
	function test_flushAll() {
	    
		$flush_ok = $this->cls->flushAll();
		$this->assertEquals(true, $flush_ok);					    
	}	
	
	
	function test_flushNamespace() {
	    
		
		$flush_ok = $this->cls->flushNamespace('ns_1');
		
		$this->assertEquals(true, $flush_ok);
								    	   			    
	}
	

	function test_del_single() {
	    	    
	    
			    
		$del_ok = $this->cls->del('ns_1', 'var_1');

		// The cache should report the key was successfully deleted
		$this->assertEquals(true, $del_ok);			    			    
		
	}
	
	
	function test_del_multi() {
	    	    
	    
		$del_keys_a = array('var_1','var_3','var_3','var_4','var_5','var_6');		
		
		$keys_deleted = $this->cls->delMulti('ns_1', $del_keys_a);

		// The cache should report deleting 6 keys
		$this->assertEquals(6, $keys_deleted);	
		
	}
	
		
	function tearDown() {

		parent::tearDown();
	}

}

class core_mCache_driver_loopback_classFunctions extends RAZ_testCase {
    
    
    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new BPM_mCache_driver_loopback();			

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Loopback cache is not active on this server');
		}
	}
	
    
	function test_writeCache_readCache() {
	    
		
		$set_ok = $this->cls->writeCache( array('namespace'=>'ns_1', 'image'=>array('var_1'=>'A','var_2'=>'B')) );
		
		// Should return true to indicate success	
		$this->assertEquals(true, $set_ok);
		
		$valid = false;
		
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid );
						
		// Returned image should be NULL	
		$this->assertEquals(null, $cache_image);
		
		// Image should be invalid	
		$this->assertEquals(false, $valid);		
										    	   			    
	}

	
	function test_writeCachePage_readCachePage() {
	    	    	      
		
		$test_pages_a = array(
		    
			'p_1'=>array('key_1'=>null),
			'p_2'=>array('key_1'=>false),
			'p_3'=>array('key_1'=>true),		    
			'p_4'=>array('key_1'=>(int)0),
		);
	
		
		$write_ok = $this->cls->writeCachePage( array('namespace'=>'ns_1', 'pages'=>$test_pages_a) );
		
		// The cache engine should return true to indicate success
		$this->assertEquals(true, $write_ok);					
		
		$result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array_keys($test_pages_a)) );
		
		// Should return an empty array
		$this->assertEquals( array(), $result);
			
						    	   			    
	}
	
	
	
	function test_flushCachePage() {
	       	   	
		
		$flush_ok = $this->cls->flushCachePage( array('namespace'=>'ns_1', 'pages'=>array('p_1','p_2','p_3')) );
		
		// Should return true
		$this->assertEquals(true, $flush_ok);			
												    	   			    
	}
	
	
	function test_lockCache() {
	    
	    			
		// Lock a namespace	
		
		$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1') );
		
		// Should return empty array		
		$this->assertEquals(array(), $cache_image);				
		
		// Check attempting to lock an already locked namespace
		
		$cache_image = $this->cls->lockCache( array('namespace'=>'ns_1') );
		
		// Should allow it and return empty array
		$this->assertEquals(array(), $cache_image);		

		
		// Check attempting to read a locked namespace	
		
		$valid = false;
		$cache_image = $this->cls->readCache( array('namespace'=>'ns_1'), $valid);

		// Should return NULL value
		$this->assertEquals(null, $cache_image);
		
		// Should be invalid
		$this->assertEquals(false, $valid);		
		
	}	
	
	
	function test_lockCachePage() {
	    
	    
		// Lock multiple cache pages
		$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array('p_1','p_2','p_3') ) );
		
		// Should return an array of empty cache page arrays
		$this->assertEquals( array('p_1'=>array(),'p_2'=>array(),'p_3'=>array()), $lock_image);	
		
		
		// Lock the same pages again
		$lock_image = $this->cls->lockCachePage( array('namespace'=>'ns_1', 'pages'=>array('p_1','p_2','p_3') ) );
		
		// Should return an array of empty cache page arrays
		$this->assertEquals( array('p_1'=>array(),'p_2'=>array(),'p_3'=>array()), $lock_image);		
		
		// Try to read the locked pages
		$cache_result = $this->cls->readCachePage( array('namespace'=>'ns_1', 'pages'=>array('p_1','p_2','p_3') ) ); 
		
		// Should return an empty array
		$this->assertEquals( array(), $cache_result);		

										    	   			    
	}
	
	
	function tearDown() {

		parent::tearDown();
	}	
	
}

?>
