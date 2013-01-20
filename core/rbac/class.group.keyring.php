<?php

/**
 * FOXFIRE USER GROUP KEYRING
 * This class determines which keys are granted to a user when they become a member of a group.
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

class FOX_uGroupKeyRing extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class


	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_sys_user_groupkeyring",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_uGroupKeyRing",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "group_id" =>	array(	"php"=>"int",    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,
			// This forces every group_id + key_id combination to be unique
			"index"=>array("name"=>"groupid_keyid", "col"=>array("group_id", "key_id"), "index"=>"PRIMARY"), "this_row"=>true),
		    "key_id" =>		array(	"php"=>"int",	 "sql"=>"smallint", "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true)
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
	 * Loads group keys into the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $group_id | Single group id as int. Multiple group id's as array of int.
	 * @param int/array $key_id | Single key id as int. Multiple key ids as array of int.
	 * @param bool $skip_load | If set true, the function will not update the class cache array from
	 *			    the persistent cache before adding data from a db call and saving it
	 *			    back to the persistent cache.
	 * @return bool | False on failure. True on success.
	 */

	public function load($group_id, $key_id=null, $skip_load=false){

		global $fox;
		$db = new FOX_db();
		$args = array();

		$args = array(
				array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
		);

		if($key_id){
			$args[] = array("col"=>"key_id", "op"=>"=", "val"=>$key_id);
		}

		$columns = array("mode"=>"include", "col"=>array("group_id", "key_id") );

		$ctrl = array("format"=>"array_key_array_true", "key_col"=>array("group_id", "key_id") );

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

		// Load the class cache array from the persistent cache
		if(!$skip_load){
		    
			try{
				self::loadCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"loadCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}

		if($db_result){

			// When the function is called with specific keys to look for, if a group doesn't
			// have that key, we can add that information to the cache. This saves a query
			// the next time the key is requested.

			if($key_id){

				if( !is_array($group_id) ){
					$group_id = array($group_id);
				}

				if( !is_array($key_id) ){
					$key_id = array($key_id);
				}

				foreach( $group_id as $group ){

					foreach( $key_id as $key ){

						if( FOX_sUtil::keyExists($key, $db_result[$group]) ){

							$this->cache["keys"][$group][$key] = true;
						}
						else {
							$this->cache["keys"][$group][$key] = false;
						}
					}
					unset($key);
				}
				unset($group);
			}
			else {

				// If the function is called with only a group_id, all of the keys that
				// group has will be added to the cache. But we cannot add information
				// about what keys a group *doesn't* have to the list, because we don't
				// have a list of keys to check against.

				foreach( $db_result as $group => $keys){

					// Flag the entire group as cached
					$this->cache["groups"][$group] = true;

					foreach( $keys as $key => $fake_var){	// $fake_var is needed because we're operating on
										// key names, not key values

						$this->cache["keys"][$group][$key] = true;
					}
					unset($key);
				}
				unset($group, $keys);

			}

			// Write the updated local cache array to the persistent cache
			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"saveCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			return $cache_ok;

		}
		else {
			return false;
		}

	}


	/**
	 * Checks if a group grants a specific key
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | id of the group
	 * @param int $key_id | id of the key
	 * @return bool | True if group has key. False if group does not have key.
	 */

	public function hasKey($group_id, $key_id){


		if( FOX_sUtil::keyExists($key_id, $this->cache["keys"][$group_id]) ){

			return $this->cache["keys"][$group_id][$key_id];
		}
		else {
			try{
				$this->load($group_id, $key_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"FOX_uGroupKeyRing load exception",
					    'data'=>array("group_id"=>$group_id, "key_id"=>$key_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				   
			return $this->cache["keys"][$group_id][$key_id];
		}

	}


	/**
	 * Gets one or more groups' keyrings. If multiple groups are specified, the groups
	 * individual keyrings are merged, eliminating duplicate keys
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | Group id as int. Multiple groups as array of int.
	 * @return bool/array | False on failure. Array of key id's on success.
	 */

	public function getKeys($group_id){

		global $fox;
		$result = array();

		if( !is_array($group_id) ){

			if( !(is_array($this->cache["groups"]) && array_key_exists($group_id, $this->cache["groups"]) ) ){

				try{
					$this->load($group_id);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>1,
						    'text'=>"FOX_uGroupKeyRing load exception",
						    'data'=>array("group_id"=>$group_id),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}					
			}

			$keyring = $this->cache["keys"][$group_id];

			// Remove all of the "negative" (group doesn't have the key)
			// cache results, and format as numeric-keyed array

			if($keyring){

				foreach($keyring as $key_id => $has_key){

					if($has_key){
						$result[] = $key_id;
					}
				}
				unset($key_id, $has_key);
			}

			return $result;

		}
		else {

			// If a group doesn't already have all its keys loaded into the cache,
			// load them into the cache

			$missing_groups = array();

			foreach($group_id as $group){

				if( !(is_array($this->cache["groups"]) && array_key_exists($group, $this->cache["groups"])) ){

					$missing_groups[] = $group;
				}
			}
			unset($group);

			if( count($missing_groups) > 0 ){

				try{
					$this->load($missing_groups);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>2,
						    'text'=>"FOX_uGroupKeyRing load exception",
						    'data'=>array("missing_groups"=>$missing_groups),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}					
			}

			// Merge the groups' keyrings. There is no PHP array function that will take
			// two or more numeric-keyed arrays and merge them into a single array containing
			// their combined keys with no duplicates. If is possible to merge with duplicates
			// then use array_unique() to remove them, but it would be an N^2 process on an
			// unsorted aray. The algorithm below is probably faster.

			$combined_keyring = array();

			foreach($group_id as $group){

				$keyring = $this->cache["keys"][$group];

				// Remove all of the "negative" (group doesn't have the key)
				// cache results, and format as numeric-keyed array

				if($keyring){

					foreach($keyring as $key_id => $has_key){

						if($has_key){
							$combined_keyring[$key_id] = true;
						}
					}
					unset($key_id, $has_key);
				}
			}
			unset($group, $keyring);

			$result = array_keys($combined_keyring);

			return $result;
		}

	}


	/**
	 * Adds one or more keys to one or more groups' keyrings
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | id of the group as int. Multiple groups as array of int.
	 * @param int/array $key_id | id of the key as int. Multiple keys as array of int.
	 * @return bool | False on failure. True on success.
	 */

	public function addKey($group_id, $key_id){

		global $fox;
		$db = new FOX_db();

		// Force a load of the entire class cache from the persistent cache
		try{
			self::loadCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"loadCache exception",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}


		// CASE 1: Single group_id, single key
		// =================================================================
		if( !is_array($group_id) && !is_array($key_id) ){

			// If the group already has the key, return true to indicate no db rows were changed
			if( self::hasKey($group_id, $key_id) ){

				return true;
			}
			else{
				$data = array("group_id"=>$group_id, "key_id"=>$key_id);

				try{
					$result = $db->runInsertQuery(self::$struct, $data, $columns=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>2,
						    'text'=>"Db insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				if($result){

					// Update the cache
					try{
						$cache_ok = self::saveCache();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>3,
							    'text'=>"saveCache exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}

					if($cache_ok){
						return $result;
					}
					else {
						return false;
					}
				}
				else {
					return $result;
				}
			}

		}

		// CASE 2: Single group_id, multiple keys
		// =================================================================
		if( !is_array($group_id) && is_array($key_id) ){

			$keys_to_add = array();

			// Create an array of "to be added" keys the user doesn't have. Note this algorithm
			// also eliminates duplicate keys, so we don't have to use array_unique() to avoid
			// a failed query if duplicate keys are present in $key_id

			foreach( $key_id as $key ){

				if( !self::hasKey($group_id, $key) ){

					$keys_to_add[] = $key;
				}
			}
			unset($key);

			// Check that there are actually keys to add, because running an insert query
			// with an empty data array will cause an SQL error

			if( count($keys_to_add) > 0){

				// Add the new keys to the database
				$data = array();

				foreach( $keys_to_add as $key ) {
					$data[] = array("group_id"=>$group_id, "key_id"=>$key );
				}
				unset($key);

				try{
					$result = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>4,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				

				// Update the cache
				if($result){

					foreach( $keys_to_add as $key ) {
						$this->cache["keys"][$group_id][$key] = true;
					}
					unset($key);

					try{
						$cache_ok = self::saveCache();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>5,
							    'text'=>"saveCache exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					

					if($cache_ok){
						return $result;
					}
					else {
						return false;
					}

				}
				else {
					return $result;
				}
			}
			else {
				return true;
			}

		}

		// CASE 3: Multiple group_id's, single key
		// =================================================================
		if( is_array($group_id) && !is_array($key_id) ){

			$groups_to_add = array();

			// Create an array of groups to be given the key that don't have the key yet
			foreach( $group_id as $group ){

				if( !self::hasKey($group, $key_id) ){

					$groups_to_add[] = $group;
				}
			}
			unset($group);

			// Check that there are actually keys to add, because running an insert query
			// with an empty data array will cause an SQL error

			if( count($groups_to_add) > 0 ){

				// Add the new keys to the database
				$data = array();

				foreach( $groups_to_add as $group) {

					$data[] = array("group_id"=>$group, "key_id"=>$key_id);
				}
				unset($group);

				try{
					$result = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>6,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				


				// Update the cache
				if($result){

					foreach( $groups_to_add as $group ) {
						$this->cache["keys"][$group][$key_id] = true;
					}
					unset($group);

					try{
					    $cache_ok = self::saveCache();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>7,
							    'text'=>"saveCache exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					

					if($cache_ok){
						return $result;
					}
					else {
						return false;
					}
				}
				else {
					return $result;
				}
			}
			else {
				return true;
			}

		}

		// CASE 4: Multiple group_id's, multiple keys (all groups get same keys)
		// =================================================================
		if( is_array($group_id) && is_array($key_id) ){

			// Create an array of "missing keys" that need to be added. Note this algorithm
			// also eliminates duplicate keys, so we don't have to use array_unique() to avoid
			// a failed query if duplicate keys are present in $key_id

			$keys_to_add = array();

			foreach( $group_id as $group ){

				foreach( $key_id as $key){

					if( !self::hasKey($group, $key) ){

						$keys_to_add[$group][] = $key;
					}
				}
			}
			unset($group, $key);

			// Check that there are actually keys to add, because running an insert query
			// with an empty data array will cause an SQL error

			if( count($keys_to_add) > 0 ){

				// Add the new keys to the database
				$data = array();

				foreach( $keys_to_add as $group => $keys ) {

					foreach( $keys as $key ) {

						$data[] = array("group_id"=>$group, "key_id"=>$key );
					}
				}
				unset($group, $key, $keys);

				try{
					$result = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>8,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				


				// Update the cache
				if($result){

					foreach( $keys_to_add as $group => $keys ) {

					    foreach( $keys as $key ) {
						    $this->cache["keys"][$group][$key] = true;
					    }
					}
					unset($group, $key, $keys);

					try{
						$cache_ok = self::saveCache();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>1,
							    'text'=>"saveCache exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					

					if($cache_ok){
						return $result;
					}
					else {
						return false;
					}
				}
				else {
					return $result;
				}
			}
			else {
				return true;

			} // ENDOF: if( count($keys_to_add) > 0 )


		} // ENDOF: if( is_array($group_id) && is_array($key_id) )


	} // ENDOF: function addKey()


	/**
	 * Removes one or more keys from one or more group's keyrings
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | id of the group as int. Multiple groups as array of int.
	 * @param int/array $key_id | id of the key as int. Multiple keys as array of int.
	 * @return int | Number of keys affected.
	 */

	public function dropKey($group_id, $key_id) {

		global $fox;
		$db = new FOX_db();

		// Drop the keys from the db
		$args = array(
				array("col"=>"group_id", "op"=>"=", "val"=>$group_id),
				array("col"=>"key_id", "op"=>"=", "val"=>$key_id)
		);

		try{
			$result = $db->runDeleteQuery(self::$struct, $args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB delete exception",
				    'data'=>$args,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}		

		// Update the cache
		if($result){

			if(!is_array($group_id)){
				$group_id = array($group_id);
			}

			if(!is_array($key_id)){
				$key_id = array($key_id);
			}

			foreach($group_id as $group) {

			    foreach($key_id as $key) {

				    unset($this->cache["keys"][$group][$key]);
			    }
			}
			unset($group, $key);

			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"saveCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if($cache_ok){
				return $result;
			}
			else {
				return false;
			}

		}
		else {
			return $result;
		}

	}


	/**
	 * Deletes the entire keyring for one or more groups
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | id of the group as int. Multiple groups as array of int.
	 * @return int | Number of keys affected.
	 */

	public function dropGroup($group_id) {

		global $fox;
		$db = new FOX_db();

		// Drop the keys from the db
		$args = array(
				array("col"=>"group_id", "op"=>"=", "val"=>$group_id)

		);

		try{
		    $result = $db->runDeleteQuery(self::$struct, $args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB delete exception",
				    'data'=>$args,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}		

		// Update the cache
		if($result){

			if(!is_array($group_id)){
				$group_id = array($group_id);
			}

			foreach($group_id as $group) {

				unset($this->cache["keys"][$group]);
				unset($this->cache["groups"][$group]);
			}
			unset($group);

			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"saveCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}			
			if($cache_ok){
				return $result;
			}
			else {
				return false;
			}

		}
		else {
			return $result;
		}

	}


} // End of class FOX_uGroupKeyRing



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_uGroupKeyRing(){

	$cls = new FOX_uGroupKeyRing();
	
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
add_action( 'fox_install', 'install_FOX_uGroupKeyRing', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_uGroupKeyRing(){

	$cls = new FOX_uGroupKeyRing();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_uGroupKeyRing', 2 );

?>