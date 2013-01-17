<?php

/**
 * FOXFIRE USER GROUP MEMBERS
 * Keeps track of which groups a user is a member of. Handles adding or removing keys from a user's keyring
 * when they are added to a group or removed from a group.
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

class FOX_uGroupMember extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class

	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_sys_user_group_members",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_uGroupMember",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "user_id" =>	array(	"php"=>"int",    "sql"=>"int",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,
			// This forces every userid + tree + branch + node name combination to be unique
			"index"=>array("name"=>"userid_groupid", "col"=>array("user_id", "group_id"), "index"=>"PRIMARY"), "this_row"=>true),
		    "group_id" =>	array(	"php"=>"int",	 "sql"=>"smallint", "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true)
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
	 * Loads user groups into the cache.
	 * 
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int. Multiple user id's as array of int.
	 * @param int/array $group_id | Single group id as int. Multiple group ids as array of int.
	 * @return bool | False on failure. True on success.
	 */

	public function load($user_id, $group_id=null, $skip_load=false){

		global $fox;

		$db = new FOX_db();
		$args = array();

		if($user_id){
			$args[] = array("col"=>"user_id", "op"=>"=", "val"=>$user_id);
		}

		if($group_id){
			$args[] = array("col"=>"group_id", "op"=>"=", "val"=>$group_id);
		}

		$columns = array("mode"=>"include", "col"=>array("user_id", "group_id") );
		$ctrl = array("format"=>"array_key_array", "key_col"=>array("user_id", "group_id") );

		try{
			$db_result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("args"=>$args,"columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		// When the function is called with specific groups to look for, and a user isn't a member
		// of that group, we can add this information to the cache. This saves a query the next time
		// the group is requested.
		if($group_id){

			if( !is_array($user_id) ){
				$user_id = array($user_id);
			}

			if( !is_array($group_id) ){
				$group_id = array($group_id);
			}

			$all_ok = true;

			foreach( $user_id as $user ){

				// Load the persistent cache record for the user_id into the class cache
				if(!$skip_load){
					$this->cache[$user] = $fox->cache->get("FOX_uGroupMember", $user);
				}

				foreach( $group_id as $group ){

					// Add the group keys to the user's class cache record
					if( FOX_sUtil::keyExists($group, $db_result[$user]) ){

						$this->cache[$user]["keys"][$group] = true;
					}
					else {
						$this->cache[$user]["keys"][$group] = false;
					}
				}
				unset($group);

				// Write the class cache record for the user_id back to the persistent cache
				$cache_ok = $fox->cache->set("FOX_uGroupMember", $user, $this->cache[$user]);

				if(!$cache_ok){
					$all_ok = false;
				}

			}
			unset($user);

			return $all_ok;

		}
		// If the function is called with only a user_id, all of the groups that user belongs to will be added
		// to the cache. But we cannot add information about the groups a user is not a member of to the list,
		// because we don't have a list of groups to check against.
		else {

			if($db_result){

				$all_ok = true;

				foreach( $db_result as $user => $groups){

					// There is no need to load the persistent cache for the user, because
					// we're overwriting every item in the user's cache page

					$this->cache[$user]["all_cached"] = true;

					foreach( $groups as $group => $fake_var){

						$this->cache[$user]["keys"][$group] = true;
					}
					unset($group, $fake_var);

					$cache_ok = $fox->cache->set("FOX_uGroupMember", $user, $this->cache[$user]);

					if(!$cache_ok){
						$all_ok = false;
					}

				}
				unset($user, $group);

				return $all_ok;
			}
			else {
				return false;
			}

		} // ENDOF: if($group_id){
		
	}

	/**
	 * Checks if a user is a member of a group
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | Single $user_id as int.
	 * @param int $group_id | Single $group_id as int.
	 * @return bool | True if user is a member of the group. False if not.
	 */

	public function inGroup($user_id, $group_id){
			
		global $fox;

		// If the user-group pair has an entry in the class cache array, return its value (true
		// if the user has the key, false if they don't)
		if( FOX_sUtil::keyExists($group_id, $this->cache[$user_id]["keys"]) ){

			$result = $this->cache[$user_id]["keys"][$group_id];
		}
		// If the user-group pair doesn't exist in the class cache array, but all of the user's
		// groups have been cached, the user is not a member of the group, so return false
		elseif( $this->cache[$user_id]["all_cached"] == true ){

			$result = false;
		}
		// Otherwise, load the class cache page for the user from the persistent
		// cache and try again
		else {

			$this->cache[$user_id] = $fox->cache->get("FOX_uGroupMember", $user_id);

			if( FOX_sUtil::keyExists($group_id, $this->cache[$user_id]["keys"]) ){

				$result = $this->cache[$user_id]["keys"][$group_id];
			}
			elseif( $this->cache[$user_id]["all_cached"] == true ){

				$result = false;
			}
			// If the user-group pair doesn't exist in the persistent cache, load the info from
			// the db, update the caches, and return the result
			else {
				try{
					self::load($user_id, $group_id);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>1,
						    'text'=>"FOX_uGroupMember load exception",
						    'data'=>array("user_id"=>$user_id, "group_id"=>$group_id),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				
				$result = $this->cache[$user_id]["keys"][$group_id];
			}
		}

		return $result;

	}


	/**
	 * Gets a list of the groups a user is a member of
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | Single $user_id as int.
	 * @return array | Array of group id's
	 */

	public function getGroups($user_id){

		global $fox;

		// If all the user's group_id's are present in the class cache, return the group_id's
		// from the class cache
		if( $this->cache[$user_id]["all_cached"] == true ){

			$result = $this->cache[$user_id]["keys"];
		}
		// Otherwise, load the class cache page for the user from the persistent cache and try again
		else {

			$this->cache[$user_id] = $fox->cache->get("FOX_uGroupMember", $user_id);

			if( $this->cache[$user_id]["all_cached"] == true ){

				$result = $this->cache[$user_id]["keys"];
			}
			// If all the user's group_id's are not present  in the persistent cache, load the
			// info from the db, update the caches, and return the result
			else {
				try{
					self::load($user_id);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>1,
						    'text'=>"FOX_uGroupMember load exception",
						    'data'=>array("user_id"=>$user_id),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				
				$result = $this->cache[$user_id]["keys"];
			}
		}

		return $result;

	}


	/**
	 * Adds one or more users to one or more groups
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user_id as int. Multiple user_id's as array of ints.
	 * @param int/array $group_id | Single group id as int. Multiple group ids as array of int.
	 * @return bool | False on failure. True on success.
	 */

	public function addToGroup($user_id, $group_id){

		global $fox;
		$db = new FOX_db();

		// CASE 1: Single user_id, single group_id
		// =================================================================
		if( !is_array($user_id) && !is_array($group_id) ){

			// Check if the user is already a member of the group
			if( self::inGroup($user_id, $group_id) ){
				return true;
			}

			// Get the group's keyring
			try{
				$gk = new FOX_uGroupKeyRing();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"FOX_uGroupKeyRing constructor exception",
					    'data'=>array("group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				
				
			try{
				$group_keyring = $gk->getKeys($group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				


			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			if( $db->beginTransaction() ){

				// Add the user-group pair to the group members table
				$data = array("user_id"=>$user_id, "group_id"=>$group_id);
				try{
					$group_ok = $db->runInsertQuery(self::$struct, $data, $columns=null);
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

				// Grant the user the group's keyring
				if($group_keyring){
					$ks = new FOX_uKeyRing();
					try{
						$keys_ok = $ks->grantKey($user_id, $group_keyring);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>4,
							    'text'=>"FOX_uKeyRing exception",
							    'data'=>array("user_id"=>$user_id, "group_keyring"=>$group_keyring),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					
				}
				else {
					// Handle groups with no keys in their keyring
					$keys_ok = true;
				}

				// If all operations were successful, commit the transaction
				if( $group_ok && $keys_ok ){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>5,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						

					// Update the cache
					if($commit_ok){

						// Load, update, writeback
						$this->cache[$user_id] = $fox->cache->get("FOX_uGroupMember", $user_id);
						$this->cache[$user_id]["keys"][$group_id] = true;
						$cache_ok = $fox->cache->set("FOX_uGroupMember", $user_id, $this->cache[$user_id]);

						return $cache_ok;
					}
					else {
						return false;
					}
				}
				else {
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>6,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					
					return false;
				}

			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			
		}

		// CASE 2: Single user_id, multiple groups
		// =================================================================
		if( !is_array($user_id) && is_array($group_id) ){

			// Load all of the user's "to be added" groups into the cache
			try{
				self::load($user_id, $group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>7,
					    'text'=>"FOX_uGroupMember load exception",
					    'data'=>array("user_id"=>$user_id, "group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}					

				
			$groups_to_add = array();

			// Create an array of all the groups the user needs to be added to
			foreach( $group_id as $group ){

				if( !$this->cache[$user_id]["keys"][$group] ){
					$groups_to_add[] = $group;
				}
			}
			unset($group, $in_cache);

			// If the user is already a member of all the requested groups, quit
			if( empty($groups_to_add) ){
				return true;
			}

			// Get combined keyring of groups to add the user to
			try{
				$gk = new FOX_uGroupKeyRing();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>8,
					    'text'=>"FOX_uGroupKeyRing constructor exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				
				
			try{
				$keyring = $gk->getKeys($groups_to_add);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>9,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("groups_to_add"=>$groups_to_add),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


			try{
				$transaction_started = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>10,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}	
			
			if($transaction_started){	

				// Add the user-group pairs to the group members table
				$data = array();

				foreach( $groups_to_add as $group ){

					$data[] = array("user_id"=>$user_id, "group_id"=>$group);
				}
				unset($group);
				
				try{
					$group_ok = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>11,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				

				
				// Grant the user the groups' combined keyrings
				if($keyring){
					$ks = new FOX_uKeyRing();
					try{
						$keys_ok = $ks->grantKey($user_id, $keyring);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>12,
							    'text'=>"FOX_uKeyRing grantKey exception",
							    'data'=>array("user_id"=>$user_id, "group_id"=>$group_id),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
				}
				else {
					// Handle none of the groups having any keys on their keyring
					$keys_ok = true;
				}

				// If all operations were successful, commit the transaction
				if( $group_ok && $keys_ok ){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>13,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						

					// Update the cache
					if($commit_ok){

						// Load, update, writeback
						$this->cache[$user_id] = $fox->cache->get("FOX_uGroupMember", $user_id);

						foreach( $groups_to_add as $group ) {
							$this->cache[$user_id]["keys"][$group] = true;
						}
						unset($group);

						$cache_ok = $fox->cache->set("FOX_uGroupMember", $user_id, $this->cache[$user_id]);

						return $cache_ok;
					}
					else {
						return false;
					}
					
				}
				else {
					
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>14,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					
					
					return false;
				}

			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 3: Multiple user_id's, single group
		// =================================================================
		if( is_array($user_id) && !is_array($group_id) ){


			// Load all of the user's to be added to the group into the cache
			try{
				self::load($user_id, $group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>15,
					    'text'=>"FOX_uGroupMember load exception",
					    'data'=>array("user_id"=>$user_id, "group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			
			$users_to_add = array();

			// Create an array of "to be added" users that aren't members of the group yet
			foreach( $user_id as $user ){

				if( !$this->cache[$user]["keys"][$group_id] ){
					$users_to_add[] = $user;
				}
			}
			unset($user, $in_cache);

			// If there are no users that are not already in the group, quit
			if( empty($users_to_add) ){
				return true;
			}

			// Get the group's keyring
			try{
				$gk = new FOX_uGroupKeyRing();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>16,
					    'text'=>"FOX_uGroupKeyRing constructor exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				
				
			try{
				$group_keyring = $gk->getKeys($group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>17,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			
			try{
				$transaction_started = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>18,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}	
			
			if($transaction_started){

				// Add the user-group pairs to the groupstore db table
				$data = array();

				foreach( $users_to_add as $user){

					$data[] = array("user_id"=>$user, "group_id"=>$group_id);
				}
				unset($user);

				try{
					$group_ok = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>19,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}					

		
				// Grant the group's keyring to each user
				if($group_keyring){
					
					$ks = new FOX_uKeyRing();

					$keys_ok = true;

					foreach( $users_to_add as $user){

						try{
							$grant_ok = $ks->grantKey($user, $group_keyring);
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>20,
								    'text'=>"FOX_uKeyRing grantKey exception",
								    'data'=>array("user"=>$user, "group_key_ring"=>$group_keyring),
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}						
						
						if(!$grant_ok){
							$keys_ok = false;
						}
					}
					unset($user);

				}
				else {
					// Handle the group having no keys on its keyring
					$keys_ok = true;
				}


				// If all operations were successful, commit the transaction
				if( $group_ok && $keys_ok ){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>21,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
						
					// Update the cache
					if($commit_ok){

						$all_ok = true;

						foreach( $users_to_add as $user) {

							// Load, update, writeback
							$this->cache[$user] = $fox->cache->get("FOX_uGroupMember", $user);
							$this->cache[$user]["keys"][$group_id] = true;
							$cache_ok = $fox->cache->set("FOX_uGroupMember", $user, $this->cache[$user]);

							if(!$cache_ok){
								$all_ok = false;
							}
						}
						unset($user);

						return $all_ok;
					}
					else {
						return false;
					}
				}
				else {	
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>22,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
						
					return false;
				}
				
			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 4: Multiple user_id's, multiple groups
		// =================================================================
		if( is_array($user_id) && is_array($group_id) ){

			// Load all of the user-group pairs to be added into the cache
			try{
				self::load($user_id, $group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>23,
					    'text'=>"FOX_uGroupMember load exception",
					    'data'=>array("user_id"=>$user_id, "group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				

			// Create an array of user-group pairs that need to be added
			$groups_to_add = array();

			foreach( $user_id as $user ){

				foreach( $group_id as $group){

					if( !$this->cache[$user]["keys"][$group] ){
						$groups_to_add[$user][] = $group;
					}
				}
			}
			unset($user, $group);

			
			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			try{
				$transaction_started = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>24,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}	
			
			if($transaction_started){

				$all_ok = true;
				try{
				    $gk = new FOX_uGroupKeyRing();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>25,
						    'text'=>"FOX_uGroupKeyRing constructor exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}				    

				foreach($user_id as $user){

					if($groups_to_add[$user]){

						// Add the user-group pairs to the groupstore db table
						$data = array();
						$add_group_ids = array();

						foreach( $groups_to_add[$user] as $group ){

							$data[] = array("user_id"=>$user, "group_id"=>$group);
							$add_group_ids[] = $group;
						}
						unset($group);

						try{
							$groups_ok = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>26,
								    'text'=>"DB insert exception",
								    'data'=>$data,
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}							
						
						// Get the groups' combined keyring
						try{
							$keyring = $gk->getKeys($add_group_ids);
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>27,
								    'text'=>"FOX_uGroupKeyRing getKeys exception",
								    'data'=>array("add_group_ids"=>$add_group_ids),
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						// Grant the user the groups' combined keyrings
						if($keyring){
							$ks = new FOX_uKeyRing();
							try{
								$keys_ok = $ks->grantKey($user, $keyring);
							}
							catch(FOX_exception $child){

								throw new FOX_exception(array(
									    'numeric'=>28,
									    'text'=>"FOX_uketRing grantKey exception",
									    'data'=>array("user"=>$user, "keyring"=>$keyring),
									    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
									    'child'=>$child
									    )
								);
							}								
						}
						else {
							// Handle none of the groups having any keys on their keyring
							$keys_ok = true;
						}
						
						if($groups_ok  && $keys_ok){
							$user_count++;
						} 
						else { 
							$all_ok = false;
						}

					}

				} 
				unset($user);
				
				// If all operations completed successfully, commit the transaction 
				if($all_ok && ($user_count >= 1) ){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>29,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					
					// Update the cache
					if($commit_ok){

						$all_ok = true;

						foreach( $user_id as $user) {

							// Load, update, writeback
							$this->cache[$user] = $fox->cache->get("FOX_uGroupMember", $user);

							foreach( $group_id as $group) {
								$this->cache[$user]["keys"][$group] = true;
							}
						    
							$cache_ok = $fox->cache->set("FOX_uGroupMember", $user, $this->cache[$user]);
							
							if(!$cache_ok){
								$all_ok = false;
							}
						}
						unset($user, $group);

						return $all_ok;

					}
					else {
						return false;
					}
				}
				elseif( $all_ok && ($user_count < 1) ){

					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>30,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return true;
				}
				else {
					
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>31,
							    'text'=>"beginTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return false;
				}

			} 
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@									
		}

	} 



	/**
	 * Removes one or more users from one or more groups
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user_id as int. Multiple user_id's as array of ints.
	 * @param int/array $group_id | Single group_id as int. Multiple group_id's as array of ints.
	 * @return int | False on failure. Int number of rows deleted on success. Int 0 if no rows deleted.
	 */

	public function removeFromGroup($user_id, $group_id) {

		global $fox;
		$db = new FOX_db();
		try{
			$gk = new FOX_uGroupKeyRing();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1 ,
				    'text'=>"FOX_uGroupKeyRing constructor exception",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}			

		// CASE 1: Single user_id, single group_id
		// =================================================================
		if( !is_array($user_id) && !is_array($group_id) ){


			// Get the combined keyring of the user's current groups, 
			// minus the one we're removing the user from
			$args = array(

				array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
					array("col"=>"group_id", "op"=>"!=", "val"=>$group_id)
			);

			$columns = array("mode"=>"include", "col"=>"group_id" );
			$ctrl = array("format"=>"col");

			try{
				$in_groups = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"DB select exception",
					    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				
			
			try{
				$keep_keys = $gk->getKeys($in_groups);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("in_groups"=>$in_groups),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}				


			// Get the keyring for the group we're removing the user from
			try{
				$drop_keys = $gk->getKeys($group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("group_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// Intersect the remove group's keyring with the user's other groups' keyrings
			// to determine the keys that need to be revoked from the user's keyring
			if($keep_keys && $drop_keys){

				$revoke_keys = array_diff($drop_keys, $keep_keys);
			}
			else{
				$revoke_keys = $drop_keys;
			}


			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
				$transaction_started = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>5,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if($transaction_started ){

				// Drop the user-group pair from the groupstore db table
				$args = array(
						array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);

				try{
					$rows_changed = $db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>6,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If the user is not a member of the group we are attempting to delete
				// them from, return "0" to indicate that no rows were changed
				
				if(!$rows_changed){
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>7,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return 0;
				}

				// Revoke the keys on the group's keyring that the user is not
				// being granted by other groups
				if($revoke_keys){
					$ks = new FOX_uKeyRing();
					try{
						$keys_ok = $ks->revokeKey($user_id, $revoke_keys);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>8,
							    'text'=>"FOX_uKeyRing revokeKey exception",
							    'data'=>array("user_id"=>$user_id, "revoke_keys"=>$revoke_keys),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
				}
				else {
					// Handle no keys to revoke
					$keys_ok = true;
				}

				// If all operations were successful, commit the transaction
				if( $rows_changed && $keys_ok ){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>9,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						

					// Update the cache
					if($commit_ok){

						// Load, update, writeback
						$this->cache[$user_id] = $fox->cache->get("FOX_uGroupMember", $user_id);
						$this->cache[$user_id]["keys"][$group_id] = false;
						$cache_ok = $fox->cache->set("FOX_uGroupMember", $user_id, $this->cache[$user_id]);

						if($cache_ok){
							return $rows_changed;
						}
						else {
							return false;
						}

					}
					else {
						return false;
					}
				}
				else {
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>10,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}					
					return false;
				}

			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 2: Single user_id, multiple groups
		// =================================================================
		if( !is_array($user_id) && is_array($group_id) ){


			// Get the combined keyring of all the user's current groups,
			// minus the groups we're removing them from
			$args = array(
					array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
					array("col"=>"group_id", "op"=>"!=", "val"=>$group_id)
			);

			$columns = array("mode"=>"include", "col"=>"group_id" );
			$ctrl = array("format"=>"col");
			try{
				$in_groups = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>11,
					    'text'=>"DB select exception",
					    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			
			try{
				$keep_keys = $gk->getKeys($in_groups);

			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>12,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("in_groups"=>$in_groups),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			// Get the combined keyring of all the groups that we're
			// removing the user from
			try{
				$drop_keys = $gk->getKeys($group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>13,
					    'text'=>"FOX_uGroupKeyRing getKeys exception",
					    'data'=>array("groiup_id"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// Intersect the $keep_keys and $drop_keys arrays to get
			// a list of keys we need to revoke from the user
			if($keep_keys && $drop_keys){

				$revoke_keys = array_diff($drop_keys, $keep_keys);
			}
			else{
				$revoke_keys = $drop_keys;
			}


			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
				$transaction_started = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>14,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			
			if( $transaction_started ){

				// Drop the user-group pairs from the groupstore db table
				$args = array(
						array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);

				try{
					$rows_changed = $db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>15,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If the user is not a member of any of the groups we're trying to delete
				// them from, return "0" to indicate that no db rows were changed

				if(!$rows_changed){
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>16,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
							);
					}						
					return 0;
				}

				// Revoke all the keys we previously calculated
				if($revoke_keys){
					$ks = new FOX_uKeyRing();
					try{
						$keys_ok = $ks->revokeKey($user_id, $revoke_keys);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>17,
							    'text'=>"FOX_uKeyRing revokeKey exception",
							    'data'=>array("user_id"=>$user_id, "revoke_keys"=>$revoke_keys),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
				}
				else {
					// Handle no keys to revoke
					$keys_ok = true;
				}

				// If all operations were successful, commit the transaction
				if( $rows_changed && $keys_ok ){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>18,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Update the cache
					if($commit_ok){

						// Load, update, writeback
						$this->cache[$user_id] = $fox->cache->get("FOX_uGroupMember", $user_id);

						foreach( $group_id as $group ){
							$this->cache[$user_id]["keys"][$group] = false;
						}
						unset($group);

						$cache_ok = $fox->cache->set("FOX_uGroupMember", $user_id, $this->cache[$user_id]);

						if($cache_ok){
							return $rows_changed;
						}
						else {
							return false;
						}
					}
					else {
						return false;
					}

				}
				else {
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>19,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return false;
				}

			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 3: Multiple user_id's, single group
		// =================================================================
		if( is_array($user_id) && !is_array($group_id) ){


			// Load all of the groups that each user is currently in, except
			// the group we're removing them from
			$args = array(
					array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
					array("col"=>"group_id", "op"=>"!=", "val"=>$group_id)
			);

			$ctrl = array("format"=>"array_key_array_grouped", "key_col"=>array("user_id", "group_id") );

			try{
				$in_groups = $db->runSelectQuery(self::$struct, $args, $columns=null, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>20,
					    'text'=>"DB select exception",
					    'data'=>array("args"=>$args, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
			
				$started_transaction = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>21,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			
			
			if($started_transaction){

				$all_ok = true;
				try{
					$gk = new FOX_uGroupKeyRing();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>22,
						    'text'=>"FOX_uGroupKeyRing constructor exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				foreach($user_id as $user){

					// Get the combined keyring of all the user's other groups
					try{
						$keep_keys = $gk->getKeys($in_groups[$user]);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>23,
							    'text'=>"FOX_uGroupKeyRing getKeys exception",
							    'data'=>array("user"=>$in_groups[$user]),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Get the keyring of the group we're removing the user from
					try{
						$drop_keys = $gk->getKeys($group_id);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>24,
							    'text'=>"FOX_uGroupKeyRing getKeys exception",
							    'data'=>array("group_id"=>$group_id),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Intersect the $keep_keys and $drop_keys arrays to get
					// a list of keys we need to revoke from the user

					if($keep_keys && $drop_keys){
						$revoke_keys = array_diff($drop_keys, $keep_keys);
					}
					else{
						$revoke_keys = $drop_keys;
					}

					// Revoke all the keys we previously calculated
					if($revoke_keys){

						$ks = new FOX_uKeyRing();
						try{
							$keys_ok = $ks->revokeKey($user, $revoke_keys);
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>25,
								    'text'=>"FOX_uKeyRing revokeKey exception",
								    'data'=>array("user"=>$user, "revoke_keys"=>$revoke_keys),
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						
						if($keys_ok === false){	    // Handles (int)0 return value from revokeKey()
							$all_ok = false;    // if we try to remove a key from a user that doesn't
						}			    // have the key, as part of removing a key from
					}				    // a large batch of users
					
				} 
				unset($user);


				// Drop the user-group pairs from the groupstore db table
				$args = array(
						array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);

				try{
					$rows_changed = $db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>26,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If none of the users are a member of the group we're trying to delete
				// them from, return "0" to indicate that no db rows were changed
				
				if(!$rows_changed){
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>27,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return 0;
				}

				// If all operations completed successfully, commit the transaction and
				// return the result of the commit.
				// =======================================================
				if($rows_changed && $all_ok){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>28,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Update the cache
					if($commit_ok){

						$all_ok = true;

						foreach( $user_id as $user) {

							// Load, update, writeback
							$this->cache[$user] = $fox->cache->get("FOX_uGroupMember", $user);
							$this->cache[$user]["keys"][$group_id] = false;
							$cache_ok = $fox->cache->set("FOX_uGroupMember", $user, $this->cache[$user]);

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
						return false;
					}

				}				
				else {	
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>29,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return false;
				}

			} 
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 4: Multiple user_id's, multiple groups
		// =================================================================
		if( is_array($user_id) && is_array($group_id) ){


			// Load all of the groups that each user is currently in, except
			// for the groups we're deleting users from
			$args = array(
					array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
					array("col"=>"group_id", "op"=>"!=", "val"=>$group_id)
			);

			$ctrl = array("format"=>"array_key_array_grouped", "key_col"=>array("user_id", "group_id") );

			try{
				$in_groups = $db->runSelectQuery(self::$struct, $args, $columns=null, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>30,
					    'text'=>"DB select exception",
					    'data'=>array("args"=>$args, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			if($db->beginTransaction()){

				$all_ok = true;
				try{
					$gk = new FOX_uGroupKeyRing();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>31,
						    'text'=>"FOX_uGroupKeyRing constructor exception",
						    'data'=>array("in_groups"=>$in_groups),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				foreach($user_id as $user){

					// Get the combined keyring of all the user's other groups
					try{
						$keep_keys = $gk->getKeys($in_groups[$user]);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>32,
							    'text'=>"FOX_uGroupKeyRing getKeys exception",
							    'data'=>array("user"=>$in_groups[$user]),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Get the keyring of the groups we're removing the user from
					try{
						$drop_keys = $gk->getKeys($group_id);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>33,
							    'text'=>"FOX_uGroupKeyRing getKeys exception",
							    'data'=>array("group_id"=>$group_id),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Intersect the $keep_keys and $drop_keys arrays to get
					// a list of keys we need to revoke from the user
					if($keep_keys && $drop_keys){
						$revoke_keys = array_diff($drop_keys, $keep_keys);
					}
					else{
						$revoke_keys = $drop_keys;
					}

					// Revoke all the keys we previously calculated
					if($revoke_keys){
						$ks = new FOX_uKeyRing();
						try{
							$keys_ok = $ks->revokeKey($user, $revoke_keys);
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>34,
								    'text'=>"FOX_uKeyRing revokeKey exception",
								    'data'=>array("user"=>$user, "revoke_keys"=>$revoke_keys),
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						if($keys_ok === false){	    // Handles (int)0 return value from revokeKey()
							$all_ok = false;    // if we try to remove a key from a user that doesn't
						}			    // have the key, as part of removing a key from
					}				    // a large batch of users

				}
				unset($user);


				// Drop the user-group pairs from the groupstore db table
				$args = array(
						array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);

				try{
					$rows_changed = $db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>35,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If none of the users are a members of any of the groups we're trying to delete
				// them from, return "0" to indicate that no db rows were changed

				if(!$rows_changed){
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>36,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return 0;
				}

				// If all operations completed successfully, commit the transaction and
				// return the result of the commit.
				// =======================================================
				if($rows_changed && $all_ok){

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>37,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Update the cache
					if($commit_ok){

						$all_ok = true;

						foreach( $user_id as $user) {

							// Load, update, writeback
							$this->cache[$user] = $fox->cache->get("FOX_uGroupMember", $user);

							foreach( $group_id as $group) {
								$this->cache[$user]["keys"][$group] = false;
							}

							$cache_ok = $fox->cache->set("FOX_uGroupMember", $user, $this->cache[$user]);

							if(!$cache_ok){
								$all_ok = false;
							}
						}
						unset($user, $group);

						if($all_ok){
							return $rows_changed;
						}
						else {
							return false;
						}

					}
					else {
						return false;
					}
				}
				else {
				    
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>38,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}						
					return false;
				}

			} 
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		
	}


	/**
	 * Drops all groups for a single user_id from the database and cache. Generally
	 * used when deleting user profiles.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | ID of the user
	 * @return bool | False on failure. Number of rows affected on success.
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
				    'data'=>$args,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		unset($this->cache[$user_id]);

		$cache_ok = $fox->cache->del("FOX_uGroupMember", $user_id);

		if($cache_ok){
			return $rows_changed;
		}
		else {
			return false;
		}
		
	}
	
	

} // End of class FOX_uGroupMember



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_FOX_uGroupMember(){

	$cls = new FOX_uGroupMember();
	
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
add_action( 'fox_install', 'install_FOX_uGroupMember', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_FOX_uGroupMember(){

	$cls = new FOX_uGroupMember();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_uGroupMember', 2 );

?>