<?php

/**
 * ### WARNING #########################################################################################
 * 
 * Setting up a secure and reliable Memcached installation requires *EXTREME TECHNICAL SKILL*. Errors
 * in your firewall and Memcached daemon configurations can expose FoxFire's crypto keys to the
 * outside world, letting hackers p3wn your install. Using Memcached on a shared server can let other
 * sites read and/or modify your cache keys, letting hackers p3wn your install.
 * 
 * *DO NOT ACTIVATE THE MEMCACHED DRIVER* unless you know *EXACTLY* what you're doing.
 * 
 * @see http://php.net/manual/en/memcached.construct.php
 * @see http://www.slideshare.net/sensepost/cache-on-delivery
 * 
 * ##################################################################################################### 
 * 
 * === ACTIVATION PROCEDURE ===
 * 
 * 1) Ensure your PHP installation has the "Memcached" extension installed
 * 
 * 2) Set up one or more Memcached daemons on your server cluster
 * 
 * 3) Properly secure the Memcached daemons
 * 
 * 4) Add the following line to your wp-config.php file:
 * 
 *	    define('FOX_MEMCACHED_ENABLE', true);
 * 
 * 5) Add the following construct to your wp-config.php file:
 * 
 *	    define('FOX_MEMCACHED_HOSTS', array(
 * 
 *		    array('localhost', 11211),
 *		    array('192.168.1.69', 698),
 *  		    array('ip_address', port_number)
 *	    ));
 * 
 * 6) Restart the FoxFire plant. FoxFire CANNOT and DOES NOT handle on-the-fly changes to caching
 *    resources because checking for this would significantly decrease cache performance. You must
 *    cycle the plant EVERY time you make changes to your cache infrastructure.
 *
 * =====================================================================================================
 */


/**
 * FOXFIRE MEMORY CACHE - MEMCACHE
 * Stores keys to to the Memcached caching system  @link http://memcached.org providing *data center*
 * level persistent caching that *does not survive reboots* of the the server(s) running the cache daemon.
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Cache Memcache
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_mCache_driver_memcached extends FOX_mCache_driver_base {


	var $enable = true;		    // True to enable the driver. False to disable it.
	
	var $is_active;			    // If true, the driver is active
	
	var $has_libs;			    // True if the PHP installation has the libs needed to connect to
					    // a Memcached installation
	
	var $can_connect;		    // True if the driver can connect to the Memcached installation	
	
	var $process_id;		    // Unique process id for this thread. Used for namespace-level locking.
	
	var $max_offset;		    // Value at which cache offset rolls over to zero.		
	
	var $engine;			    // Cache engine instance
	
	var $use_full = true;		    // Use 'full' mode
	var $use_basic = true;		    // Use 'basic' mode
	var $use_portable = false;	    // Use 'portable' mode
	
	var $mode;			    // Driver operating mode ("full", "basic", "portable")
	
	var $servers;			    // Memcached servers to connect to
	var $compress_threshold;	    // Data compression threshold
	
	var $flush_propagation_delay;	    // Propagation delay for a FLUSH_ALL command to reach all nodes
					    // in memcached server cluster
	
	var $set_propagation_delay;	    // Propagation delay for a SET command to reach all nodes
					    // in memcached server cluster	
		
	
	// ================================================================================================================
	
	
	public function __construct($args=null){

	    
		// Handle dependency-injection 
		// ===========================================================
	    
		// NOTE: Memcached's flush() has one-second granularity. It only affects items
		// set a minimum of 1 second before it, and it could affect items set for up to
		// one second after it. This it probably in Memcached's implementation to handle
		// network latency across multiple servers, and would never be a problem in production
		// because the only time a site would dump the *entire* cache is on a server reboot.
		// @see http://ca2.php.net/manual/en/memcache.flush.php

		// However, its a HUGE issue during unit testing when the cache has to be flushed 
		// between every test fixture ...potentially hundreds of times a second, and the unit
		// tests are usually run against a SINGLE memcached instance. As such, the unit tests
		// usually set this value to almost zero. If you experience problems with the memcached
		// test fixture on your system, you may need to increase the 'flush_propagation_delay'
		// parameter
	    
	    
		$args_default = array(
			'max_offset' => 2147483646,  // 32-BIT MAXINT
			'servers' => array(   
					    array('ip'=>'127.0.0.1', 'port'=>11211, 'persist'=>false, 'weight'=>100) 
			),
			'compress_threshold' => 0.2,
			'flush_propagation_delay' => 1200000,
			'set_propagation_delay' => 0,		    
		);

		$args = FOX_sUtil::parseArgs($args, $args_default);
		
		foreach($args as $key => $var){
		    
			$this->{$key} = $var;		    
		}
		unset($key, $var);
		
		
		// Handle process-id binding
		// ===========================================================
		
		if(FOX_sUtil::keyExists('process_id', $args)){
		    
			// Binding to a reference is important. It makes the cache engine $process_id
			// update if the FOX_mCache is changed, which we do during unit testing.
		    
			$this->process_id = &$args['process_id'];			
		}
		else {	
			global $fox;
			$this->process_id = $fox->process_id;
		}	
		

		// CASE 1: Try for the "Memcached" extension, which is the fastest and
		// has the most advanced features.
		// =======================================================================
		
		$this->has_libs = false;
		
		if( class_exists("Memcached") && ($this->use_full == true) ){

			$this->has_libs = true;
			
			$this->engine = new Memcached();

			$active_servers = $this->engine->getServerList();				

			if( count($active_servers) == 0 ){

				$formatted_servers = array();

				foreach( $this->servers as $server){

					$temp = array( $server['ip'], $server['port'], $server['weight'] );
					$formatted_servers[] = $temp;
				}
				unset($server);

				$this->engine->addServers($formatted_servers);
			}


			$this->engine->setCompressThreshold($this->compress_threshold);

			$this->mode = 'full';
			
			if($this->enable == true){
			    
				$this->is_active = true;
			}

		}

		// CASE 2: Try for the "Memcache" extension, which has limited features, 
		// but is more common.
		// =======================================================================			
		elseif( class_exists("Memcache") && ($this->use_basic == true) ){

			$this->has_libs = true;
			
			$this->engine = new Memcache();

			foreach($this->servers as $server){

				$this->engine->addServer($server['ip'], $server['port'], $server['persist']);
			}
			unset($server);

			$this->engine->setCompressThreshold($this->compress_threshold);

			$this->mode = 'basic';	
			
			if($this->enable == true){
			    
				$this->is_active = true;
			}

		}
		// CASE 3: Finally, use our portable Memcached library. Which is what
		// 80% of Linux installs and 99% of Mac and Windows installs will end up
		// using ...due to problems with PECL libs and missing Windows DLL's.
		// =======================================================================			
		elseif ($this->use_portable == true) {
		    
			require ( dirname( __FILE__ ) . '/class.portable.memcached.php' );
			
			$this->has_libs = true;

			$this->engine = new FOX_memcached_portable();

			$this->mode = 'portable';
			
			if($this->enable == true){
			    
				$this->is_active = true;
			}				

		}
			
		
	}
	
	
	/**
	 * Enables the cache driver
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	
	public function enable(){
			   	    
		$this->enable = true;	
		
		if($this->has_libs == true){

			$this->is_active = true;
		}				
	}
	
	
	/**
	 * Disables the cache driver
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	
	public function disable(){
			    
		$this->enable = false;	    
		$this->is_active = false;			
	}	
	
	
	/**
	 * Checks if the cache engine driver is active
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | True if active. False if disabled.
	 */

	public function isActive(){

		return $this->is_active;
	}


	/**
	 * Returns the current performance stats of the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getStats(){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		else {

			$result = $this->engine->getStats();
		}

		
		return $result;
		
	}


	/**
	 * Removes all entries in the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushAll(){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		else {
		    
			// NOTE: Memcached's flush() has one-second granularity. It only affects items
			// set a minimum of 1 second before it, and it could affect items set for up to
			// one second after it. This it probably in Memcached's implementation to handle
			// network latency across multiple servers, and would never be a problem in production
			// because the only time a site would dump the *entire* cache is on a server reboot.
			// @see http://ca2.php.net/manual/en/memcache.flush.php
		   
		    
			usleep($this->flush_propagation_delay);
		    
			$this->engine->flush();	
			
			usleep($this->flush_propagation_delay);			
		}
		
		return true;
		
	}

	
	/**
	 * Locks an entire namespace within the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $namespace | Class namespace
	 * @param int $seconds |  Time in seconds from present time until lock expires	  
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function lockNamespace($ns, $seconds){

		// @see http://en.wikipedia.org/wiki/Dekker%27s_algorithm
	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		else {

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>$ns,			    
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));		    		
			}
			
			try {
				$offset = self::getOffset($ns);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
						
			
			
			if($offset != -1){
			    
				$already_locked = false;
			}
			else {
			    
				// If the namespace is *already* locked, fetch the lock info array
				// ================================================================
			    
			    	$already_locked = true;
				
				$lock = $this->engine->get("fox.ns_lock.".$ns);	
				
				// NOTE: data structures stored to memcached must be serialized				
				$lock = unserialize($lock);
				
				// If the lock is owned by the current PID, just write back the lock array to the cache
				// with an updated timestamp, refreshing the lock. This provides important functionality,
				// letting a PID that has a lock on the namespace extend its lock time incrementally as it
				// works through a complex processing job. If the PID had to release and reset the lock
				// each time, the data would be venerable to being overwritten by other PID's.
				
				if( $lock['pid'] == $this->process_id ){
				    
					$offset = $lock['offset'];				    				    
				}								 
				else {

					throw new FOX_exception(array(
						'numeric'=>4,
						'text'=>"Namespace is already locked",
						'data'=>array('ns'=>$ns, 'lock'=>$lock),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));					    
				}
				
			}
			 
			$lock_array = array( 
					    'pid'=>$this->process_id, 
					    'expire'=>( microtime(true) + $seconds ),
					    'offset'=>$offset
			);
			
			// NOTE: data structures stored to memcached must be serialized	
			$lock_array = serialize($lock_array);
			
			
			// If the cache engine can operate in 'full' mode, do both
			// writes as a single operation to guarantee atomicity
			// ==============================================================
			
			if( $this->mode == 'full' ){
			    
				$keys = array(			    
						"fox.ns_lock.".$ns => $lock_array
				);

				if(!$already_locked){

					$keys["fox.ns_offset.".$ns] = -1;
				}			    
			    
				$set_ok = $this->engine->setMulti($keys, $expire=0);
				
				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Error writing to cache in 'full' mode",
						'data'=>array('keys'=>$keys, 'set_ok'=>$set_ok),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}				
			}
			
			// If the cache engine can only operate in 'basic' mode, write the lock array first, then
			// the offset. That way, if the lock write fails we can abort, and if the offset write
			// fails, debug info will be present in the lock array if we want it, and it will be
			// harmlessly overwritten the next time a PID tries to lock the namespace
			// ==============================================================	
			
			else {
			    			    
				$set_ok = $this->engine->set("fox.ns_lock.".$ns, $lock_array, false, $expire=0);
			    
				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>6,
						'text'=>"Error writing lock array to cache in 'basic' mode",
						'data'=>array('key'=>"fox.ns_lock.".$ns, 'val'=>$lock_array),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}
				
				if(!$already_locked){

					$set_ok = $this->engine->set("fox.ns_offset.".$ns, -1, false, $expire=0);

					if(!$set_ok){

						throw new FOX_exception(array(
							'numeric'=>7,
							'text'=>"Error writing namespace offset to cache in 'basic' mode",
							'data'=>array('key'=>"fox.ns_offset.".$ns, 'val'=>-1),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}					
				}					
			    
			}		
						
		}

		
		return $offset;

	}
	
	
	/**
	 * Unlocks a locked namespace within the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $namespace | Class namespace	  
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function unlockNamespace($ns){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		else {

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>$ns,			    
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));		    		
			}
			
			try {
				$offset = self::getOffset($ns);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
						
			// Only try to load the lock array if the namespace is actually locked
			// ================================================================			
			
			if($offset == -1){
			    			    
				$lock = $this->engine->get("fox.ns_lock.".$ns);	
				
				// NOTE: data structures stored to memcached must be serialized				
				$lock = unserialize($lock);
				
				// If the lock is being released by the PID that set it,
				// restore the offset to the saved value. If its being unlocked
				// by a foreign PID, increment it by 1 to flush the namespace
				
				if( $lock['pid'] == $this->process_id ){
				    
					$offset = $lock['offset'];				    				    
				}								 
				else {												
					$offset = $lock['offset'] + 1;
				}
				
				
				// If the cache engine can operate in 'full' mode, do both
				// writes as a single operation to guarantee atomicity
				// ==============================================================

				if( $this->mode == 'full' ){

					$keys = array(			    
							"fox.ns_lock.".$ns => false,
							"fox.ns_offset.".$ns => $offset			    
					);			    

					$set_ok = $this->engine->setMulti($keys, $expire=0);

					if(!$set_ok){

						throw new FOX_exception(array(
							'numeric'=>5,
							'text'=>"Error writing to cache in 'full' mode",
							'data'=>array('keys'=>$keys, 'set_ok'=>$set_ok),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}				
				}

				// If the cache engine can only operate in 'basic' mode, write the offset array first, 
				// then clear the lock array. That way, if the offset write fails we can abort, and
				// debug info will be present in the lock array if we want it.
				// ==============================================================	

				else {

					$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, false, $expire=0);

					if(!$set_ok){

						throw new FOX_exception(array(
							'numeric'=>6,
							'text'=>"Error writing namespace offset to cache in 'basic' mode",
							'data'=>array('key'=>"fox.ns_offset.".$ns, 'val'=>$offset),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}
						
					$set_ok = $this->engine->set("fox.ns_lock.".$ns, false, false, $expire=0);

					if(!$set_ok){

						throw new FOX_exception(array(
							'numeric'=>7,
							'text'=>"Error clearing lock array from cache in 'basic' mode",
							'data'=>array('key'=>"fox.ns_lock.".$ns, 'val'=>false),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}					

				}								
				
			}
						
		}
		
		return $offset;

	}
	
	

	/**
	 * Removes all entries within the specified namespace from the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @return int | Exception on failure. Int offset on success.
	 */

	public function flushNamespace($ns){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		try {
			$offset = self::getOffset($ns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error in self::getOffset()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		// If the namespace is currently locked, recover the offset
		// from the lock array 
		// ==============================================================
		
		$namespace_locked = false;

		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);	

			// NOTE: data structures stored to memcached must be serialized				
			$lock = unserialize($lock);	

			$offset = $lock['offset'];
			$namespace_locked = true;
		}

		if($offset < $this->max_offset){   

			$offset++;
		}
		else {
			$offset = 1;
		}	


		// If the cache engine can operate in 'full' mode, do both
		// writes as a single operation to guarantee atomicity
		// ==============================================================

		if( $this->mode == 'full' ){

			$keys = array("fox.ns_offset.".$ns => $offset);

			// Clear lock array, if needed
			// ==================================

			if($namespace_locked){

				$keys["fox.ns_lock.".$ns] = false;					
			}	

			$set_ok = $this->engine->setMulti($processed, $expire);

			if(!$set_ok){

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Error writing to cache in 'full' mode",
					'data'=>array('keys'=>$keys, 'set_ok'=>$set_ok),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}				
		}

		// If the cache engine can only operate in 'basic' mode, clear the lock array
		// first, then unlock the namespace. That way, we can re-try the write if it  
		// fails, while  still having the namespace protected
		// ==============================================================

		else {

			// Clear lock array, if needed
			// ==================================

			if($namespace_locked){

				$set_ok = $this->engine->set("fox.ns_lock.".$ns, false, false, $expire=0);

				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Error clearing lock array from cache in 'basic' mode",
						'data'=>array('key'=>"fox.ns_lock.".$ns, 'val'=>false),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}

			}

			// Set the offset
			// ==================================
			
			$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, false, $expire=0);

			if(!$set_ok){

				throw new FOX_exception(array(
					'numeric'=>6,
					'text'=>"Error writing namespace offset to cache in 'basic' mode",
					'data'=>array('key'=>"fox.ns_offset.".$ns, 'val'=>$offset),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}				

		}
		
		
		return $offset;

	}


	/**
	 * Gets the offset for a cache namespace.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @return bool | Exception on failure. Int offset on success.
	 */

	public function getOffset($ns){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		else {
			
			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));		    		
			}
			
			$offset = $this->engine->get("fox.ns_offset.".$ns);			

			// If there is no offset key for the namespace present in 
			// the cache, create one
			
			if($offset == false){

				$offset = 1;
				$expire = 0;
			
				$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, $expire);				

				if(!$set_ok){
				    
					throw new FOX_exception(array(
						'numeric'=>3,
						'text'=>"Error writing to cache",
						'data'=>array('namespace'=>$ns, 'offset'=>$offset, 'expire'=>$expire),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}

			}											
		}
		
		return $offset;

	}


	/**
	 * Stores a value into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param mixed $val | Value to assign
	 * @param int $check_offset | Offset to check against
	 * @param bool $clear_lock | True to clear a namespace lock, if the PID owns it	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function set($ns, $var, $val, $check_offset=null, $clear_lock=false){

			
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		else {			
			
			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));		    		
			}
			
			try {
				$offset = self::getOffset($ns);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}								

			$namespace_locked = false;
			
			if($offset == -1){
			    			    
				$lock = $this->engine->get("fox.ns_lock.".$ns);	
				
				// NOTE: data structures stored to memcached must be serialized				
				$lock = unserialize($lock);			
								
				// If the lock is owned by the current PID, set the $offset variable to
				// the value stored in the $lock array. This makes the keys valid when
				// the lock is released by the the process that set it, but causes them
				// to be flushed if the lock expires.
				
				if( $lock['pid'] == $this->process_id ){
				    
					$offset = $lock['offset'];
					$namespace_locked = true;					
				}								 
				else {
				    							
					throw new FOX_exception(array(
						'numeric'=>4,
						'text'=>"Namespace is currently locked",
						'data'=>array('ns'=>$ns, 'lock'=>$lock),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));	
				}						
				
			}

			// Check the current offset matches the expected offset

			if( ($check_offset !== null) && ($check_offset != $offset) ){

				throw new FOX_exception(array(
					'numeric'=>5,
					'text'=>"Current offset doesn't match expected offset",
					'data'=>array('current_offset'=>$offset, 'expected_offset'=>$check_offset),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));				    				    
			}

			// Neither of PHP's memcache libraries understands the difference
			// between (bool)true, (int)1, and (float)1. So we have to serialize
			// EVERY piece of data we send to the cache. 
			    
			$sval = serialize($val);
			
				
			// If the cache engine can operate in 'full' mode, do both
			// writes as a single operation to guarantee atomicity
			// ==============================================================
			
			if( $this->mode == 'full' ){
			    							    
				$keys = array(			    
						"fox." . $ns . "." . $offset . "." . $var => $sval		    
				);

				if( $namespace_locked && $clear_lock ){

					$keys["fox.ns_offset.".$ns] = $offset;
					$keys["fox.ns_lock.".$ns] = false;					
				}			    
			    
				$set_ok = $this->engine->setMulti($keys, $expire=0);
				
				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Error writing to cache in 'full' mode",
						'data'=>array('keys'=>$keys, 'set_ok'=>$set_ok),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}				
			}
			
			// If the cache engine can only operate in 'basic' mode, write the data first, then
			// unlock the namespace. That way, we can re-try the write if it fails, while
			// still having the namespace protected
			// ==============================================================	
			
			else {
			    	
				// Write data key
				// ==================================
			    
				$keyname = "fox." . $ns . "." . $offset . "." . $var;
				
				$set_ok = $this->engine->set($keyname, $sval, false, $expire=0);
			    
				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>6,
						'text'=>"Error writing data key to cache in 'basic' mode",
						'data'=>array('key'=>$keyname, 'sval'=>$sval),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}
				
				
				// Clear lock
				// ==================================
				
				if( $namespace_locked && $clear_lock ){

					$set_ok = $this->engine->set("fox.ns_lock.".$ns, false, false, $expire=0);

					if(!$set_ok){

						throw new FOX_exception(array(
							'numeric'=>7,
							'text'=>"Error clearing lock array from cache in 'basic' mode",
							'data'=>array('key'=>"fox.ns_lock.".$ns, 'val'=>false),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}
					
					$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, false, $expire=0);
					
					if(!$set_ok){

						throw new FOX_exception(array(
							'numeric'=>8,
							'text'=>"Error writing namespace offset to cache in 'basic' mode",
							'data'=>array('key'=>"fox.ns_offset.".$ns, 'val'=>$offset),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));
					}			    
				}						
			    
			}		
			
		}
		
		return true;
		
	}


	/**
	 * Stores multiple values into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Data to set in the form "key"=>"val"
	 * @param int $check_offset | Offset to check against
	 * @param bool $clear_lock | True to clear a namespace lock, if the PID owns it	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setMulti($ns, $data, $check_offset=null, $clear_lock=false){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}

		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		
		try {
			$offset = self::getOffset($ns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error in self::getOffset()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		$processed = array();
		$namespace_locked = false;

		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);	

			// NOTE: data structures stored to memcached must be serialized				
			$lock = unserialize($lock);			

			// If the lock is owned by the current PID, set the $offset variable to
			// the value stored in the $lock array. This makes the keys valid when
			// the lock is released by the the process that set it, but causes them
			// to be flushed if the lock expires.

			if( $lock['pid'] == $this->process_id ){

				$offset = $lock['offset'];
				$namespace_locked = true;					
			}								 
			else {

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Namespace is currently locked",
					'data'=>array('ns'=>$ns, 'lock'=>$lock),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));	
			}	

		}			

		// Check the current offset matches the expected offset

		if( ($check_offset !== null) && ($check_offset != $offset) ){

			throw new FOX_exception(array(
				'numeric'=>5,
				'text'=>"Current offset doesn't match expected offset",
				'data'=>array('current_offset'=>$offset, 'expected_offset'=>$check_offset),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));				    				    
		}

		$processed = array();

		// Add namespace prefix to each keyname

		foreach($data as $key => $val){

			// Neither of PHP's memcache libraries understands the difference
			// between (bool)true, (int)1, and (float)1. So we have to serialize
			// EVERY piece of data we send to the cache. 

			$sval = serialize($val);

			$processed["fox." . $ns . "." . $offset . "." . $key] = $sval;							
		}
		unset($key, $val);

		$expire = 0;

		// If the cache engine can operate in 'full' mode, do both
		// writes as a single operation to guarantee atomicity
		// ==============================================================

		if( $this->mode == 'full' ){

			// Clear lock
			// ==================================

			if( $namespace_locked && $clear_lock ){

				$processed["fox.ns_offset.".$ns] = $offset;
				$processed["fox.ns_lock.".$ns] = false;					
			}	

			// WARNING: it appears the setMulti() function isn't truly atomic, so we're
			// going to have to come up with an alternative for this functionality
			// 
			// @see http://ca1.php.net/manual/en/memcached.setmulti.php
			
			$set_ok = $this->engine->setMulti($processed, $expire);

			if(!$set_ok){

				throw new FOX_exception(array(
					'numeric'=>6,
					'text'=>"Error writing to cache in 'full' mode",
					'data'=>array('processed'=>$processed, 'expire'=>$expire, 'set_ok'=>$set_ok),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}				
		}

		// If the cache engine can only operate in 'basic' mode, write the data first, 
		// then unlock the namespace. That way, we can re-try the write if it fails, 
		// while  still having the namespace protected
		// ==============================================================

		else {

			// Write data keys
			// ==================================

			$key_count = 0;
			$failed_keys = array();

			foreach( $processed as $key => $val){

				$set_ok = $this->engine->set($key, $val, false, $expire);

				if($set_ok){
					$key_count++;
				}
				else {
					$failed_keys[] = array('key'=>$key, 'val'=>$val, 'expire'=>$expire);
				}
			}
			unset($key, $val);

			if( $key_count != count($processed) ){

				throw new FOX_exception(array(
					'numeric'=>7,
					'text'=>"Error writing to cache in 'basic' mode",
					'data'=>array('processed'=>$processed, 'failed_keys'=>$failed_keys),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}


			// Clear lock
			// ==================================

			if( $namespace_locked && $clear_lock ){

				$set_ok = $this->engine->set("fox.ns_lock.".$ns, false, false, $expire=0);

				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>8,
						'text'=>"Error clearing lock array from cache in 'basic' mode",
						'data'=>array('key'=>"fox.ns_lock.".$ns, 'val'=>false),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}

				$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, false, $expire=0);

				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>9,
						'text'=>"Error writing namespace offset to cache in 'basic' mode",
						'data'=>array('key'=>"fox.ns_offset.".$ns, 'val'=>$offset),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}			    
			}				

		}
							
		return true;

	}


	/**
	 * Retrieves a value from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param bool &$valid | True if key exists in cache. False if not.
	 * 
	 * @return int &$offset | Current namespace offset
	 * @return mixed | Exception on failure. Stored data item on success.
	 */

	public function get($ns, $var, &$valid=null, &$offset=null){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		try {
			$offset = self::getOffset($ns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error in self::getOffset()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}											


		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);	

			// NOTE: data structures stored to memcached must be serialized				
			$lock = unserialize($lock);				

			// If the lock is owned by the current PID, set the $offset variable to
			// the value stored in the $lock array. This lets the process that owns
			// the lock read from the cache.

			if( $lock['pid'] == $this->process_id ){

				$offset = $lock['offset'];				    				    
			}								 
			else {	
				$offset = null;	    // Prevent PHP from setting the $offset
						    // variable if we're in an error state

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Namespace is currently locked",
					'data'=>array('ns'=>$ns, 'lock'=>$lock),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					    
			}

		}

		$key = "fox." . $ns . "." . $offset . "." . $var;

		if( $this->mode == 'full' ){

			$result = $this->engine->get($key);	

			// Check if the key is valid

			if( $this->engine->getResultCode() == Memcached::RES_NOTSTORED ){

				$valid = false;
			}
			else {
				$valid = true;
			}			
		}
		else {

			// The Memcache class doesn't have a "key valid" flag. To check if
			// the key is valid, we request the key using the get() method's
			// array format, and if the key is missing from the results array,
			// we know its not a valid key

			$cache_result = $this->engine->get( array($key) );	

			if( FOX_sUtil::keyExists($key, $cache_result)){

				$valid = true;
				$result = $cache_result[$key];
			}
			else {
				$valid = false;
				$result = null;
			}			    			    			    
		}
		
		// Neither of PHP's memcache libraries understands the difference
		// between (bool)true, (int)1, and (float)1. So we have to serialize
		// EVERY piece of data we send to the cache. 

		$result = unserialize($result);			
			

		return $result;

	}


	/**
	 * Retrieves multiple values from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $names | Array of cache variable names
	 * @param int &$offset | Current namespace offset
	 * 	 
	 * @return mixed | Exception on failure. Stored data item on success.
	 */

	public function getMulti($ns, $names, &$offset=null){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		try {
			$offset = self::getOffset($ns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error in self::getOffset()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}


		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);	

			// NOTE: data structures stored to memcached must be serialized				
			$lock = unserialize($lock);				

			// If the lock is owned by the current PID, set the $offset variable to
			// the value stored in the $lock array. This lets the process that owns
			// the lock read from the cache.

			if( $lock['pid'] == $this->process_id ){

				$offset = $lock['offset'];				    				    
			}								 
			else {				
				$offset = null;	    // Prevent PHP from setting the $offset
						    // variable if we're in an error state

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Namespace is currently locked",
					'data'=>array('ns'=>$ns, 'lock'=>$lock),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					    
			}

		}			

		// Add namespace prefix to each keyname
		foreach($names as $key){

			$processed[] = "fox." . $ns . "." . $offset . "." . $key;
		}
		unset($key);

		if( $this->mode == 'full'){

			// PHP's 'memcached' class uses the getMulti() 
			// method for fetching multiple keys at once
			$cache_result = $this->engine->getMulti($processed);			
		}
		else {
			// PHP's 'memcache' class supports passing an 
			// array of keys to the get() method			    
			$cache_result = $this->engine->get($processed);
		}

		$result = array();

		foreach($names as $key){

			// BEFORE: "namespace.offset.keyname"=>"value"
			// AFTER:  "keyname"=>"value"

			$prefixed_name = "fox." . $ns . "." . $offset . "." . $key;

			// This prevents the loop from creating keys
			// in the $result array if they don't exist in
			// the $cache_result array

			if( array_key_exists($prefixed_name, $cache_result) ){	    

				// Neither of PHP's memcache libraries understands the difference
				// between (bool)true, (int)1, and (float)1. So we have to serialize
				// EVERY piece of data we send to the cache. This increases cache
				// value memory usage by 400% for small items, and CPU usage by
				// at least an order of magnitude when fetching large items. 

				$skey = $cache_result[$prefixed_name];

				$result[$key] = unserialize($skey);	    
			}

		}
		unset($key);						
		
		return $result;	

	}


	/**
	 * Deletes an item from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of key
	 * @param int $check_offset | Offset to check against	 
	 * 
	 * @return bool | Exception on failure. True on key exists. False on key doesn't exist.
	 */

	public function del($ns, $var, $check_offset=null){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		try {
			$offset = self::getOffset($ns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error in self::getOffset()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);	

			// NOTE: data structures stored to memcached must be serialized				
			$lock = unserialize($lock);				

			// If the lock is owned by the current PID, set the $offset variable to
			// the value stored in the $lock array. This lets the process that owns
			// the lock delete from the cache.

			if( $lock['pid'] == $this->process_id ){

				$offset = $lock['offset'];					
			}								 
			else {				

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Namespace is currently locked",
					'data'=>array('ns'=>$ns, 'lock'=>$lock),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					    
			}

		}

		// Check the current offset matches the expected offset

		if( ($check_offset !== null) && ($check_offset != $offset) ){

			throw new FOX_exception(array(
				'numeric'=>5,
				'text'=>"Current offset doesn't match expected offset",
				'data'=>array('current_offset'=>$offset, 'expected_offset'=>$check_offset),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));				    				    
		}			

		
		$key = "fox." . $ns . "." . $offset . "." . $var;
		$delete_ok = $this->engine->delete($key);
		
		return $delete_ok;
		
	}


	/**
	 * Deletes multiple items from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Key names as array of strings.
	 * @param int $check_offset | Offset to check against	 
	 * 
	 * @return int | Exception on failure. Int number of keys deleted on success.
	 */

	public function delMulti($ns, $data, $check_offset=null){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		try {
			$offset = self::getOffset($ns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error in self::getOffset()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);	

			// NOTE: data structures stored to memcached must be serialized				
			$lock = unserialize($lock);				

			// If the lock is owned by the current PID, set the $offset variable to
			// the value stored in the $lock array. This lets the process that owns
			// the lock delete from the cache.

			if( $lock['pid'] == $this->process_id ){

				$offset = $lock['offset'];					
			}								 
			else {					

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Namespace is currently locked",
					'data'=>array('ns'=>$ns, 'lock'=>$lock),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					    
			}

		}			

		// Check the current offset matches the expected offset

		if( ($check_offset !== null) && ($check_offset != $offset) ){

			throw new FOX_exception(array(
				'numeric'=>5,
				'text'=>"Current offset doesn't match expected offset",
				'data'=>array('current_offset'=>$offset, 'expected_offset'=>$check_offset),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));				    				    
		}

		$processed = array();

		// Add namespace prefix to each keyname

		foreach($data as $val){

			$processed[] =  "fox." . $ns . "." . $offset . "." . $val ;
		}
		unset($val);		
		

		// Memcached doesn't have a multi-delete feature, which means we can't
		// make multi-delete operations atomic. 

		$key_count = 0;

		foreach( $processed as $key ){

			// If the key existed in the cache, the memcache class will return
			// true. Otherwise it will return false.

			$key_existed = $this->engine->delete($key);

			if($key_existed){
				$key_count++;
			}
		}		
		
		
		return $key_count;

	}
	
	

} // End of class FOX_mCache_driver_memcache


?>