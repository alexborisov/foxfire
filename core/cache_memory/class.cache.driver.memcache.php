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
	    
		$this->has_libs = false;
		
		$this->servers = array(  
					    array('ip'=>'127.0.0.1', 'port'=>11211, 'persist'=>false, 'weight'=>100) 
		); 
		
		$this->compress_threshold = 0.2;
		
		    
		// CASE 1: Try for the "Memcached" extension, which is the fastest and
		// has the most advanced features.
		// =======================================================================
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
		    
			// NOTE: Memcached's flush() has one-second granularity. It only affects items
			// set a minimum of 1 second before it, and it could affect items set for up to
			// one second after it. This it probably in Memcached's implementation to handle
			// network latency across multiple servers, and would never be a problem in production
			// because the only time a site would dump the *entire* cache is on a server reboot.
			// 
			// However, its a HUGE issue during unit testing when the cache has to be flushed 
			// between every test fixture ...potentially hundreds of times a second. To prevent
			// incredibly frustrating debug problems, we've hard-coded timing margins around the
			// flush method. @see http://ca2.php.net/manual/en/memcache.flush.php

		    
			usleep(1200000);
		    
			$this->engine->flush();	
			
			usleep(1200000);
			
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

	    
    // NOTE: when writing the namespace-locking code, set the lock array FIRST
    // then write the -1 lock offset. This guarantees the lock offset flag won't
    // beset if the lock array write fails	    
	    
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
				$expire = 0;
			
				$set_ok = $this->engine->set("fox.ns_offset.".$ns, $offset, $expire);				

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
			
			$set_ok = $this->engine->set($key, $sval, $expire);
			
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
	
	

} // End of class FOX_mCache_driver_memcache


?>