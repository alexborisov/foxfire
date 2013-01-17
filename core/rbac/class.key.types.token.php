<?php

/**
 * FOXFIRE USER KEY TYPES
 * This class operates as a central dictionary for all keys used within the system, mapping human-readable
 * key names to 16-bit integers. This dramatically reduces the memory requirements for the user keystore. As
 * with other performance-critical components, this class has a query aggregator and an internal non-persistant
 * cache. Eventually caching will be handled using memcachd or direct IO with a fast SSD drive.
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

class FOX_uKeyType extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class

	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_sys_user_keytypes",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_uKeyType",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "key_id" =>		array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,  "default"=>null, "index"=>"PRIMARY"),
		    "tree" =>		array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,
			"index"=>array("name"=>"tree_branch_name", "col"=>array("tree", "branch", "name"), "index"=>"UNIQUE"), "this_row"=>true),
		    "branch" =>		array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "name" =>		array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "descr" =>		array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>"",		"auto_inc"=>false,  "default"=>null,	"index"=>false)
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
	 * Loads a key, branch, tree, or the entire keystore from the database
	 * into the key cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $tree| The key's tree name
	 * @param string $branch | The key's branch name
	 * @param string $name | The key's name
	 * @param bool $skip_load | If set true, the function will not update the class cache array from
	 *			    the persistent cache before adding data from a db call and saving it
	 *			    back to the persistent cache.
	 * @return bool | False on failure. True on success.
	 */

	public function load($tree=null, $branch=null, $name=null, $skip_load=false){

		global $fox;
		$db = new FOX_db();
		$args = array();

		// Nest the query variables. For example it wouldn't make sense to try and
		// load a branch without specifying a tree

		if($tree){

			$args[] = array("col"=>"tree", "op"=>"=", "val"=>$tree);

			if($branch){

				$args[] = array("col"=>"branch", "op"=>"=", "val"=>$branch);

				if($key){
					$args[] = array("col"=>"name", "op"=>"=", "val"=>$name);
				}
			}
		}

		$columns = array("mode"=>"include", "col"=>array("key_id", "tree", "branch", "name") );

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("tree", "branch", "name") );

		try{
			$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$crtl),
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

		if($result){

			// Update the keys cache
			foreach ($result as $tree_name => $branches){

			    foreach( $branches as $branch_name => $key_names){

				foreach( $key_names as $key_name => $val ){

					$this->cache["keys"][$tree_name][$branch_name][$key_name] = $val;
				}
			    }
			}
			unset($tree_name, $branches, $branch_name, $key_names, $key_name, $val);

			// Update the branch and tree caches. Set $all_cached if entire db was loaded.
			if($tree && $branch && !$name){

				if( !is_array($branch) ){
					$branch = array($branch);
				}

				foreach( $branch as $branch_name ){
					$this->cache["branches"][$branch_name] = true;
				}
				unset($branch_name);

			}
			elseif($tree && !$branch && !$name){

				if( !is_array($tree) ){
					$tree = array($tree);
				}

				foreach( $tree as $tree_name ){
					$this->cache["trees"][$tree_name] = true;
				}
				unset($tree_name);

			}
			elseif(!$tree && !$branch && !$name){

				$this->cache["all_cached"] = true;
			}

			// Write the updated class cache array to the persistent cache
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
	 * Fetches the ID of a single key
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $tree | tree name for key
	 * @param string $branch | branch name for key
	 * @param string $name | name of key
	 * @param bool &$valid | True if the requested key was found in the database. False if not.
	 * @return bool/int | False on failure. Key ID on success.
	 */

	public function getKeyID($tree, $branch, $name, &$valid=null){

		$key = array( "tree"=>$tree, "branch"=>$branch, "name"=>$name);
		$key = array($key);  // Wrap in array to match getKeyIDMulti() format

		try{
			$result = self::getKeyIDMulti($key, $valid);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"FOX_uKeyType getKeyIDMulti exception",
				    'data'=>array("key"=>$key, "valid"=>$valid),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		return $result[$tree][$branch][$name];

	}


	/**
	 * Fetches the ID's of one or more keys.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $keys | one or more arrays, each descrrribing a key to retrieve
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $tree | tree name for key
	 *	    => VAL @param string $branch | branch name for key
	 *	    => VAL @param string/array $name | Single name as string. Multiple as array of strings.
	 *
	 * @param bool &$valid | True if all requested keys were found in the database. False if not.
	 * @return bool/array | False on failure. Array of key ID's on success
	 */

	public function getKeyIDMulti($keys, &$valid=null){

		global $fox;
		$valid = true;

		// Build a list of keys we need to fetch from the db. Remember, the class cache
		// array is loaded from the persistent cache every time the class is instantiated,
		// so if the requested keys are not in the class cache at this point, they will
		// have to be fetched from the db
		// ================================================================================

		$missing_keys = array();

		foreach($keys as $key){

			$tree = $key["tree"];	    // This is easier to understand than using extract($key)
			$branch = $key["branch"];   // because it shows the variable names we're using
			$name = $key["name"];

			if(!is_array($name)){
				$name = array($name);
			}

			foreach( $name as $key_name ){

				if( !FOX_sUtil::keyExists($key_name, $this->cache["keys"][$tree][$branch]) ){

					$missing_keys[$tree][$branch][$key_name] = true;
				}
			}
		}
		unset($key, $tree, $branch, $name, $key_name);


		// Load any missing keys from the database
		// =======================================

		if( count($missing_keys) > 0 ){

			$db = new FOX_db();
			$key_ids = array();

			foreach( $missing_keys as $tree_name => $branches){

				foreach( $branches as $branch_name => $key_arrays){

					foreach( $key_arrays as $key_name => $fake_var){

						$args = array(
								array("col"=>"tree", "op"=>"=", "val"=>$tree_name),
								array("col"=>"branch", "op"=>"=", "val"=>$branch_name),
								array("col"=>"name", "op"=>"=", "val"=>$key_name)
						);

						$columns = array("mode"=>"include", "col"=>array("key_id") );

						$ctrl = array("format"=>"var" );

						try{
							$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
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
						if($result){
							$key_ids[$tree_name][$branch_name][$key_name] = $result;
						}
						else {
							// If the user has requested a non-existent key, set the
							// valid flag to false to indicate a problem
							$valid = false;
						}
					}
				}
			}
			unset($tree_name, $branches, $branch_name, $key_arrays, $key_names);


			// Update the cache
			// ================

			if( count($key_ids) > 0 ){

				// Update the class cache from the persistent cache
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
				// Add keys fetched from the database to it
				foreach($key_ids as $tree_name => $branches){

				    foreach( $branches as $branch_name => $key_names){

					foreach( $key_names as $key_name => $val ){

						$this->cache["keys"][$tree_name][$branch_name][$key_name] = $val;
					}
				    }
				}
				unset($tree_name, $branches, $branch_name, $key_names, $key_name, $val);

				// Write the updated class cache array to the persistent cache
				try{
					self::saveCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>3,
						    'text'=>"daveCache exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
			}

		}


		// Build the results array
		// =======================

		$result = array();

		foreach($keys as $key){

			$tree = $key["tree"];	    // This is easier to understand than using extract($key)
			$branch = $key["branch"];   // because it shows the variable names we're using
			$name = $key["name"];

			$result[$tree][$branch][$name] = $this->cache["keys"][$tree][$branch][$name];

		}
		unset($key, $tree, $branch, $name);

		return $result;
	}


	/**
	 * Fetches all fields for one or more keys. This function is NOT cached, and should
	 * only be used in admin editing screens, where all of the key's data fields are
	 * required.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $key_id | id of key. Multiple id's as array of ints.
	 * @return bool/int | False on failure. Array of arrays of key rows on success.
	 */

	public function getKeyData($key_id){

		$db = new FOX_db();

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("key_id"));

		try{
			$result = $db->runSelectQueryCol(self::$struct, "key_id", "=", $key_id, $columns=null, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array( "col"=>"key_id", "op"=>"=", "val"=>$key_id,"ctrl"=>$ctrl, ),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		return $result;
	}


	/**
         * Returns an array of keytype objects based on user supplied parameters. This function
	 * is NOT cached, and should only be used in admin editing screens, where all of the key's
	 * data fields are required.
         *
         * @version 0.1.9
         * @since 0.1.9
         *
	 * @param array $args | @see FOX_db::runSelectQuery() for array structure
	 * @param bool/array $columns | @see FOX_db::runSelectQuery() for array structure
	 * @param array $ctrl | @see FOX_db::runSelectQuery() for array structure
         * @return bool/int/array | False on failure. Int on count. Array of objects on success.
         */

	public function query($args=null, $columns=null, $ctrl=null) {


		$ctrl_default = array(
			"format"=>"array_array"
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);

		$db = new FOX_db();
		try{
			$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array( "args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		return $result;
	}



	/**
	 * Creates a single key
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $tree | tree name for key (max 32 chars)
	 * @param string $branch | branch name for key (max 32 chars)
	 * @param string $name | name for key (max 32 chars)
	 * @param string $descr | admin description for key (max 255 chars)
	 * @return bool/int | False on failure. True on success.
	 */

	public function createKey($tree, $branch, $name, $descr){

		$key = array( "tree"=>$tree, "branch"=>$branch, "name"=>$name, "descr"=>$descr);
		$key = array($key);  // Wrap in array to match createKeyMulti() format

		try{
		    $result = self::createKeyMulti($key);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"FOX_uKeyType createKeyMulti exception",
				    'data'=>array( "key"=>$key),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		return $result;
	}


	/**
	 * Creates a new key
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $data | one or more arrays, each descrribing a key to create
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $tree | tree name for key (max 32 chars)
	 *	    => VAL @param string $branch | branch name for key (max 32 chars)
	 *	    => VAL @param string $name | name for key (max 32 chars)
	 *	    => VAL @param string $descr | admin description for key (max 255 chars)
	 *
	 * @return bool | False on failure. True on success.
	 */

	public function createKeyMulti($data){

		global $fox;
		$db = new FOX_db();

		// Make sure that none of the keys to be added already exist
		// =========================================================

		$add_keys = array();

		foreach($data as $key){

			$tree = $key["tree"];	    // This is easier to understand than using extract($key)
			$branch = $key["branch"];   // because it shows the variable names we're using
			$name = $key["name"];
			$descr = $key["descr"];

			$add_keys[$tree][$branch][$name]["descr"] = $descr;
		}
		unset($key, $tree, $branch, $name);

		$add_data = array();

		foreach($add_keys as $tree_name => $branches){

			foreach( $branches as $branch_name => $key_arrays){

				$key_names = array_keys($key_arrays);	// The foreach() function provides the full "key_name"=>"value"
									// pair in $key_arrays, so we need to use array_keys() to extract
									// the key names
				$args = array(
						array("col"=>"tree", "op"=>"=", "val"=>$tree_name),
						array("col"=>"branch", "op"=>"=", "val"=>$branch_name),
						array("col"=>"name", "op"=>"=", "val"=>$key_names)
				);

				$columns = array("mode"=>"include", "col"=>array("key_id") );
				$ctrl = array("format"=>"col");

				try{
					$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>1,
						    'text'=>"DB select exception",
						    'data'=>array( "args"=>$args, "columns"=>$columns,"ctrl"=>$ctrl, ),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// If there is no DB entry, we can add the key to our $add_keys array
				if(!$result){

					foreach($key_arrays as $key_name => $vars){

						$add_data[] = array("tree"=>$tree_name, "branch"=>$branch_name, "name"=>$key_name, "descr"=>$vars["descr"] );
					}
				}

				// If the key exists in the DB, for now we'll crash with an error, but we can do other
				// things once we build an error management system
				else{

					throw new FOX_exception(array(
						    'numeric'=>1,
						    'text'=>"FOX_uKeyType::createKeyMulti - Attempted to create a key that is already in the database.",
						    'data'=>array( "tree"=>$tree_name, "branch"=>$branch_name, "key"=>$key_name ),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>null
						    )
					);
				}
			}
		}
		unset($tree_name, $branches, $branch_name, $key_arrays, $key_names, $key_name, $vars);


		// Add the new keys to the database
		// ================================
		try{
			$result = $db->runInsertQueryMulti(self::$struct, $add_data, $columns=null, $ctrl=null);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB insert exception",
				    'data'=>array( "add_data"=>$add_date),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// NOTE: Because we add all keys as a single query, we cannot add their key_id's to the cache
		// because 1) mySQL only returns an insert ID when the table's primary key is a single auto
		// increment column, and 2) it only does it for single insert queries.

		return $result;
	}



	/**
	 * Edits fields for an existing key, given the key's key_id
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $data | one or more arrays, each descrribing a key to create
	 *	=> VAL @param int $key_id | key_id for the key to edit
	 *	=> VAL @param string $tree | tree name for key (max 32 chars)
	 *	=> VAL @param string $branch | branch name for key (max 32 chars)
	 *	=> VAL @param string $name | name for key (max 32 chars)
	 *	=> VAL @param string $descr | admin description for key (max 255 chars)
	 *
	 * @return bool | False on failure. True on success.
	 */

	public function editKey($data){

		global $fox;
		$db = new FOX_db();

		// Get the column values for the current key
		// =========================================

		// Trap missing $key_id
		if(!$data["key_id"]){
			return false;
		}

		$columns = array("mode"=>"exclude", "col"=>array("key_id") );
		$ctrl = array("format"=>"row");

		try{
			$old = $db->runSelectQueryCol(self::$struct, "key_id", "=", $data["key_id"], $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("col"=>"key_id", "op"=>"=", "val"=>$data["key_id"], "columns"=>$columns,"ctrl"=>$ctrl ),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		// If the key's $tree, $branch, or $name fields are being modified, check that a
		// key with the new combination does not already exist in the database
		// ===================================================================================

		$args = array();

		// Tree
		// ==========
		if( $data["tree"] && ($old["tree"] != $data["tree"]) ){

			$args[] = array("col"=>"tree", "op"=>"=", "val"=>$data["tree"]);
			$dupe_check_required = true;
		}
		else {
			$args[] = array("col"=>"tree", "op"=>"=", "val"=>$old["tree"]);
		}

		// Branch
		// ==========
		if( $data["branch"] && ($old["branch"] != $data["branch"]) ){

			$args[] = array("col"=>"branch", "op"=>"=", "val"=>$data["branch"]);
			$dupe_check_required = true;
		}
		else {
			$args[] = array("col"=>"branch", "op"=>"=", "val"=>$old["branch"]);
		}

		// Name
		// ==========
		if( $data["name"] && ($old["name"] != $data["name"]) ){

			$args[] = array("col"=>"name", "op"=>"=", "val"=>$data["name"]);
			$dupe_check_required = true;
		}
		else {
			$args[] = array("col"=>"name", "op"=>"=", "val"=>$old["name"]);
		}


		if($dupe_check_required){

			$ctrl = array("count"=>true, "format"=>"var");
			try{
				$key_exists = $db->runSelectQuery(self::$struct, $args, $columns=null, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"DB select exception",
					    'data'=>array("args"=>$args,"ctrl"=>$ctrl ),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}


		// If possible, update the key in the database
		// ==========================================================

		if($key_exists){

			// Changes to the "Tree", "Branch" or "Name" fields would create a
			// collision with a key that already exists in the database.
			throw new FOX_exception(array(
				    'numeric'=>3,
				    'text'=>"FOX_uKeyType::editKey - Attempted to rename a key to a value that would create a collission with an existing key.",
				    'data'=>array("tree"=> $data['tree'], "branch"=> $data['branch'], "key"=> $data['name'] ),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
				    )
			);


		}
		else {
			// Add the key to the db and persistent cache

			$args = array(
					array("col"=>"key_id", "op"=>"=", "val"=>$data["key_id"])
			);

			$columns = array("mode"=>"exclude", "col"=>array("key_id") );

			try{
				$result = $db->runUpdateQuery(self::$struct, $data, $args, $columns);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"DB update exception",
					    'data'=>array("data"=>$data, "args"=>$args,"columns"=>$columns ),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			if($result){

				// Update the class cache from the persistent cache
				try{
					self::loadCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>5,
						    'text'=>"loadCache exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// Flush the old class cache entry
				unset( $this->cache["keys"][$old["tree"]][$old["branch"]][$old["name"]] );

				// Set the new class cache entry
				$this->cache["keys"][$data["tree"]][$data["branch"]][$data["name"]] = $data["key_id"];

				// Write the updated class cache array to the persistent cache
				try{
					$cache_ok = self::saveCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>6,
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

	}



	/**
	 * Deletes a single key, given the key's $tree, $branch, and $name
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $tree | tree name for key (max 32 chars)
	 * @param string $branch | branch name for key (max 32 chars)
	 * @param string $name | name for key (max 32 chars)
	 * @return bool/int | False on failure. True on success.
	 */

	public function dropKeyByNameSingle($tree, $branch, $name){

		$key = array( "tree"=>$tree, "branch"=>$branch, "name"=>$name);
		$key = array($key);  // Wrap in array to match dropKeyByNameMulti() format

		try{
			$result = self::dropKeyByNameMulti($key);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"FOX_uKetType dropKeyByNameMulti exception",
				    'data'=>array("key"=>$key ),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		return $result;
	}



	/**
	 * Deletes one or more existing keys given the key's $tree, $branch, and $name
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $keys | one or more arrays, each describing a key to delete
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $tree | tree name for key (max 32 chars)
	 *	    => VAL @param string $branch | branch name for key (max 32 chars)
	 *	    => VAL @param string $name | name for key (max 32 chars)
	 *
	 * @return bool | False on failure. True on success.
	 */

	public function dropKeyByNameMulti($keys) {

		global $fox;
		$db = new FOX_db();

		if( empty($keys) ){	// Handle an empty input array
			return false;
		}

		// Get the ids for the keys
		$valid = true;
		try{
			$key_info = self::getKeyIDMulti($keys, $valid);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"FOX_uKeyType getKeyMulti exception",
				    'data'=>array("keys"=>$keys,"valid"=>$valid ),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		if($valid){

			// Convert the $key_info array into integer key id's

			$key_ids = array();

			foreach ($key_info as $tree_name => $branches){

			    foreach( $branches as $branch_name => $key_names){

				foreach( $key_names as $key_name => $key_id ){

					$key_ids[] = $key_id;
				}
			    }
			}
			unset($tree_name, $branches, $branch_name, $key_names, $key_name, $key_id);


			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
			    $started_transaction = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if( $started_transaction ){

				$args = array(
						array("col"=>"key_id", "op"=>"=", "val"=>$key_ids)
				);

				try{
					$db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>3,
						    'text'=>"Db delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// TODO:
				// 1) drop the keys from the user keystore
				// 2) purge the dropped keys from the user keystore cache
				// 3) drop the keys from all groups that grant them

				try{
					$query_ok = $db->commitTransaction();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>4,
						    'text'=>"commitTransaction exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
			}
			else {
				$query_ok = false;
			}
			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


			// Update the cache
			if($query_ok){

				// Update the class cache from the persistent cache
				try{
					self::loadCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>5,
						    'text'=>"loadCache exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// Flush the deleted keys from the class cache
				foreach ($key_info as $tree_name => $branches){

				    foreach( $branches as $branch_name => $key_names){

					foreach( $key_names as $key_name => $fake_var ){

						unset($this->cache["keys"][$tree_name][$branch_name][$key_name]);
					}
				    }
				}
				unset($tree_name, $branches, $branch_name, $key_names, $key_name, $fake_var);

				// Write the updated class cache array to the persistent cache
				try{
					$cache_ok = self::saveCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>6,
						    'text'=>"beginTransaction exception",
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
		else {


			throw new FOX_exception(array(
				    'numeric'=>7,
				    'text'=>"FOX_uKeyType::dropKeyByNameMulti - Attempted to call on one or more keys that do not exist",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);

		} // ENDOF: if($valid)


	}


	/**
	 * Deletes one or more keys, given the $key_id
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int/array $key_id | single $key_id as int. Multiple as array of ints.
	 * @return bool | False on failure. True on success.
	 */

	public function dropByID($key_id) {

		global $fox;
		$db = new FOX_db();

		// Fetch all the matching keys from the db
		$args = array(
				array("col"=>"key_id", "op"=>"=", "val"=>$key_id),
		);

		$columns = array("mode"=>"include", "col"=>array("tree", "branch", "name") );
		$ctrl = array("format"=>"array_array");

		try{
			$key_names = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
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

		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		try{
			$started_transaction = $db->beginTransaction();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"beginTransaction exception",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		if($started_transaction  ){

			$args = array(
					array("col"=>"key_id", "op"=>"=", "val"=>$key_id)
			);

			try{
				$db->runDeleteQuery(self::$struct, $args);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"DB delete exception",
					    'data'=>$args,
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			// TODO:
			// 1) drop the keys from the user keystore
			// 2) purge the dropped keys from the user keystore cache
			// 3) drop the keys from all groups that grant them

			try{
				$query_ok = $db->commitTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"commitTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}
		else {
			$query_ok = false;
		}
		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		// Update the cache
		if($query_ok){

			// Update the class cache from the persistent cache
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
			// Flush the deleted keys from the class cache
			foreach($key_names as $key){

				$tree = $key["tree"];
				$branch = $key["branch"];
				$name = $key["name"];

				unset($this->cache["keys"][$tree_name][$branch_name][$name]);
			}
			unset($key, $tree, $branch, $name);

			// Write the updated class cache array to the persistent cache
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
	 * Deletes an entire branch of keys
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $tree | The branch's tree name
	 * @param string $branch | The branch name
	 * @return bool | False on failure. True on success.
	 */

	public function dropBranch($tree, $branch) {

		global $fox;
		$db = new FOX_db();

		// Fetch the key_id's for all matching keys from the db
		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branch),
		);

		$columns = array("mode"=>"include", "col"=>array("key_id") );
		$ctrl = array("format"=>"col");

		try{
			$drop_ids = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"Db select exception",
				    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
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
				    'numeric'=>2,
				    'text'=>"beginTransaction exception",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		if( $started_transaction ){

			$args = array(
					array("col"=>"key_id", "op"=>"=", "val"=>$drop_ids)
			);

			try{
				$db->runDeleteQuery(self::$struct, $args);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"DB delete exception",
					    'data'=>$args,
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// TODO:
			// 1) drop the keys from the user keystore
			// 2) purge the dropped keys from the user keystore cache
			// 3) drop the keys from all groups that grant them

			try{
				$query_ok = $db->commitTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"commitTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

		}
		else {
			$query_ok = false;
		}
		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		// Update the cache
		if($query_ok){

			// Update the class cache from the persistent cache
			try{
				self::loadCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>5,
					    'text'=>"loadCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// Flush the deleted branch from the class cache
			unset($this->cache["keys"][$tree_name][$branch]);
			unset($this->cache["branches"][$tree_name][$branch]);

			// Write the updated class cache array to the persistent cache
			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>6,
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
	 * Deletes an entire tree of keys
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $tree | The tree name
	 * @return bool | False on failure. True on success.
	 */

	public function dropTree($tree) {

		global $fox;
		$db = new FOX_db();

		// Fetch the key_id's for all matching keys from the db
		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree)
		);

		$columns = array("mode"=>"include", "col"=>array("key_id") );
		$ctrl = array("format"=>"col");

		try{
			$drop_ids = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
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


		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		try{
			$started_transaction = $db->beginTransaction();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"beginTransaction exception",
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		if( $started_transaction ){

			$args = array(
					array("col"=>"key_id", "op"=>"=", "val"=>$drop_ids)
			);

			try{
				$db->runDeleteQuery(self::$struct, $args);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"DB delete exception",
					    'data'=>$args,
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			// TODO:
			// 1) drop the keys from the user keystore
			// 2) purge the dropped keys from the user keystore cache
			// 3) drop the keys from all groups that grant them

			try{
				$query_ok = $db->commitTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"commitTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}
		else {
			$query_ok = false;
		}
		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		// Update the cache
		if($query_ok){

			// Update the class cache from the persistent cache
			try{
				self::loadCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>5,
					    'text'=>"loadCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// Flush the deleted branch from the class cache
			unset($this->cache["keys"][$tree_name]);
			unset($this->cache["branches"][$tree_name]);
			unset($this->cache["trees"][$tree_name]);

			// Write the updated class cache array to the persistent cache
			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>6,
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
	 * Returns all keys currently stored in the db
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return bool/array | False on failure. Array of keys on success.
	 */

	public function getAll() {

		$result = array(

			    "91"=>"Editors",
			    "74"=>"Suspended",
			    "216"=>"Not Verified",
			    "112"=>"Group Ban",
		);

		return $result;

	}


} // End of class FOX_uKeyType




/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_FOX_uKeyType(){

	$cls = new FOX_uKeyType();
	
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
add_action( 'fox_install', 'install_FOX_uKeyType', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_FOX_uKeyType(){

	$cls = new FOX_uKeyType();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_uKeyType', 2 );

?>