<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - MEMORY CACHE | PERSISTENT | MEMCACHED
 * Tests the operation of the memory cache when persistent caching is available on the server via Memcached
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


require_once ( dirname( __FILE__ ) . '/abstract_cache.driver.functions.php' );
require_once ( dirname( __FILE__ ) . '/abstract_cache.driver.ops.php' );


class core_mCache_driver_memcached_ops_basic extends core_mCache_driver_ops {

    
    	function setUp() {
		
		parent::setUp();
	
		$args = array(
			'compress_threshold' => 0.2,
			'flush_propagation_delay' => 1200,
			'set_propagation_delay' => 0,		    
		);
		
		$this->cls = new FOX_mCache_driver_memcached($args);
		
		// Force class to use 'basic' mode
		$this->cls->use_full = false;
		$this->cls->use_basic = true;		    
		$this->cls->use_portable = false;
		
		// Run the contructor function again to update
		$this->cls->__construct($args);		

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Memcached is not active on this server');
		}
	}
			
	function tearDown() {

		parent::tearDown();
	}

}


class core_mCache_driver_memcached_classFunctions_basic extends core_mCache_driver_classFunctions {
    
        
    	function setUp() {
		
		parent::setUp();
	
		$args = array(
			'compress_threshold' => 0.2,
			'flush_propagation_delay' => 1200,
			'set_propagation_delay' => 0,		    
		);
		
		$this->cls = new FOX_mCache_driver_memcached($args);
		
		// Force class to use 'basic' mode
		$this->cls->use_full = false;
		$this->cls->use_basic = true;		    
		$this->cls->use_portable = false;
				
		// Run the contructor function again to update
		$this->cls->__construct($args);			

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Memcached is not active on this server');
		}
	}
	
    	
	function tearDown() {

		parent::tearDown();
	}		
}


//class core_mCache_driver_memcached_ops_full extends core_mCache_driver_memcached_ops_basic {
//    
//    
//    	function setUp() {
//		
//		parent::setUp();
//	
//		$this->cls = new FOX_mCache_driver_memcached();
//		
//		// Force class to use 'full' mode
//		
//		$this->cls->use_full = true;
//		$this->cls->use_basic = false;		    
//		$this->cls->use_portable = false;
//
//		// Run the contructor function again to update
//		$this->cls->__construct();
//
//		if( !$this->cls->isActive() ){
//
//			$this->markTestSkipped('Memcached is not active on this server');
//		}
//	}        
//}
//
//
//class core_mCache_driver_memcached_ops_portable extends core_mCache_driver_memcached_ops_basic {
//    
//    
//    	function setUp() {
//		
//		parent::setUp();
//	
//		$this->cls = new FOX_mCache_driver_memcached();
//		
//		// Force class to use 'basic' mode
//		
//		$this->cls->use_full = false;
//		$this->cls->use_basic = false;		    
//		$this->cls->use_portable = true;
//
//		// Run the contructor function again to update
//		$this->cls->__construct();
//
//		if( !$this->cls->isActive() ){
//
//			$this->markTestSkipped('Memcached is not active on this server');
//		}
//	}        
//}



// The code below is used to test our advanced Memcached drivers,
// once they're built
// ====================================================================================================


//class core_mCache_driver_memcached_classFunctions_full extends core_mCache_driver_memcached_classFunctions_basic {
//    
//    
//    	function setUp() {
//		
//		parent::setUp();
//	
//		$this->cls = new FOX_mCache_driver_memcached();
//		
//		// Force class to use 'basic' mode
//		$this->cls->use_full = true;
//		$this->cls->use_basic = false;		    
//		$this->cls->use_portable = false;
//		
//		// Run the contructor function again to update
//		$this->cls->__construct();			
//
//		if( !$this->cls->isActive() ){
//
//			$this->markTestSkipped('Memcached is not active on this server');
//		}
//	}
//	
//}
//
//
//class core_mCache_driver_memcached_classFunctions_portable extends core_mCache_driver_memcached_classFunctions_basic {
//    
//    
//    	function setUp() {
//		
//		parent::setUp();
//	
//		$this->cls = new FOX_mCache_driver_memcached();
//		
//		// Force class to use 'portable' mode
//		$this->cls->use_full = false;
//		$this->cls->use_basic = false;		    
//		$this->cls->use_portable = true;
//		
//		// Run the contructor function again to update
//		$this->cls->__construct();			
//
//		if( !$this->cls->isActive() ){
//
//			$this->markTestSkipped('Memcached is not active on this server');
//		}
//	}
//	
//}

?>