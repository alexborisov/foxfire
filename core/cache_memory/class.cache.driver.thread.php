<?php

/**
 * FOXFIRE MEMORY CACHE - THREAD DRIVER
 * Stores keys to a thread's process memory, allowing cache objects to be shared between multiple class 
 * instances within a single thread.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Cache Thread
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_mCache_driver_thread extends FOX_mCache_driver_base {


	var $enable = true;		    // True to enable the driver. False to disable it.
	
	var $is_active;			    // If true, the driver is active
	
	var $process_id;		    // Unique process id for this thread. Used for namespace-level locking.
	
	var $max_offset;		    // Value at which cache offset rolls over to zero.	
	
	var $cache;			    // Cache data array
	
	var $max_keys;			    // The maximum number of keys to store. Setting this paramater to high 
					    // values will often result in extremely poor performance, because once
					    // full, the cache array has to be re-sorted after every write operation.
	
	
	// ================================================================================================================


	public function __construct($args=null){

	    
		// Handle dependency-injection for unit tests
	    
		if(FOX_sUtil::keyExists('process_id', $args)){
		    
			$this->process_id = $args['process_id'];
		}
		else {	
			global $fox;
			$this->process_id = $fox->process_id;
		}
		
		if(FOX_sUtil::keyExists('max_keys', $args)){
		    
			$this->max_keys = $args['max_keys'];
		}
		else {	
			$this->max_keys = 1000;
		}
		
		if(FOX_sUtil::keyExists('max_offset', $args)){
		    
			$this->max_offset = $args['max_offset'];
		}
		else {	
			$this->max_offset = 2147483646;  // (32-bit maxint)
		}
				
		$this->cache = array();				
		
		if($this->enable == true){

			$this->is_active = true;
		}	
		else {		    
			$this->is_active = false;		    
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
		$this->is_active = true;		
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
	 * @return bool | True if active. False if not.
	 */

	public function isActive(){
		
		return true;
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

		$this->cache = array();	
		
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

			$lock = self::fetch("fox.ns_lock.".$ns);	

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

		$keys = array(			    
				"fox.ns_lock.".$ns => array( 
							    'pid'=>$this->process_id, 
							    'expire'=>( microtime(true) + $seconds ),
							    'offset'=>$offset
				)			    
		);

		if(!$already_locked){

			$keys["fox.ns_offset.".$ns] = -1;
		}

		self::storeMulti($keys);
								
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

			$lock = self::fetch("fox.ns_lock.".$ns);	

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

			self::storeMulti($keys);						

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

		// If the namespace is currently locked, recover the offset from the lock array

		$namespace_locked = false;

		if($offset == -1){

			$lock = self::fetch("fox.ns_lock.".$ns);				
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
				"fox.ns_offset.".$ns => $offset			    
		);

		if($namespace_locked == true){

			$keys["fox.ns_lock.".$ns] = false;			    
		}

		self::storeMulti($keys);
									
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

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		$offset = self::fetch("fox.ns_offset.".$ns);

		// If there is no offset key for the namespace present in the cache, create one
		if(!$offset){

			$offset = 1;
			self::store("fox.ns_offset.".$ns, $offset);
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
			    			    
				$lock = self::fetch("fox.ns_lock.".$ns);	
								
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
					"fox." . $ns . "." . $offset . "." . $var => $val		    
			);

			if( $namespace_locked && $clear_lock ){

				$keys["fox.ns_offset.".$ns] = $offset;	
				$keys["fox.ns_lock.".$ns] = false;
			}

			self::storeMulti($keys);				
						
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
			
			$processed = array();
			$namespace_locked = false;
			
			if($offset == -1){
			    			    
				$lock = self::fetch("fox.ns_lock.".$ns);	

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
				
			// Add namespace prefix to each keyname
			
			foreach($data as $key => $val){

				$processed["fox." . $ns . "." . $offset . "." . $key] = $val;
			}
			unset($key, $val);

			
			if( $namespace_locked && $clear_lock ){

				$processed["fox.ns_offset.".$ns] = $offset;
				$processed["fox.ns_lock.".$ns] = false;					
			}			

			self::storeMulti($processed);
			
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
			
			
			if($offset == -1){
			    			    
				$lock = self::fetch("fox.ns_lock.".$ns);			
								
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
			
			$result = self::fetch($key, $valid);			
			
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

			$lock = self::fetch("fox.ns_lock.".$ns);			

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

		$cache_result = self::fetchMulti($processed);

		// self::fetchMulti() will return an array of the form "keyname"=>"value". If a key doesn't 
		// exist in the cache, the requested key will not be present in the results array.

		$result = array();

		foreach($names as $key){

			// BEFORE: "namespace.offset.keyname"=>"value"
			// AFTER:  "keyname"=>"value"

			$prefixed_name = "fox." . $ns . "." . $offset . "." . $key;

			if( array_key_exists($prefixed_name, $cache_result) ){	    // This prevents the loop from creating keys
										    // in the $result array if they don't exist in
				$result[$key] = $cache_result[$prefixed_name];	    // the cache result array
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

			$lock = self::fetch("fox.ns_lock.".$ns);			

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
		$delete_ok = self::delete($key);

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

			$lock = self::fetch("fox.ns_lock.".$ns);			

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

		$key_count = 0;

		// Add namespace prefix to each keyname
		foreach($data as $val){

			$key =  "fox." . $ns . "." . $offset . "." . $val ;

			$del_result = self::delete($key);

			if($del_result == true){

				$key_count++;			    			    
			}						
		}
		unset($val);			

		
		return $key_count;		


	}
	


	// =============================================================================================
	
	
	/**
	 * Writes a single key to the cache array
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function store($key, $value){
		
		return self::storeMulti( array( $key => $value) );		
	}
	
	
	/**
	 * Writes a multiple keys to the cache array
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function storeMulti($data){
	   
		// Implemented this way so that all keys in a transaction 
		// have the same timestamp
	    
		$current_time = microtime(true);
		
		foreach( $data as $key => $value ){
		    
			$this->cache[$key] = array( 'value'=>$value, 'timestamp'=>$current_time );			
		}
		unset($key, $value);
		
		self::trim();
		
		return true;	
	}	
	
	
	/**
	 * Reads a key from the cache array
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function fetch($key, &$valid=null){
			    
		if( FOX_sUtil::keyExists($key, $this->cache) ){

			$valid = true;		    
			$result = $this->cache[$key]['value'];		
			$this->cache[$key]['timestamp'] = microtime(true);
			
		}
		else {
			$valid = false;		    
			$result = null;
		}
			
		return $result;		
	}
	
	
	/**
	 * Reads a key from the cache array
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function fetchMulti($keys){
			    
		// Implemented this way so that all keys in a transaction 
		// have the same timestamp
	    
		$current_time = microtime(true);
		$result = array();
		
		foreach( $keys as $key ){
		    
			if( FOX_sUtil::keyExists($key, $this->cache) ){
			    
				$this->cache[$key]['timestamp'] = $current_time;
				$result[$key] = $this->cache[$key]['value'];
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
	 * @param string $key | Name of key
	 * @return bool | False on failure. True on success.
	 */

	public function delete($key){

		if( FOX_sUtil::keyExists($key, $this->cache) ){

			unset($this->cache[$key]);
			return true;
		}
		else {
			return false;
		}		
		
	}	
	
	
	/**
	 * Removes least recently used entries from the cache array
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function trim(){

		$key_count = count($this->cache);

		if($key_count > $this->max_keys){

			// Create a $key_id => $timestamp index array
			// for array_multisort() to use
		    
			$last_used = array();

			foreach( $this->cache as $key => $value ){			

				$last_used[$key] = $value['timestamp'];			
			}
			unset($value);

			// Sort the $cache array using the index array, then slice
			// off the excess keys from the bottom of the cache array
			
			array_multisort($last_used, SORT_DESC, $this->cache);
			
			$this->cache = array_slice($this->cache, 0, ($this->max_keys - 1) );
			
			unset($last_used);
						
		}
		
		return true;
		
	}
	
	

} // End of class FOX_mCache_driver_thread


?>