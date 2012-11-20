<?php

/**
 * BP-MEDIA MEMORY CACHE - APC DRIVER
 * Stores keys to PHP's built-in APC caching system @link http://php.net/manual/en/book.apc.php
 * providing *server level* persistent caching that *survives a reboot*.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Cache APC
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class BPM_mCache_driver_apc extends BPM_mCache_driver_base {


	// VERY IMPORTANT: The following must be set in your php.ini file:
	// ======================================================================
	//	    [PECL]
	//	    extension=php_apc.dll (on XAMPP installations remove the ";" in front of it)
	//	    [APC]
	//	    apc.slam_defense = 0  (workaround for "cache slam error" defect in PHP's APC lib)
	//	    apc.write_lock = 1
	
	
	var $enable = true;		    // True to enable the driver. False to disable it.
	
	var $is_active;			    // If true, the driver is active
	
	var $has_libs;			    // True if the PHP installation has the libs needed to connect to
					    // an APC installation
	
	var $can_connect;		    // True if the driver can connect to the APC installation	
	    
	
	// ================================================================================================================
	
	
	public function __construct(){
			
	    
		if( function_exists("apc_store") ){

			$this->has_libs = true;
			
			if($this->enable == true){
			    
				$this->is_active = true;
			}
			    
		}
		else {
			$this->has_libs = false;
			$this->is_active = false;
		}		
		
	}
	
	
	/**
	 * Enables the cache driver
	 *
	 * @version 0.1.9
	 * @since 0.1.9
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
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	
	public function disable(){
			    
		$this->enable = false;	    
		$this->is_active = false;			
	}	
	
	
	/**
	 * Checks if the cache engine driver is active
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return bool | True if active. False if disabled.
	 */

	public function isActive(){

		return $this->is_active;
	}


	/**
	 * Returns the current performance stats of the cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getStats(){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {

			return apc_cache_info();
		}

	}


	/**
	 * Removes all entries in the cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushAll(){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {
			apc_clear_cache();		// Clear the "system" (opcode) cache
			apc_clear_cache('opcode');	// Clear the "system" (opcode) cache for PHP < 5.3
			apc_clear_cache('user');	// Clear the "user" (data) cache						
		}
		
		return true;
		
	}


	/**
	 * Removes all entries within the specified namespace from the cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushNamespace($ns){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {

			if( empty($ns) ){

				throw new BPM_exception( array(
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

			$result = apc_store("bpm.ns_offset.".$ns, $offset);			
		}
		
		return $result;

	}


	/**
	 * Gets the offset for a cache namespace.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @return bool | False on failure. True on success.
	 */

	public function getOffset($ns){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {
		    
			if( empty($ns) ){

				throw new BPM_exception( array(
					'numeric'=>2,
					'text'=>"Empty namespace value",
					'data'=>array('ns'=>$ns),			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));		    		
			}
			
			$offset = apc_fetch("bpm.ns_offset.".$ns);

			// If there is no offset key for the namespace present in 
			// the cache, create one
			
			if(!$offset){

				$offset = 1;
				$store_ok = apc_store("bpm.ns_offset.".$ns, $offset);

				if(!$store_ok){
				    
					throw new BPM_exception(array(
						'numeric'=>3,
						'text'=>"Error writing to cache",
						'data'=>array('namespace'=>$ns, 'offset'=>$offset),
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
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param mixed $val | Value to assign
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function set($ns, $var, $val){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {			
			
			if( empty($ns) ){

				throw new BPM_exception( array(
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
			catch (BPM_exception $child) {

				throw new BPM_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		
			$key = "bpm." . $ns . "." . $offset . "." . $var;
			
			$store_ok = apc_store($key, $val);
			
			if(!$store_ok){

				throw new BPM_exception(array(
					'numeric'=>4,
					'text'=>"Error writing to cache",
					'data'=>array('namespace'=>$ns, 'offset'=>$offset),
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
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Data to set in the form "key"=>"val"
	 * @return bool | Exception on failure. True on success.
	 */

	public function setMulti($ns, $data){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new BPM_exception( array(
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
			catch (BPM_exception $child) {

				throw new BPM_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			$processed = array();

			// Add namespace prefix to each keyname
			
			foreach($data as $key => $val){

				$processed["bpm." . $ns . "." . $offset . "." . $key] = $val;
			}
			unset($key, $val);

			// NOTE: apc_store() has a different error reporting format when
			// passed an array @see http://php.net/manual/en/function.apc-store.php
			
			$cache_result = apc_store($processed);

			if( count($cache_result) != 0 ){

				throw new BPM_exception(array(
					'numeric'=>4,
					'text'=>"Error writing to cache",
					'data'=>array('namespace'=>$ns, 'data'=>$data, 'error'=>$cache_result),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			
		}
		
		return true;

	}


	/**
	 * Retrieves a value from the cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param bool &$valid | True if key exists in cache. False if not.
	 * 
	 * @return mixed | Exception on failure. Stored data item on success.
	 */

	public function get($ns, $var, &$valid=null){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new BPM_exception( array(
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
			catch (BPM_exception $child) {

				throw new BPM_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			$key = "bpm." . $ns . "." . $offset . "." . $var;
			$result = apc_fetch($key, $valid);			
			
		}
		
		return $result;

	}


	/**
	 * Retrieves multiple values from the cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $names | Array of cache variable names
	 * @return mixed | Exception on failure. Stored data item on success.
	 */

	public function getMulti($ns, $names){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new BPM_exception( array(
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
			catch (BPM_exception $child) {

				throw new BPM_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// Add namespace prefix to each keyname
			foreach($names as $key){

				$processed[] = "bpm." . $ns . "." . $offset . "." . $key;
			}
			unset($key);

			$cache_result = apc_fetch($processed);

			// APC will return an array of the form "keyname"=>"value". If a key doesn't exist in the
			// cache, the requested key will not be present in the results array.

			$result = array();

			foreach($names as $key){

				// BEFORE: "namespace.offset.keyname"=>"value"
				// AFTER:  "keyname"=>"value"

				$prefixed_name = "bpm." . $ns . "." . $offset . "." . $key;

				if( array_key_exists($prefixed_name, $cache_result) ){	    // This prevents the loop from creating keys
											    // in the $result array if they don't exist in
					$result[$key] = $cache_result[$prefixed_name];	    // the $cache_result array
				}

			}
			unset($key);					
			
		}
		
		return $result;	

	}


	/**
	 * Deletes an item from the cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of key
	 * 
	 * @return bool | Exception on failure. True on key exists. False on key doesn't exist.
	 */

	public function del($ns, $var){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new BPM_exception( array(
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
			catch (BPM_exception $child) {

				throw new BPM_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			$key = "bpm." . $ns . "." . $offset . "." . $var;
			$delete_ok = apc_delete($key);
			
		}
		
		return $delete_ok;
		
	}


	/**
	 * Deletes multiple items from the cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Key names as array of strings.
	 * @return int | Exception on failure. Int number of keys deleted on success.
	 */

	public function delMulti($ns, $data){

	    
		if( !$this->isActive() ){
		    
			throw new BPM_exception(array(
				'numeric'=>1,
				'text'=>"Cache driver is not active",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		else {	

			if( empty($ns) ){

				throw new BPM_exception( array(
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
			catch (BPM_exception $child) {

				throw new BPM_exception(array(
					'numeric'=>3,
					'text'=>"Error in self::getOffset()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			$processed = array();

			// Add namespace prefix to each keyname

			foreach($data as $val){

				$processed[] =  "bpm." . $ns . "." . $offset . "." . $val ;
			}
			unset($val);			
			
			// An undocumented feature of apc_delete() is that it can accept multiple
			// keys at once, as an array of strings. The return value is an array
			// containing the names of any keys that didn't exist in the cache.
			
			$cache_result = apc_delete($processed);
			
			$keys_deleted = count($data) - count($cache_result);			

		}
		
		return $keys_deleted;

	}
	
	
	


} // End of class BPM_mCache_driver_apc


?>