<?php

/**
 * FOXFIRE MEMORY CACHE - REDIS DRIVER
 * Stores keys to to the Redis caching system @link http://redis.io providing *data center* level
 * caching that *survives reboots*, supports large objects, and has native transaction support
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Cache Redis
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_mCache_driver_redis extends FOX_mCache_driver_base {
	
	
	var $enable = false;		    // True to enable the driver. False to disable it.
	
	var $is_active;			    // If true, the driver is active
	
	var $has_libs;			    // True if the PHP installation has the libs needed to connect to
					    // a Redis installation
	
	var $can_connect;		    // True if the driver can connect to the Redis installation	
	
	var $process_id;		    // Unique process id for this thread. Used for namespace-level locking.
	
	var $max_offset;		    // Value at which cache offset rolls over to zero.	
	
	var $engine;			    // Cache engine instance
	
	var $server;			    // Redis servers to connect to	
	
	
	// ================================================================================================================
	
	
	public function __construct($args=null){
		
	    
		$args_default = array(
			'max_offset' => 2147483646,  // 32-BIT MAXINT
			'server' => array('ip'=>'127.0.0.1', 'port'=>6379, 'database'=>15, 'alias'=>'first')	    
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
			    
	    	$this->has_libs = true;
		
		if($this->enable == true){

			require_once ( FOX_PATH_BASE . '/lib/predis/autoload.php' );

			$this->engine = new Predis\Client($this->server);		    

			$this->is_active = true;
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

			if(!$this->engine){
			    
				$this->__construct();
			}			
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
			$this->engine->flushAll();						
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

		// If the namespace is *already* locked, fetch the lock info array

		if($offset != -1){

			$already_locked = false;
		}
		else {

			$already_locked = true;

			$lock = $this->engine->get("fox.ns_lock.".$ns);
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

		$keys = array(
				"fox.ns_lock.".$ns => serialize($lock_array)
		);
		

		if(!$already_locked){

			$keys["fox.ns_offset.".$ns] = -1;
		}

		$this->engine->mset($keys);
		
		
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

		if($offset == -1){

			$lock = $this->engine->get("fox.ns_lock.".$ns);
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

			$keys = array(
					"fox.ns_lock.".$ns => false,
					"fox.ns_offset.".$ns => $offset
			);

			$this->engine->mset($keys);						

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
						
			// If the namespace is currently locked, recover the offset from the lock array
			
			$namespace_locked = false;
			
			if($offset == -1){
			    			    
				$lock = $this->engine->get("fox.ns_lock.".$ns);
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
			
			$keys = array(
					"fox.ns_offset.".$ns=>$offset			    
			);
						
			if($namespace_locked){
			    
				$keys["fox.ns_lock.".$ns] = false;		    
			}
		
			$this->engine->mset($keys);
			
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
	 * @return bool | False on failure. True on success.
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

			if($offset == false){

				$offset = 1;
				$store_ok = $this->engine->set("fox.ns_offset.".$ns, $offset);

				if(!$store_ok){
				    
					throw new FOX_exception(array(
						'numeric'=>3,
						'text'=>"Error writing to cache engine while setting new offset",
						'data'=>array('namespace'=>$ns, 'offset'=>$offset),
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

		
		$keys = array(		    
				"fox." . $ns . "." . $offset . "." . $var => serialize($val)
		);

		if( $namespace_locked && $clear_lock ){

			$keys["fox.ns_offset.".$ns] = $offset;			    
		}

		$this->engine->mset($keys);				
		
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

		// Clear lock, if required
		// ==================================

		if( $namespace_locked && $clear_lock ){

			$processed["fox.ns_offset.".$ns] = $offset;
			$processed["fox.ns_lock.".$ns] = false;					
		}	
		
		
		$this->engine->mset($processed);				
		
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
		
		$cache_result = $this->engine->get($key);	

		if( $cache_result !== null ){

			$valid = true;
			$result = unserialize($cache_result);
		}
		else {
			$valid = false;
			$result = null;
		}		
					
					
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
		
		$cache_result = $this->engine->mget($processed);

		// The Predis 'mget' operator accepts an array of the form "offset"=>"keyname", 
		// and returns an array of the  form "offset"=>"value". If a requested key doesn't 
		// exist in the cache, the value at its offset in the return array will be will be NULL

		$result = array();

		foreach($names as $key => $name){
		    			
			if( $cache_result[$key] !== null ){	    
										   										   
				$result[$name] = unserialize($cache_result[$key]);			    
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
		
		// The Predis 'del' operator can take input as a string or as an array,
		// and returns the number of items that were successfully deleted
		
		$keys_deleted = $this->engine->del($key);
		
		if($keys_deleted == 1){
		
			return true;
		}
		else {
			return false;
		}
		
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

		// The Predis 'del' operator can take input as a string or as an array,
		// and returns the number of items that were successfully deleted
		
		$keys_deleted = $this->engine->del($processed);		
		
		return $keys_deleted;

		
	}
	


} // End of class FOX_mCache_driver_redis


?>