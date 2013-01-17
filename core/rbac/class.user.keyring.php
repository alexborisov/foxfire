<?php

/**
 * FOXFIRE USER KEYRING
 * This class operates as a central key repository for all users on the system. It replaces the the
 * simple class-based WordPress "user roles" model with a sophisticated "token" based system that has
 * flexibility and capacity to serve multi-tiered sites with millions of users.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage RBAC
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_uKeyRing extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class
	

	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_sys_user_keyring",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_uKeyRing",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "user_id" =>	array(	"php"=>"int",    "sql"=>"int",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,
			// This forces every userid + tree + branch + node name combination to be unique
			"index"=>array("name"=>"userid_keyid", "col"=>array("user_id", "key_id"), "index"=>"PRIMARY"), "this_row"=>true),
		    "key_id" =>	array(	"php"=>"int",	"sql"=>"smallint", "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true)
		 )
	);


	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_user_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	// ================================================================================================================


	public function __construct($args=null) {

		if($args){
			$this->process_id = &$args['process_id'];
			$this->mCache = &$args['mCache'];				
		}
		else {
			global $fox;
			$this->process_id = &$fox->process_id;
			$this->mCache = &$fox->mCache;				
		}

		try{
			self::loadCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Error loading cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
	}


	/**
	 * Loads user keys into the cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int. Multiple user id's as array of int.
	 * @param int/array $key_id | Single key id as int. Multiple key ids as array of int.
	 * @return bool | False on failure. True on success.
	 */

	public function load($user_id, $key_id=null, $skip_load=false){

		global $fox;

		$db = new FOX_db();
		$args = array();

		if($user_id){
			$args[] = array("col"=>"user_id", "op"=>"=", "val"=>$user_id);
		}

		if($key_id){
			$args[] = array("col"=>"key_id", "op"=>"=", "val"=>$key_id);
		}

		$columns = array("mode"=>"include", "col"=>array("user_id", "key_id") );
		$ctrl = array("format"=>"array_key_array_grouped", "key_col"=>array("user_id", "key_id") );

		try{
			$db_result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// When the function is called with specific keys to look for, if a user doesn't
		// have that key, we can add that information to the cache. This saves a query
		// the next time the key is requested.

		if($db_result){

			if($key_id){

				if( !is_array($user_id) ){
					$user_id = array($user_id);
				}

				if( !is_array($key_id) ){
					$key_id = array($key_id);
				}

				$all_ok = true;

				foreach( $user_id as $user ){

					// Load the persistent cache record for the user_id into the class's cache array
					if(!$skip_load){
						$this->cache[$user] = $fox->cache->get("FOX_uKeyRing", $user);
					}

					foreach( $key_id as $key ){

						if(FOX_sUtil::valExists($key, $db_result[$user]) ){

							$this->cache[$user]["keys"][$key] = true;
						}
						else {
							$this->cache[$user]["keys"][$key] = false;
						}
					}

					// Save the updated persistent cache record for the user_id
					$cache_ok = $fox->cache->set("FOX_uData", $user, $this->cache[$user]);

					if(!$cache_ok){
						$all_ok = false;
					}
				}

				return $all_ok;

			}
			else {

				// If the function is called without $key_id set, all of the keys for each user will be added
				// to the cache. At this point we can unset all of the user's "false" keys because we know the
				// list of keys is authoratative (if it's not in the array, they don't have the key).

				$all_ok = true;

				foreach( $db_result as $user => $keys){

					unset( $this->cache[$user] );

					$this->cache[$user]["all_cached"] = true;

					foreach( $keys as $key ){

						$this->cache[$user]["keys"][$key] = true;
					}

					// Save the updated persistent cache record for the user_id
					$cache_ok = $fox->cache->set("FOX_uData", $user, $this->cache[$user]);

					if(!$cache_ok){
						$all_ok = false;
					}
				}

				return $all_ok;


			}

		}
		else {
			return false;
		}

	}


	/**
	 * Checks if a single user has a single key. If the key is not already cached, it will
	 * be added to the class cache and the persistent cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | user_id to check
	 * @param int $key_id | key_id to check for
	 * @return bool | True if user_id has key. False if not.
	 */

	public function hasKey($user_id, $key_id){

		global $fox;
		$result = array();

		// If the key has an entry in the class cache array, return its value (true
		// if the user has the key, false if they don't)
		if( FOX_sUtil::keyExists($key_id, $this->cache[$user_id]["keys"]) ){

			$result = $this->cache[$user_id]["keys"][$key_id];
		}
		// If the key doesn't exist in the class cache array, but all of the user's
		// keys have been cached, the user doesn't have the key, so return false
		elseif( $this->cache[$user_id]["all_cached"] == true ){

			$result = false;
		}
		// Otherwise, load the class cache page for the user from the persistent
		// cache and try again
		else {

			$this->cache[$user_id] = $fox->cache->get("FOX_uKeyRing", $user_id);

			if( FOX_sUtil::keyExists($key_id, $this->cache[$user_id]["keys"]) ){

				$result = $this->cache[$user_id]["keys"][$key_id];
			}
			elseif( $this->cache[$user_id]["all_cached"] == true ){

				$result = false;
			}
			// If the key is not present in the persistent cache, load the info from
			// the db, update the caches, and add the result to the $result array
			else {

				self::load($user_id, $key_id, $skip_load=true);
				$result = $this->cache[$user_id]["keys"][$key_id];
			}

		}

		return $result;

	}


	/**
	 * Gets the entire keyring for a single user. If the user_id's keyring is not already cached,
	 * it will be added to the class cache and persistent cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | user_id to get keyring for
	 * @return array | Key ids for user_id
	 */

	public function getKeys($user_id){

		$data = array($user_id);

		try{
			$result = self::getKeysMulti($data);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"FOX_uKeyRing getKeyMulti exception",
				    'data'=>array("data"=>$data),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		return $result[$user_id];

	}


	/**
	 * Gets the entire keyring for multiple users. If a user_id's keyring is not already cached,
	 * it will be added to the class cache and persistent cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $users | array of user id's
	 * @return array | "user_id"=>array($key_id)
	 */

	public function getKeysMulti($users){

		global $fox;
		$result = array();

		foreach($users as $user_id){

			// If the user's entire keyring is not present in the class cache array, load the
			// class cache page for the user from the persistent cache

			if( !$this->cache[$user_id]["all_cached"] == true ){

				$this->cache[$user_id] = $fox->cache->get("FOX_uKeyRing", $user_id);

				// If the user's entire keyring is not present in the persistent cache,
				// load it from the db and update both caches

				if( !$this->cache[$user_id]["all_cached"] == true ){

					try{
						self::load($user_id, $key_id, $skip_load=true);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>1,
							    'text'=>"FOX_uKeyRing load exception",
							    'data'=>array("user_id"=>$user_id, "key_id"=>$key_id, "skip_load"=>true),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
				}
			}

			// Remove any "negative" cache results (user doesn't have the key),
			// and format as numeric-keyed array

			$user_result = array();
			$keyring = $this->cache[$user_id]["keys"];

			if($keyring){

				foreach($keyring as $key_id => $has_key){

					if($has_key){
						$user_result[] = $key_id;
					}
				}
				unset($key_id, $has_key);
			}

			$result[$user_id] = $user_result;

		}
		unset($user_id, $user_result, $keyring);

		return $result;

	}


	/**
	 * Creates a new key, adding it to the database and cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user_id as int. Multiple user_id's as array of ints.
	 * @param int/array $key_id | Single key_id as int. Multiple key_id's as array of ints.
	 * @return bool | False on failure. True on success but no db rows changed. Int number of rows changed on success.
	 */

	public function grantKey($user_id, $key_id){

		global $fox;
		$db = new FOX_db();

		// CASE 1: Single user_id, single key
		// =================================================================
		if( !is_array($user_id) && !is_array($key_id) ){


			// If the user already has the key, return true to indicate no db rows were changed
			if( self::hasKey($user_id, $key_id) ){

				return true;
			}
			else{
				$data = array("user_id"=>$user_id, "key_id"=>$key_id );

				try{
					$rows_changed = $db->runInsertQuery(self::$struct, $data, $columns=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>1,
						    'text'=>"DB insert exception",
						    'data'=>array("data"=>$data),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				if($rows_changed){

					// Load the user's keyring into the class cache from the persistent cache
					$this->cache[$user_id] = $fox->cache->get("FOX_uKeyRing", $user_id);

					// Add the new key
					$this->cache[$user_id]["keys"][$key_id] = true;

					// Update the persistent cache
					$cache_ok = $fox->cache->set("FOX_uKeyRing", $user_id, $this->cache[$user_id]);

					if($cache_ok){
						return $rows_changed;
					}
					else {
						return false;
					}

				}
				else {
					return $rows_changed;
				}

			}

		}

		// CASE 2: Single user_id, multiple keys
		// =================================================================
		if( !is_array($user_id) && is_array($key_id) ){


			// Load the user's entire keyring into the cache (if not cached already)
			try{
				self::getKeys($user_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"FOX_uKeyRing getKeys exception",
					    'data'=>array("user_id"=>$user_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			// Create an array of "to be added" keys the user doesn't have.
			$keys_to_add = array();

			foreach( $key_id as $key ){

				if( !$this->cache[$user_id]["keys"][$key] ){	// This test condition gets both missing
					$keys_to_add[] = $key;			// keys (null) and negative keys (false)
				}
			}
			unset($key);

			if( count($keys_to_add) > 0 ){

				// Add the new keys to the database
				$data = array();

				foreach( $keys_to_add as $key) {

					$data[] = array("user_id"=>$user_id, "key_id"=>$key);
				}
				unset($key);

				try{
					$rows_changed = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>3,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// Update the cache
				if($rows_changed){

					// Load the user's keyring into the class cache from the persistent cache
					$this->cache[$user_id] = $fox->cache->get("FOX_uKeyRing", $user_id);

					// Add the new keys to the class cache
					foreach( $keys_to_add as $key) {

						$this->cache[$user_id]["keys"][$key] = true;
					}
					unset($key);

					// Update the persistent cache from the class cache
					$cache_ok = $fox->cache->set("FOX_uKeyRing", $user_id, $this->cache[$user_id]);

					if($cache_ok){
						return $rows_changed;
					}
					else {
						return false;
					}

				}
				else {
					return $rows_changed;
				}

			}
			else {
				return true;

			} // ENDOF: if( count($keys_to_add) > 0 )

		}

		// CASE 3: Multiple user_id's, single key
		// =================================================================
		if( is_array($user_id) && !is_array($key_id) ){

			$users_to_add = array();

			// Create an array of "to be added" users that don't have the key
			foreach( $user_id as $user ){

				try{
				    $has_key = self::hasKey($user, $key_id);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>4,
						    'text'=>"FOX_uKeyRing hasKey exception",
						    'data'=>array("user"=>$user, "key_id"=>$key_id),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				if( !$has_key ){
					$users_to_add[] = $user;
				}
			}
			unset($user);

			if( count($users_to_add) > 0 ){

				// Add the new keys to the database
				$data = array();

				foreach( $users_to_add as $user ) {

					$data[] = array("user_id"=>$user, "key_id"=>$key_id);
				}
				unset($user);

				try{
					$rows_changed = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>5,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// Update the cache
				if($rows_changed){

					$all_ok = true;

					foreach( $users_to_add as $user ) {

						// Load the user's keyring into the class cache from the persistent cache
						$this->cache[$user] = $fox->cache->get("FOX_uKeyRing", $user);

						// Add the new key to the class cache
						$this->cache[$user]["keys"][$key_id] = true;

						// Update the persistent cache from the class cache
						$cache_ok = $fox->cache->set("FOX_uKeyRing", $user, $this->cache[$user]);

						if(!$cache_ok){
							$all_ok = false;
						}

					}
					unset($user);


					if($all_ok){
						return $rows_changed;
					}
					else {
						return false;
					}

				}
				else {
					return $rows_changed;
				}
			}
			else {
				return true;

			} // ENDOF: if( count($users_to_add) > 0 )

		}

		// CASE 4: Multiple user_id's, multiple keys (all users get same keys)
		// =================================================================
		if( is_array($user_id) && is_array($key_id) ){

			// Load each user's full keyring into the cache (if not cached already)
			try{
				self::getKeysMulti($user_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>6,
					    'text'=>"FOX_uKeyRing getKeys exception",
					    'data'=>array("user_id"=>$user_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			// Create an array of keys that need to be added to each user_id
			$keys_to_add = array();

			foreach( $user_id as $user ){

				foreach( $key_id as $key){

					if( !$this->cache[$user]["keys"][$key] ){	// This test condition gets both missing
						$keys_to_add[$user][] = $key;		// keys (null) and negative keys (false)
					}
				}
				unset($key);
			}
			unset($user);


			// Add the new keys to the database
			if( count($keys_to_add) > 0 ){

				$data = array();

				foreach( $keys_to_add as $user => $keys ) {

					foreach( $keys as $key ) {

						$data[] = array("user_id"=>$user, "key_id"=>$key );
					}
					unset($key);
				}
				unset($user, $keys);

				try{
					$rows_changed = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>7,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// Update the cache
				if($rows_changed){

					$all_ok = true;

					foreach( $keys_to_add as $user => $keys ) {

						// Load the user's keyring into the class cache from the persistent cache
						$this->cache[$user] = $fox->cache->get("FOX_uKeyRing", $user);

						// Add the new keys to the class cache
						foreach( $keys as $key) {

							$this->cache[$user]["keys"][$key] = true;
						}
						unset($key);

						// Update the persistent cache from the class cache
						$cache_ok = $fox->cache->set("FOX_uKeyRing", $user, $this->cache[$user]);

						if(!$cache_ok){
							$all_ok = false;
						}

					}
					unset($user, $keys);

					if($all_ok){
						return $rows_changed;
					}
					else {
						return false;
					}

				}
				else {
					return $rows_changed;
				}

			}
			else {
				return true;

			} // ENDOF: if( count($keys_to_add) > 0 )

		}


	} // ENDOF: function grantKey()


	/**
	 * Drops one or more keys from the database for one or more user_id's
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user_id as int. Multiple user_id's as array of ints.
	 * @param int/array $key_id | Single key_id as int. Multiple key_id's as array of ints.
	 * @return bool/int | False on failure. Int number of rows deleted on success.
	 */

	public function revokeKey($user_id, $key_id) {

		global $fox;
		$db = new FOX_db();

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
				array("col"=>"key_id", "op"=>"=", "val"=>$key_id),
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB delete exception",
				    'data'=>array("args"=>$args),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		if( !is_array($user_id) ){
			$user_id = array($user_id);
		}

		if( !is_array($key_id) ){
			$key_id = array($key_id);
		}

		$all_ok = true;

		// Even if no rows were changed, we still update the cache (just to be sure)
		foreach( $user_id as $user ) {

			// Load the user's keyring into the class cache from the persistent cache
			$this->cache[$user] = $fox->cache->get("FOX_uKeyRing", $user);

			// Remove the deleted keys
			foreach( $key_id as $key) {

				unset($this->cache[$user]["keys"][$key]);
			}
			unset($key);

			// Update the persistent cache from the class cache
			$cache_ok = $fox->cache->set("FOX_uKeyRing", $user, $this->cache[$user]);

			if(!$cache_ok){
				$all_ok = false;
			}

		}
		unset($user);

		if($all_ok){
			return $rows_changed;
		}
		else {
			return false;
		}

	}



	/**
	 * Drops all keys for a single user id from the database and cache. Typically
	 * used when deleting user profiles.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | ID of the user
	 * @return bool/int | False on failure. Int number of rows deleted on success.
	 */

	public function dropUser($user_id) {

		global $fox;
		$db = new FOX_db();

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB delete exception",
				    'data'=>array("args"=>$args),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// Remove user from class cache
		unset($this->cache[$user_id]);

		// Remove from persistent cache
		$cache_ok = $fox->cache->del("FOX_uKeyRing", $user_id);

		if($cache_ok){
			return $rows_changed;
		}
		else {
			return false;
		}

	}


	/**
	 * Drops one or more keys from the database and cache for ALL USERS ON THE SITE. Generally used when
	 * uninstalling or upgfoxing apps, or for managing admin-assigned arbitrary keys.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $key_id | Single key_id as int. Multiple key_id's as array of ints.
	 * @return bool | False on failure. True on success.
	 */

	public function revokeKeySitewide($key_id) {

		global $fox;
		$db = new FOX_db();

		$args = array(
				array("col"=>"key_id", "op"=>"=", "val"=>$key_id)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB delete exception",
				    'data'=>array("args"=>$args),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// Because multiple user_id's are affected by this operation, we have
		// to flush the entire cache
		try{
			$cache_ok = self::flushCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"flushCache exception",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		if($cache_ok){
			return $rows_changed;
		}
		else {
			return false;
		}
	}


} // End of class FOX_uKeyRing


/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_FOX_uKeyRing(){

	$cls = new FOX_uKeyRing();
	
	try {
		$cls->install();
	}
	catch (FOX_exception $child) {

		// If the error is being thrown because the table already exists, 
		// just discard it
	    
		if( $child->data['child']->data['numeric'] != 2 ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error creating db table",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
	}
	
}
add_action( 'fox_install', 'install_FOX_uKeyRing', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_FOX_uKeyRing(){

	$cls = new FOX_uKeyRing();
	
	try {
		$cls->uninstall();
	}
	catch (FOX_exception $child) {

		// If the error is being thrown because the table doesn't exist, 
		// just discard it
	    
		if( $child->data['child']->data['numeric'] != 3 ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error dropping db table",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
	}
	
}
add_action( 'fox_uninstall', 'uninstall_FOX_uKeyRing', 2 );

?>