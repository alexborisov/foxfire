<?php

/**
 * FOXFIRE USER DATASTORE
 * This class operates as a central key:value datastore for user data The class is very database-efficient, it
 * has an internal cache, and multiple keys can be retrieved in a single SQL query.
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

class FOX_uData extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class


	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "rad_sys_user_data",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_uData",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "user_id" =>	array(	"php"=>"int",    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",		"auto_inc"=>false,  "default"=>null,
			// This forces every tree + branch + node name combination to be unique
			"index"=>array("name"=>"userid_tree_branch_node", "col"=>array("user_id", "tree", "branch", "node"), "index"=>"PRIMARY"), "this_row"=>"UNIQUE"),
		    "tree" =>		array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>false),
		    "branch" =>		array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>false),
		    "node" =>		array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
		    "val" =>		array(	"php"=>"serialize", "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>"",			"auto_inc"=>false,  "default"=>null,	"index"=>false),
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
	 * Removes all empty walks from the class cache for a specific user_id
	 *
	 * @version 0.1.9
	 * @since 0.1.9

	 * @param int $user_id | user_id to compact cache for
	 * @return null
	 */

	public function compactCache($user_id) {


		if( FOX_sUtil::keyExists($user_id, $this->cache) ){

			$this->cache[$user_id]["keys"] = FOX_sUtil::arrayPrune($this->cache[$user_id]["keys"], 2);
		}

	}


	/**
	 * Loads key, branch, tree, or all data for one or more users from the db
	 * into the key cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int. Multiple user id as array of int.
	 * @param int/array $tree | Single tree id as int. Multiple tree id as array of int.
	 * @param int/array $branch | Single branch id as int. Multiple branch id as array of int.
	 * @param string/array $key | Single key as string. Multiple keys as array of strings.
	 * @param bool $skip_load | If set true, the function will not update the class cache array from
	 *			    the persistent cache before adding data from a db call and saving it
	 *			    back to the persistent cache.
	 * @return bool | False on failure. True on success.
	 */

	public function load($user_id, $tree=null, $branch=null, $key=null, $skip_load=false){

		global $rad;
		$db = new FOX_db();

		// Run query
		// ===========================================================

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id)
		);

		if($tree){

			$args[] = array("col"=>"tree", "op"=>"=", "val"=>$tree);

			if($branch){

				$args[] = array("col"=>"branch", "op"=>"=", "val"=>$branch);

				if($key){
					$args[] = array("col"=>"node", "op"=>"=", "val"=>$key);
				}
			}
		}


		$ctrl = array("format"=>"array_key_array", "key_col"=>array("user_id", "tree", "branch", "node") );

		try{
			$db_result = $db->runSelectQuery(self::$struct, $args, $columns=null, $ctrl);
		}
		catch(FOX_exception $child){


			$debug_args = array ("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key, "skip_load"=>$skip_load);

			throw new FOX_exception(array(
					'numeric'=>1,
					'text'=>"Db select exception ",
					'data'=>$debug_args,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);
		}
		
		// Update cache
		// ===========================================================
		
		
		if(!$db_result){

			// If the query returned zero results, no further work needs to be done
			return true;
		}
		else {


			// Key or array of keys from the *same branch* and *same tree*
			// --------------------------------------------------------------

			if( $user_id && $tree && $branch && $key ){

				// Load the persistent cache record for the user_id into the class's cache array

				if(!$skip_load){

					try{
						$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
					}
					catch(FOX_exception $child) {

						throw new FOX_exception(array(
								'numeric'=>2,
								'text'=>"Cache get exception",
								'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							)
						);
					}

				}

				// Overwrite the class cache array with the data fetched in the db query

				foreach( $db_result[$user_id][$tree][$branch] as $key_name => $val ){

					$this->cache[$user_id]["keys"][$tree][$branch][$key_name] = $val["val"];
				}


				// Overwrite the persistent cache record with the updated class cache array

				try{
					$cache_ok = $rad->cache->set("FOX_uData", $user_id, $this->cache[$user_id]);
				}
				catch(FOX_exception $child){				

					throw new FOX_exception(array(
							'numeric'=>3,
							'text'=>"Cache set exception",
							'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
							    )
					);
				}

			}

			// Branch or array of branches from the *same* tree
			// --------------------------------------------------------------

			elseif( $user_id && $tree && $branch && !$key ){

				// Load the persistent cache record for the user_id into the class's cache array

				if(!$skip_load){

					try{
						$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
					}
					catch(FOX_exception $child){
						throw new FOX_exception(array(
								'numeric'=>4,
								'text'=>"Cache get exception",
								'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							    )
						);
					}
				}

				// Overwrite the class cache array with the data fetched in the db query

				foreach( $db_result[$user_id][$tree] as $branch_name => $keys){

				    foreach( $keys as $key_name => $val ){

					    $this->cache[$user_id]["keys"][$tree][$branch_name][$key_name] = $val["val"];
				    }

				    // Set the branch cache
				    $this->cache[$user_id]["branch"][$tree][$branch_name] = true;

				}

				// Overwrite the persistent cache record with the updated class cache array
				try{
					$cache_ok = $rad->cache->set("FOX_uData", $user_id, $this->cache[$user_id]);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(
							'numeric'=>5,
							'text'=>"Cache set exception",
							'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						    )
					);
				}

			}

			// Tree or array of trees
			// --------------------------------------------------------------

			elseif( $user_id && $tree && !$branch && !$key ){

				// Load the persistent cache record for the user_id into the class's cache array

				if(!$skip_load){
					try{
						$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
					}
					catch(FOX_exception $child){
						throw new FOX_exception(array(
								'numeric'=>6,
								'text'=>"Cache get exception",
								'data'=>array("user_id"=>$user_id, "tree"=>$tree),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							    )
						);
					}
				}

				// Overwrite the class cache array with the data fetched in the db query

				foreach ($db_result[$user_id] as $tree_name => $branches){

				    foreach( $branches as $branch_name => $keys){

					foreach( $keys as $key_name => $val ){

						$this->cache[$user_id]["keys"][$tree_name][$branch_name][$key_name] = $val["val"];
					}
				    }

				    // Set the tree cache
				    $this->cache[$user_id]["tree"][$tree_name] = true;

				}

				// Overwrite the persistent cache record with the updated class cache array
				try{
					$cache_ok = $rad->cache->set("FOX_uData", $user_id, $this->cache[$user_id]);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(
							'numeric'=>7,
							'text'=>"Cache set exception",
							'data'=>array("user_id"=>$user_id, "tree"=>$tree),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						    )
					);
				}

			}

			// User or array of users
			// --------------------------------------------------------------
			elseif( $user_id && !$tree && !$branch && !$key ){

				$cache_ok = true;

				foreach($db_result as $single_user => $trees){

					// Load the persistent cache record for the user_id into the class's cache array

					if(!$skip_load){
						try{
							$this->cache[$single_user] = $rad->cache->get("FOX_uData", $single_user, $valid);
						}
						catch(FOX_exception $child){
							throw new FOX_exception(array(
									'numeric'=>8,
									'text'=>"Cache get exception",
									'data'=>array("user_id"=>$user_id),
									'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
									'child'=>$child
								    )
							);
						}

					}

					// Overwrite the class cache array with the data fetched in the db query

					foreach ($trees as $tree_name => $branches){

					    foreach( $branches as $branch_name => $keys){

						foreach( $keys as $key_name => $val ){

						    $this->cache[$single_user]["keys"][$tree_name][$branch_name][$key_name] = $val["val"];
						}
					    }
					}

					// Set the all_cached flag
					$this->cache[$single_user]["all_cached"] = true;

					// Overwrite the persistent cache record with the updated class cache array
					try{
						$cache_ok = $rad->cache->set("FOX_uData", $single_user, $this->cache[$single_user]);
					}
					catch(FOX_exception $child){
						throw new FOX_exception(array(
								'numeric'=>9,
								'text'=>"Cache set exception",
								'data'=>array("user_id"=>$user_id),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							    )
						);
					}

				}

			}

			return true;


		}


	}


	/**
	 * Loads all data for one or more $user_id's into the data cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int. Multiple user id's as array of ints.
	 * @return bool | False on failure. True on success.
	 */

	public function loadUser($user_id){

		try{
			return $this->load($user_id, $tree=null, $branch=null, $key=null, $skip_load=false);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"Load exception",
					    'data'=>array("user_id"=>$user_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					)
			);
		}
	}


	/**
	 * Loads one or more trees for a single user_id into the data cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int.
	 * @param int/array $tree| Single tree id as int. Multiple tree id's as array of ints.
	 * @return bool | False on failure. True on success.
	 */

	public function loadTree($user_id, $tree){

		try{
		    return $this->load($user_id, $tree, $branch=null, $key=null, $skip_load=false);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"Load exception",
					    'data'=>array("user_id"=>$user_id, "tree"=>$tree),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					)
			);
		}
	}


	/**
	 * Loads one or more branches for a single tree and user_id into the data cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int.
	 * @param int/array $tree| Single tree id as int.
	 * @param int/array $branch | Single branch id as int. Multiple branch id's as array of ints.
	 * @return bool | False on failure. True on success.
	 */

	public function loadBranch($user_id, $tree, $branch){

		try{
		    return $this->load($user_id, $tree, $branch, $key=null, $skip_load=false);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"Load exception",
					    'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					)
			);
		}
	}


	/**
	 * Loads one or more keys for a single branch, tree and user_id into the data cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int.
	 * @param int/array $tree| Single tree id as int.
	 * @param int/array $branch | Single branch id as int.
	 * @param string/array $key | Single key name as string. Multiple key names as array of strings.
	 * @return bool | False on failure. True on success.
	 */

	public function loadKey($user_id, $tree, $branch, $key){

		try{
		    return $this->load($user_id, $tree, $branch, $key, $skip_load=false);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"Load exception",
					    'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					)
			);
		}
	}


	/**
	 * Fetches a key, branch, tree, or an entire user's keystore from the key cache.
	 *
	 * If an object is not in the cache yet, it will be retrieved from the database
	 * and added to the cache. Multiple items in the *lowest level group* specified
	 * can be retrieved in a single query by passing their names or id's as an array.
	 *
	 * WRONG: get( $id=1, $tree = array(3, 6, 7, 2), $branch=7, $key="gender")
	 *                    ^^^^^^^^^^^^^^^^^^^^^^^^
	 * RIGHT: get( $id=1, $tree=2, $branch=7, $key=array("fans","likes","gender")
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single $user_id as int. Multiple as array of ints.
	 * @param int/array $tree | Single $tree as int. Multiple as array of ints.
	 * @param int/array $branch | Single $branch as int. Multiple as array of ints.
	 * @param string/array $key | Single key name as string. Multiple as array of strings.
	 * @param bool &$valid | True if the object exists. False if not.
	 * @param array &$error | Array containing numeric and text error information
	 * @return bool | False on failure. True on success.
	 */

	public function get($user_id, $tree=null, $branch=null, $key=null, &$valid=null){

		global $rad;

		$db = new FOX_db();
		$args = array();

		// Key or group of keys
		// =============================
		if($user_id && $tree && $branch && $key){


			if( is_array($user_id ) || is_array($tree ) || is_array($branch) ){

				throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"Attempted to pass multiple user id's, tree's, or branches when specifying key",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>null
					    )
				);
			}

			// If the user_id is not present in the class cache array, try to load it
			// from the persistent cache
			if(!$this->cache[$user_id]){

				try{
					$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(
							'numeric'=>2,
							'text'=>"Cache get exception",
							'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						    )
					);
				}
			}

			// Single key
			if( !is_array($key) ){
				$single = true;
				$key = array($key);
			}
			else {
				$single = false;
			}


			// Find all the keys that have been requested but are not in the cache
			$missing_keys = array();

			$branch_cached = FOX_sUtil::keyTrue($branch, $this->cache[$user_id]["branch"][$tree]);
			$tree_cached = FOX_sUtil::keyTrue($tree, $this->cache[$user_id]["tree"]);
			$user_cached = FOX_sUtil::keyTrue("all_cached", $this->cache[$user_id]);

			if( !$branch_cached && !$tree_cached && !$user_cached ){

				foreach($key as $key_name){

					if( !FOX_sUtil::keyExists($key_name, $this->cache[$user_id]["keys"][$tree][$branch]) ){

						$missing_keys[] = $key_name;
					}

				}
				unset($key_name);

			}

			// Load missing keys
			if( count($missing_keys) != 0 ){

				try{
					$this->load($user_id, $tree, $branch, $missing_keys, $skip_load=true);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(	
						    'numeric'=>3,
						    'text'=>"Load exception",
						    'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "missing_keys"=>$missing_keys),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);

				}
			}

			$result = array();

			// Build an array of the requested keys
			foreach($key as $key_name){

				if( FOX_sUtil::keyExists($key_name, $this->cache[$user_id]["keys"][$tree][$branch]) ){

					$result[$key_name] = $this->cache[$user_id]["keys"][$tree][$branch][$key_name];
				}
				else {
					$result[$key_name] = null;
				}

			}
			unset($key_name);

			// Only set the $valid flag true if every requested key was successfully fetched
			if( count($result) == count($key) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one key was requested, and the key was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single == true) && (count($result) == 1) ){

				$result = $result[$key[0]];
			}

			return $result;

		}

		// Branch or group of branches
		// =============================

		elseif($user_id && $tree && $branch && !$key){


			if( is_array($user_id ) || is_array($tree) ){

			    throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Attempted to pass multiple user id's or tree's when specifying branch",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
					)
				);
			}

			// If the user_id is not present in the class cache array, try to load it
			// from the persistent cache
			if(!$this->cache[$user_id]){

				try{
					$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(				
							'numeric'=>5,
							'text'=>"Cache get exception",
							'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						    )
					);
				}

			}

			// Single branch
			if( !is_array($branch) ){
				$single = true;
				$branch = array($branch);
			}
			else {
				$single = false;
			}


			// Find all the branches that have been requested but are not in the cache
			$missing_branches = array();

			foreach($branch as $branch_name){

				$branch_cached = FOX_sUtil::keyTrue($branch_name, $this->cache[$user_id]["branch"][$tree]);
				$tree_cached = FOX_sUtil::keyTrue($tree_name, $this->cache[$user_id]["tree"]);
				$user_cached = FOX_sUtil::keyTrue("all_cached", $this->cache[$user_id]);

				if( !$branch_cached && !$tree_cached && !$user_cached ){

					$missing_branches[] = $branch_name;
				}

			}
			unset($branch_name);

			// Load missing branches
			if( count($missing_branches) != 0 ){

				try{
					$this->load($user_id, $tree, $missing_branches, null, $skip_load=true);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(	
						    'numeric'=>6,
						    'text'=>"Load exception",
						    'data'=>array("user_id"=>$user_id, "tree"=>$tree, "missing_branches"=>$missing_branches),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);

				}
			}

			$result = array();

			// Build an array of the requested branches
			foreach($branch as $branch_name){

				if( FOX_sUtil::keyExists($branch_name, $this->cache[$user_id]["keys"][$tree]) ){

					$result[$branch_name] = $this->cache[$user_id]["keys"][$tree][$branch_name];
				}
				else {
					$result[$branch_name] = null;
				}

			}
			unset($branch_name);

			// Only set the $valid flag true if every requested branch was successfully fetched
			if( count($result) == count($branch) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one branch was requested, and the branch was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single == true) && (count($result) == 1) ){

				$result = $result[$branch[0]];
			}

			return $result;

		}

		// Tree or group of trees
		// =============================
		elseif($user_id && $tree && !$branch && !$key){

			if( is_array($user_id ) ){

				throw new FOX_exception(array(
					    'numeric'=>7,
					    'text'=>"Attempted to pass multiple user id's when specifying tree",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>null
				    )
				);
			}

			// If the user_id is not present in the class cache array, try to load it
			// from the persistent cache
			if(!$this->cache[$user_id]){

				try{
					$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(	
							'numeric'=>8,
							'text'=>"Cache get exception",
							'data'=>array("user_id"=>$user_id, "tree"=>$tree),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						    )
					);
				}

			}

			// Single tree
			if( !is_array($tree) ){
				$single = true;
				$tree = array($tree);
			}
			else {
				$single = false;
			}

			// Find all the trees that have been requested but are not in the cache
			$missing_trees = array();

			foreach($tree as $tree_name){

				$tree_cached = FOX_sUtil::keyTrue($tree_name, $this->cache[$user_id]["tree"]);
				$user_cached = FOX_sUtil::keyTrue("all_cached", $this->cache[$user_id]);

				if( !$tree_cached && !$user_cached ){

					$missing_trees[] = $tree_name;
				}

			}
			unset($tree_name);

			// Load missing trees
			if( count($missing_trees) != 0 ){

				try{
					$this->load($user_id, $missing_trees, null, null, $skip_load=true);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(	
						    'numeric'=>9,
						    'text'=>"Load exception",
						    'data'=>array("user_id"=>$user_id, "missing_trees"=>$missing_trees),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);

				}
			}

			$result = array();

			// Build an array of the requested trees
			foreach($tree as $tree_name){

				if( FOX_sUtil::keyExists($tree_name, $this->cache[$user_id]["keys"]) ){

					$result[$tree_name] = $this->cache[$user_id]["keys"][$tree_name];
				}
				else {
					$result[$tree_name] = null;
				}

			}
			unset($tree_name);

			// Only set the $valid flag true if every requested tree was successfully fetched
			if( count($result) == count($tree) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one tree was requested, and the tree was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single == true) && (count($result) == 1) ){

				$result = $result[$tree[0]];
			}

			return $result;


		}

		// Entire keystore for one or more users
		// =====================================
		elseif($user_id && !$tree && !$branch && !$key) {

			if(!is_array($user_id)){
				$single = true;
				$user_id = array($user_id);
			}
			else {
				$single = false;
			}

			// Find all the user_id's that have been requested but are not in the cache
			$missing_trees = array();

			foreach($user_id as $current_user_id){

				if(!FOX_sUtil::keyTrue("all_cached", $this->cache[$current_user_id]) ){

					$missing_users[] = $current_user_id;
				}

			}
			unset($current_user_id);


			// Load missing user_id's
			if( count($missing_users) != 0 ){

				try{
					$this->load($missing_users, null, null, null, $skip_load=true);
				}
				catch(FOX_exception $child){
					throw new FOX_exception(array(	
						    'numeric'=>10,
						    'text'=>"Load exception",
						    'data'=>array("missing_users"=>$missing_users),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

			}

			$result = array();

			// Build an array of the requested users
			foreach($user_id as $current_user_id){

				if( FOX_sUtil::keyExists($current_user_id, $this->cache) ){

					$result[$current_user_id] = $this->cache[$current_user_id]["keys"];
				}
				else {
					$result[$current_user_id] = null;
				}

			}
			unset($current_user_id);

			// Only set the $valid flag true if every requested tree was successfully fetched
			if( count($result) == count($user_id) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one user_id was requested, and the user_id was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single = true) && (count($result) == 1) ){

				$result = $result[$user_id[0]];
			}

			return $result;

		}

		// Bad input
		// =============================
		else {

			throw new FOX_exception(array(
				'numeric'=>10,
				'text'=>"Bad input args",
				'data'=>array ("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
				    )
			);
		}

	}


	/**
	 * Returns all data for one or more $user_id's.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int. Multiple user id's as array of ints.
	 * @param bool &$valid | True if user_id exists. False if not.
	 * @return bool | False on failure. True on success.
	 */

	public function getUser($user_id, &$valid=null){

		try{
			return $this->get($user_id, null, null, null, $valid);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(	
				'numeric'=>1,
				'text'=>"get exception",
				'data'=>array ("user_id"=>$user_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);
		}
	}


	/**
	 * Returns one or more API data arrays for a single user_id.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $user_id | Single user id as int.
	 * @param int/array $tree| Single tree id as int. Multiple tree id's as array of ints.
	 * @param bool &$valid | True if tree exists. False if not.
	 * @return bool | False on failure. True on success.
	 */

	public function getTree($user_id, $tree, &$valid=null){

		try{
			return $this->get($user_id, $tree, null, null, $valid);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(	
				'numeric'=>1,
				'text'=>"get exception",
				'data'=>array ("user_id"=>$user_id, "tree"=>$tree),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);
		}
	}


	/**
	 * Returns one or more branch data arrays from a single tree belonging to a single user id
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | Single user id as int.
	 * @param int $tree| Single tree id as int.
	 * @param int/array $branch | Single branch id as int. Multiple branch id's as array of ints.
	 * @param bool &$valid | True if branch exists. False if not.
	 * @return bool | False on failure. True on success.
	 */

	public function getBranch($user_id, $tree, $branch, &$valid=null){

		try{
		    return $this->get($user_id, $tree, $branch, null, $valid);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(	
				'numeric'=>1,
				'text'=>"get exception",
				'data'=>array ("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);
		}
	}


	/**
	 * Returns one or more keys for a single branch within a single tree belonging to a single user id.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | Single user id as int.
	 * @param int $tree| Single tree id as int.
	 * @param int $branch | Single branch id as int.
	 * @param string/array $key | Single key name as string. Multiple key names as array of strings.
	 * @param bool &$valid | True if key exists. False if not.
	 * @return bool | False on failure. True on success.
	 */

	public function getKey($user_id, $tree, $branch, $key, &$valid=null){

		try{
			return $this->get($user_id, $tree, $branch, $key, $valid);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(	
				'numeric'=>1,
				'text'=>"get exception",
				'data'=>array ("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);
		}
	}


	/**
	 * Creates a new key or updates an existing key.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | User ID
	 * @param int $tree | API id
	 * @param int $branch | App id
	 * @param bool/int/float/string/array/obj $key | Key value
	 * @return bool | False on failure. True on success.
	 */

	public function setKey($user_id, $tree, $branch, $key, $val){

		$data = array(
				array(	"user_id"=>$user_id,
					"tree"=>$tree,
					"branch"=>$branch,
					"key"=>$key,
					"val"=>$val
				)
		);
		try{
			$result = self::setKeyMulti($data);
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(	
				'numeric'=>1,
				'text'=>"setKeyMulti exception",
				'data'=>array ("data"=>$data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);
		}

		return $result;

	}


	/**
	 * Creates or updates one or more keys.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
         * @param array $data | Array of row arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int $user_id | user id
	 *	    => VAL @param int $tree | tree id
	 *	    => VAL @param int $branch | branch id
	 *	    => VAL @param string $key | key name
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value

	 * @return bool | False on failure. True on success.
	 */

	public function setKeyMulti($data){

		global $rad;
		$db = new FOX_db(); //var_dump($data);

		$cache_update = array();
		$insert_data = array();

		// Process each row
		// ===========================================================

		foreach( $data as $row ){

			if( empty($row["user_id"]) )
			{
				throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"Empty user_id",
					    'data'=>$row,
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>null
					    )
				);
			}

			if( empty($row["tree"]) )
			{
				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"Empty tree name",
					    'data'=>$row,
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>null
					    )
				);
			}

			if( empty($row["branch"]) )
			{
				throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Empty branch name",
					'data'=>$row,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
					    )
				);
			}

			if( empty($row["key"]) )
			{

				throw new FOX_exception(array(
					'numeric'=>4,
					'text'=>"Empty key name",
					'data'=>$row,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
					    )
				);
			}

			// Expand the key into a heirarchical array
			$update_data[$row["user_id"]][$row["tree"]][$row["branch"]][$row["key"]] = $row["val"];

		}
		unset($row);


		// Load the persistent cache records for all the module_id's into the temp class cache array
		try{
			$update_cache = $rad->cache->getMulti("FOX_uData", array_keys($update_data));
		}
		catch(FOX_exception $child){
			
			throw new FOX_exception(array(
					'numeric'=>5,
					'text'=>"Cache get error",
					'data'=>$update_data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);
		    
		}

		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		try{
			$begin_ok = $db->beginTransaction();
		}
		catch(FOX_exception $child){
			
			throw new FOX_exception(array(
					'numeric'=>6,
					'text'=>"Couldn't initiate transaction",
					'data'=>$data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);
		    
		}

		foreach( $update_data as $user_id => $trees ){

			foreach( $trees as $tree => $branches ){

				foreach( $branches as $branch => $keys ){

					foreach( $keys as $key => $val ){

						$insert_data = array(
									"user_id"=>$user_id,
									"tree"=>$tree,
									"branch"=>$branch,
									"node"=>$key,
									"val"=>$val
						);

						try{
							$query_ok = $db->runIndateQuery(self::$struct, $insert_data, $columns=null);
						}
						catch(FOX_exception $child){

							try{
								$db->rollbackTransaction();
							}
							catch(FOX_exception $child){

								throw new FOX_exception(array(
										'numeric'=>8,
										'text'=>"rollbackTransaction",
										'data'=>$data,
										'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
										'child'=>$child
									    )
								);

							}								
							
							throw new FOX_exception(array(
								'numeric'=>7,
								'text'=>"Error while writing to the database",
								'data'=>$insert_data,
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
								    )
							);							

						}

						// Overwrite the class cache array with the data set in the db query
						$update_cache[$user_id]["keys"][$tree][$branch][$key] = $val;

					}
					unset($key, $val);
				}
				unset($branch, $key);
			}
			unset($tree, $branches);
		}
		unset($user_id, $trees);


		try{
			$commit_ok = $db->commitTransaction();
		}
		catch(FOX_exception $child){

		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			throw new FOX_exception(array(
				'numeric'=>8,
				'text'=>"Error commiting transaction to database",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			    )
			);
		}

		// Overwrite the persistent cache records with the temp class cache array items
		try{
			$cache_ok = $rad->cache->setMulti("FOX_uData", $update_cache);
		}
		catch(BPN_exception $child){
		    
		    throw new FOX_exception(array(
				'numeric'=>9,
				'text'=>"Cache set error",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				)
			);
			    		    
		}

		// Write the temp class cache array items to the class cache array
		foreach($update_cache as $key => $val){

			$this->cache[$key] = $val;
		}
		unset($key, $val);

		return true;

	}


	/**
	 * Drops one or more keys from the database and cache for a single branch within a single tree
	 * belonging to a single user id.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | ID of the user
	 * @param int $tree | ID of the tree
	 * @param int $branch | ID of the branch
	 * @param string $key | name of the key
	 * @param array &$error | Array containing numeric and text error information
	 * @return bool | False on failure. True on success.
	 */

	public function dropKey($user_id, $tree, $branch, $key, &$error=null) {

		global $rad;
		$db = new FOX_db();

		if( empty($user_id) || empty($tree) || empty($branch) || empty($key) ){

			throw new FOX_exception(array(
					'numeric'=>1,
					'text'=>"Empty control parameter",
					'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				    )
			);
		}

		if( is_array($user_id ) || is_array($tree ) || is_array($branch) ){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"Attempted to pass multiple user id's, tree's, or branches",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
				    )
			);
		}

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branch),
				array("col"=>"node", "op"=>"=", "val"=>$key)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){
			
			throw new FOX_exception(array(
				 'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);		    
		}


		if($rows_changed){	// Only update the cache if rows were deleted

			// Load the persistent cache record for the user_id into the class's cache array
			try{
				$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
						'numeric'=>4,
						'text'=>"Cache get error",
						'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					    )
				);
			}

			// Remove the deleted keys from the class cache array
			if( is_array($key) ){

				foreach($key as $key_name){
					unset($this->cache[$user_id]["keys"][$tree][$branch][$key_name]);
				}
				unset($key_name);
			}
			else {
				unset($this->cache[$user_id]["keys"][$tree][$branch][$key]);
			}

			self::compactCache($user_id);


			// Overwrite the persistent cache record with the updated class cache array
			try{
			    $cache_ok = $rad->cache->set("FOX_uData", $user_id, $this->cache[$user_id]);
			}
			catch(FOX_exception $child){
			    
			    	throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Cache set error",
						'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch, "key"=>$key),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					    )
				);
			}

			if($cache_ok){

				return $rows_changed;
			}

		}
		else{
			// For a delete query, return value "0" could be a perfectly valid query that affected zero rows. So for
			// this function we want to return that value rather than "false".
			return $rows_changed;
		}


	}


	/**
	 * Drops all keys from the database and cache for one or more branchs within a
	 * single tree belonging to a single user id.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | ID of the user
	 * @param int $tree | ID of the tree
	 * @param int $branch | ID of the branch
	 * @param array &$error | Array containing numeric and text error information
	 * @return bool | False on failure. True on success.
	 */

	public function dropBranch($user_id, $tree, $branch, &$error=null) {

		global $rad;
		$db = new FOX_db();

		if( empty($user_id) || empty($tree) || empty($branch) ){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"Empty control parameter",
				    'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
				    )
			);
		}

		if( is_array($user_id ) || is_array($tree ) ){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"Attempted to pass multiple user id's or trees",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
				    )
			);
		}

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branch)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);

		}


		if($rows_changed){	// Only update the cache if rows were deleted

			// Load the persistent cache record for the user_id into the class's cache array
			try{
				$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
			}
			catch(FOX_exception $child){			    
				throw new FOX_exception(array(
						'numeric'=>4,
						'text'=>"Cache get error",
						'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					    )
				);
			}

			// Remove the deleted branch data and cache flags from the class cache array
			if( is_array($branch) ){

				foreach($branch as $branch_name){

					unset($this->cache[$user_id]["keys"][$tree][$branch_name]);
					unset($this->cache[$user_id]["branch"][$tree][$branch_name]);
				}
				unset($branch_name);
			}
			else {
				unset($this->cache[$user_id]["keys"][$tree][$branch]);
				unset($this->cache[$user_id]["branch"][$tree][$branch]);
			}

			self::compactCache($user_id);


			// Overwrite the persistent cache record with the updated class cache array
			try{
				$cache_ok = $rad->cache->set("FOX_uData", $user_id, $this->cache[$user_id]);
			}
			catch(FOX_exception $child){
				throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Cache set error",
						'data'=>array("user_id"=>$user_id, "tree"=>$tree, "branch"=>$branch),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					    )
				);
			}

		}
		else{
			// For a delete query, return value "0" could be a perfectly valid query that affected zero rows. So for
			// this function we want to return that value rather than "false".
			return $rows_changed;
		}


	}



	/**
	 * Drops all keys from the database and cache for one or more trees belonging to
	 * a single user id.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | ID of the user
	 * @param int $tree | ID of the tree
	 * @param array &$error | Array containing numeric and text error information
	 * @return bool | False on failure. True on success.
	 */

	public function dropTree($user_id, $tree, &$error=null) {

		global $rad;
		$db = new FOX_db();

		if( empty($user_id) || empty($tree) ){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"Empty control parameter",
				    'data'=>array("user_id"=>$user_id, "tree"=>$tree),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
				    )
			);
		}

		if( is_array($user_id ) ){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"Attempted to pass multiple user id's",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
				    )
			);
		}

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id),
				array("col"=>"tree", "op"=>"=", "val"=>$tree)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){

		    throw new FOX_exception(array(
				'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("user_id"=>$user_id, "tree"=>$tree),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				)
			);

		}


		if($rows_changed){	// Only update the cache if rows were deleted

			// Load the persistent cache record for the user_id into the class's cache array
			try{
				$this->cache[$user_id] = $rad->cache->get("FOX_uData", $user_id, $valid);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
						'numeric'=>4,
						'text'=>"Cache get exception",
						'data'=>array("user_id"=>$user_id, "tree"=>$tree),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child)
				);
			}

			// Remove the deleted tree data and cache flags from the class cache array
			if( is_array($tree) ){

				foreach($tree as $tree_name){
					unset($this->cache[$user_id]["keys"][$tree_name]);
					unset($this->cache[$user_id]["branch"][$tree_name]);
					unset($this->cache[$user_id]["tree"][$tree_name]);
				}
				unset($tree_name);
			}
			else {
				unset($this->cache[$user_id]["keys"][$tree]);
				unset($this->cache[$user_id]["branch"][$tree]);
				unset($this->cache[$user_id]["tree"][$tree]);
			}

			self::compactCache($user_id);


			// Overwrite the persistent cache record with the updated class cache array
			try{
				$cache_ok = $rad->cache->set("FOX_uData", $user_id, $this->cache[$user_id]);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
						'numeric'=>5,
						'text'=>"Cache set exception",
						'data'=>array("user_id"=>$user_id, "tree"=>$tree),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					    )
				);
			}
			return $rows_changed;

		}
		else{
			// For a delete query, return value "0" could be a perfectly valid query that affected zero rows. So for
			// this function we want to return that value rather than "false".
			return $rows_changed;
		}

		
	}


	/**
	 * Drops all data for one or more user_id's from the database and cache.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | ID of the user
	 * @param array &$error | Array containing numeric and text error information
	 * @return bool | False on failure. True on success.
	 */

	public function dropUser($user_id, &$error=null) {

		global $rad;
		$db = new FOX_db();

		if( empty($user_id)){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Empty user_id",
				'data'=>array("user_id"=>$user_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
				    )
			);
		}

		$args = array(
				array("col"=>"user_id", "op"=>"=", "val"=>$user_id)
		);

		try{
		    $rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){
		    
			    throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("user_id"=>$user_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);

		}

		if($rows_changed){	// Only update the cache if rows were deleted

			if(!is_array($user_id)){
				$user_id = array($user_id);
			}

			foreach($user_id as $user){

				// Remove user_id from the class's cache array
				unset($this->cache[$user]);

				// Drop user_id from cache
				try{
				    $cache_ok = $rad->cache->del("FOX_uData", $user);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
							'numeric'=>3,
							'text'=>"Cache delete error",
							'data'=>array("user_id"=>$user_id, "user"=>$user),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						    )
					);
				}

			}
			unset($user);

		}

		// For a delete query, return value "0" could be a perfectly valid query that affected zero rows. So for
		// this function we want to return that value rather than "false".
		return $rows_changed;

	}


	/**
	 * Drops one or more keys from the database and cache for a single branch within a single
	 * tree FOR ALL USERS ON THE SITE. Generally used when uninstalling or upgrading modules, or for
	 * managing admin-assigned data objects, notes about users, etc.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $tree | id of the tree
	 * @param int $branch | id of the branch
	 * @param string/array $key | key name as string. Multiple names as array of strings.
	 * @return bool | False on failure. True on success.
	 */

	public function dropSiteKey($tree, $branch, $key) {

		global $rad;
		$db = new FOX_db();

		if( empty($tree) || empty($branch) || empty($key) ){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("tree"=>$tree, "branch"=>$branch, "key"=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
				    )
			);
		}

		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branch),
				array("col"=>"node", "op"=>"=", "val"=>$key)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("tree"=>$tree, "branch"=>$branch, "key"=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);

		}

		// Since this operation affects *all* user_id's, we have to flush the cache
		try{
			$cache_ok = self::flushCache();
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Cache flush exception",
					'data'=>array("tree"=>$tree, "branch"=>$branch, "key"=>$key),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);		    
		       
		}

		if($cache_ok){

			return $rows_changed;
		}

	}


	/**
	 * Drops all keys from the database and cache for a single branch within a single tree
	 * FOR ALL USERS ON THE SITE. Generally used when uninstalling or upgrading a module.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $tree | ID of the tree
	 * @param int $branch | ID of the branch
	 * @return bool | False on failure. True on success.
	 */

	public function dropSiteBranch($tree, $branch) {

		global $rad;
		$db = new FOX_db();

		if( empty($tree) || empty($branch) ){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("tree"=>$tree, "branch"=>$branch),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
				    )				 
			);
		}

		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branch)
		);

		try{
			$rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("tree"=>$tree, "branch"=>$branch),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);

		}

		// Since this operation affects *all* user_id's, we have to flush the cache
		try{
			$cache_ok = self::flushCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Cache flush error",
					'data'=>array("tree"=>$tree, "branch"=>$branch),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);
		}
		
		if($cache_ok){

			return $rows_changed;	
		}
		


	}


	/**
	 * Drops all keys from the database and cache for a single tree for ALL USERS ON
	 * THE SITE. Generally used when uninstalling or upgrading an API.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $tree | ID of the tree
	 * @return bool | False on failure. True on success.
	 */

	public function dropSiteTree($tree) {

		global $rad;
		$db = new FOX_db();

		if( empty($tree) ){

			    throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Empty tree",
				'data'=>array("tree"=>$tree),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
				)
			);
		}

		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree)
		);

		try{
		    $rows_changed = $db->runDeleteQuery(self::$struct, $args, $ctrl=null);
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("tree"=>$tree),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);
				
		}

		// Since this operation affects *all* user_id's, we have to flush the cache
		try{
		    $cache_ok = self::flushCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
					'numeric'=>3,
					'text'=>"Cache flush error",
					'data'=>array("tree"=>$tree),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);
		}
		if($cache_ok){

			return $rows_changed;
		}


	}


	/**
	 * Deletes the entire user data store, and flushes the cache. Generally
	 * used for testing and debug.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return bool | False on failure. True on success.
	 */

	public function dropAll(&$error=null) {

		global $rad;
		$db = new FOX_db();

		try{
		    $query_ok = $db->runTruncateTable(self::$struct);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Error while clearing the database",
				'data'=>null,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
				    )
			);

		}


		// Since this operation affects *all* user_id's, we have to flush the cache
		try{
			$cache_ok = self::flushCache();
		}
		catch(FOX_exception $child){
			throw new FOX_exception(array(
					'numeric'=>2,
					'text'=>"Cache flush exception",
					'data'=>null,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				    )
			);
		}

		if($cache_ok){

			return true;
		}
		else {
		    
			return false;
		}



	}




} // End of class FOX_uData



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_FOX_uData(){

	$cls = new FOX_uData();
	
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
add_action( 'rad_install', 'install_FOX_uData', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_FOX_uData(){

	$cls = new FOX_uData();
	
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
add_action( 'rad_uninstall', 'uninstall_FOX_uData', 2 );

?>