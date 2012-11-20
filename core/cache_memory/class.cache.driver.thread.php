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
	
	var $cache;			    // Cache data array
	
	var $max_keys;			    // The maximum number of keys to store. Setting this paramater to high 
					    // values will often result in extremely poor performance, because once
					    // full, the cache array has to be re-sorted after every write operation.
	
	
	// ================================================================================================================


	public function __construct($args=null){

		$this->cache = array();	
		
		if($args){
		    
			$this->max_keys = &$args['max_keys'];
		}
		else {	
			$this->max_keys = 1000;
		}	
		
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
	 * Stores a value into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param mixed $val | Value to assign
	 * @return bool | False on failure. True on success.
	 */

	public function set($ns, $var, $val){

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$offset = self::getOffset($ns);

		$key = "fox." . $ns . "." . $offset . "." . $var;
		$result = self::store($key, $val);

		return $result;
	}


	/**
	 * Stores multiple values into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Data to set in the form "key"=>"val"
	 * @return bool | False on failure. True on success.
	 */

	public function setMulti($ns, $data){

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$offset = self::getOffset($ns);
		$processed = array();

		// Add namespace prefix to each keyname
		foreach($data as $key => $val){

			$processed["fox." . $ns . "." . $offset . "." . $key] = $val;
		}
		unset($key, $val);

		self::storeMulti($processed);
		
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
	 * @return mixed | False on failure. Stored data item on success.
	 */

	public function get($ns, $var, &$valid=null){

		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$offset = self::getOffset($ns);

		$key = "fox." . $ns . "." . $offset . "." . $var;
		$result = self::fetch($key, $valid);

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
	 * @return mixed | False on failure. Stored data item on success.
	 */

	public function getMulti($ns, $names){

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$offset = self::getOffset($ns);

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
	 * @return bool | False on failure. True on success.
	 */

	public function del($ns, $var){

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$offset = self::getOffset($ns);

		$key = "fox." . $ns . "." . $offset . "." . $var;
		$result = self::delete($key);

		return $result;
		
	}
	

	/**
	 * Deletes multiple items from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Key names as array of strings.
	 * @return bool | False on failure. True on success.
	 */

	public function delMulti($ns, $data){

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$offset = self::getOffset($ns);
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

	    
		if( empty($ns) ){
		
			throw new FOX_exception( array(
				'numeric'=>1,
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

		$result = self::store("fox.ns_offset.".$ns, $offset);

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

		$this->cache = array();	
		
		return true;
		
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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