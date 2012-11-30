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
		
	    
		// Handle dependency-injection for unit tests
	    
		if(FOX_sUtil::keyExists('process_id', $args)){
		    
			$this->process_id = $args['process_id'];
		}
		else {	
			global $fox;
			$this->process_id = $fox->process_id;
		}

		if(FOX_sUtil::keyExists('max_offset', $args)){
		    
			$this->max_offset = $args['max_offset'];
		}
		else {	
			$this->max_offset = 2147483646;  // (32-bit maxint)
		}		
	    
	    	$this->has_libs = true;
		
		if($this->enable == true){
		    
			$this->server = array('ip'=>'127.0.0.1', 'port'=>6379, 'database'=>15, 'alias'=>'first');

			require_once ( FOX_PATH_LIB . '/predis/autoload.php' );

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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {
			$this->engine->flushAll();						
		}
		
		return true;
		
	}


	/**
	 * Removes all entries within the specified namespace from the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushNamespace($ns){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));		    		
			}	
			
			$offset = self::getOffset($ns);

			if($offset < 255){
				$offset++;
			}
			else {
				$offset = 1;
			}

			$expire = 0;			
			$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, $expire);
			
			if(!$set_ok){

				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Error writing to cache",
					'data'=>array('ns'=>$ns, 'offset'=>$offset, 'expire'=>$expire),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			
			}			
			
		}
		
		return true;

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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {
			
			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));		    		
			}
		
			$offset = $this->engine->get("fox.ns_offset.".$ns);			

			// If there is no offset key for the namespace present in 
			// the cache, create one
			
			if(!$offset){

				$offset = 1;
			
				$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset);				

				if(!$set_ok){
				    
					throw new FOX_exception(array(
						'numeric'=>3,
						'text'=>"Error writing to cache",
						'data'=>array('namespace'=>$ns, 'offset'=>$offset, 'expire'=>$expire),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function set($ns, $var, $val){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {			
			
			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		
			$key = "fox." . $ns . "." . $offset . "." . $var;
			$expire = 0;
			
			// Neither of PHP's memcache libraries understands the difference
			// between (bool)true, (int)1, and (float)1. So we have to serialize
			// EVERY piece of data we send to the cache. This increases cache
			// value memory usage by 400% for small items, and CPU usage by
			// at least an order of magnitude when fetching large items. 
			
			$sval = serialize($val);
			
			$set_ok = $this->engine->set($key, $sval);
			
			if(!$set_ok){

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Error writing to cache",
					'data'=>array('key'=>$key, 'val'=>$val, 'offset'=>$offset),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
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
	 * @return bool | Exception on failure. True on success.
	 */

	public function setMulti($ns, $data){
	    
	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			$processed = array();

			// Add namespace prefix to each keyname
			
			foreach($data as $key => $val){

				// Neither of PHP's memcache libraries understands the difference
				// between (bool)true, (int)1, and (float)1. So we have to serialize
				// EVERY piece of data we send to the cache. This increases cache
				// value memory usage by 400% for small items, and CPU usage by
				// at least an order of magnitude when fetching large items. 

				$sval = serialize($val);
			
				$processed["fox." . $ns . "." . $offset . "." . $key] = $sval;							
			}
			unset($key, $val);
			
			$expire = 0;
			
			if( $this->mode == 'full' ){
			    
				$set_ok = $this->engine->setMulti($processed, $expire);
				
				if(!$set_ok){

					throw new FOX_exception(array(
						'numeric'=>4,
						'text'=>"Error writing to cache in 'full' mode",
						'data'=>array('processed'=>$processed, 'expire'=>$expire, 'set_ok'=>$set_ok),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));
				}				
			}
			else {
			    
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
						'numeric'=>5,
						'text'=>"Error writing to cache in 'basic' mode",
						'data'=>array('processed'=>$processed, 'failed_keys'=>$failed_keys),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
	 * @return mixed | Exception on failure. Null and invalid on nonexistent. Stored data item and valid on success.
	 */

	public function get($ns, $var, &$valid=null){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
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
			    
				$cache_result = $this->engine->mget( array($key) );	

				if( FOX_sUtil::keyExists($key, $cache_result)){

					$valid = true;
					$result = $cache_result[$key];
				}
				else {
					$valid = false;
					$result = null;
				}			    			    			    
			}
			
		}
		
		// Neither of PHP's memcache libraries understands the difference
		// between (bool)true, (int)1, and (float)1. So we have to serialize
		// EVERY piece of data we send to the cache. This increases cache
		// value memory usage by 400% for small items, and CPU usage by
		// at least an order of magnitude when fetching large items. 

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
	 * @return mixed | Exception on failure. Stored data items array on success.
	 */

	public function getMulti($ns, $names){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
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
			
		}
		
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
	 * 
	 * @return bool | Exception on failure. False on key nonesistent. True on key exists.
	 */

	public function del($ns, $var){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			$key = "fox." . $ns . "." . $offset . "." . $var;
			$delete_ok = $this->engine->delete($key);			

		}
		
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
	 * @return bool | Exception on failure. Int number of keys deleted on success.
	 */

	public function delMulti($ns, $data){

	    
		if( !$this->isActive() ){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
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

		}
		
		return $key_count;

	}


} // End of class FOX_mCache_driver_redis


?>