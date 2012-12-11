<?php

/**
 * FOXFIRE L3 MONOLITHIC ABSTRACT DATASTORE CLASS
 * Implements an efficient 3rd order monolithic datastore
 *
 * FEATURES
 * --------------------------------------
 *  -> Transient cache support
 *  -> Persistent cache support
 *  -> Progressive cache loading
 *  -> SQL transaction support
 *  -> Fully atomic operations
 *  -> Advanced error handling
 *  -> Data validation on key values
 *  -> Multi-thread safe
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Config
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_dataStore_monolithic_L3_base extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache
					    // locking. Loaded by descendent class.

	var $cache;			    // Main cache array for this class

	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache
					    // operations. Loaded by descendent class.


	/* ================================================================================================================
	 *	Cache Strategy: "monolithic"
	 *
	 *	=> ARR array $cache | Main cache array
	 *
	 *	    => ARR @param array $keys | Main datastore
	 *		=> ARR string '' | tree
	 *		    => ARR string | branch
	 *			=> KEY string | node
	 *			    => VAL string 'filter' | Filter name
	 *			    => VAL mixed 'ctrl' | Filter control options
	 *			    => VAL mixed 'val ' | serialized key data
	 *
	 *	    => VAL bool $all_cached | True if the cache has authority (all rows loaded from db)
	 *
	 *	    => ARR array $trees | Trees dictionary
	 *		=> KEY string '' | tree name
	 *		    => VAL bool | True if cached. False if not.
	 *
	 *	    => ARR array $branches | Branches dictionary
	 *		=> ARR string '' | tree name
	 *		    => KEY string | branch name
	 *			=> VAL bool | True if cached. False if not.
	 *
	 * */

	// ============================================================================================================ //


	/**
	 * Loads node, branch, tree, or the entire datastore into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $tree| Single tree id as int.
	 * @param int/array $branch | Single branch id as int.
	 * @param string/array $node | Single node name as string. Multiple node names as array of strings.
	 * @param bool $skip_load | If set true, the function will not update the class cache array from
	 *			    the persistent cache before adding data from a db call and saving it
	 *			    back to the persistent cache.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function load($tree=null, $branch=null, $node=null, $skip_load=false){


		$db = new FOX_db();
		$struct = $this->_struct();

		// Build and run query
		// ===========================================================

		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree)
		);

		if($branch){

			$args[] = array("col"=>"branch", "op"=>"=", "val"=>$branch);

			if($node){
				$args[] = array("col"=>"node", "op"=>"=", "val"=>$node);
			}
		}

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("tree", "branch", "node") );
		$columns=null;

		try {
			$db_result = $db->runSelectQuery($struct, $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		if($db_result){

			// Fetch the persistent cache image
			// ===================================================

			if($skip_load){

				$cache_image = $this->cache;
			}
			else {
				try {
					$cache_image = self::readCache();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					));
				}
			}

			// Rebuild the cache image
			// ===================================================

			$ancestor_cached = false;

			if(!$tree){

				// Indicate all data for the module_id is cached
				$cache_image["all_cached"] = true;

				// Clear descendent dictionaries, because they're redundant
				unset($cache_image["trees"]);
				unset($cache_image["branches"]);

				// Prevent descendents from loading their dictionaries
				$ancestor_cached = true;
			}

			foreach( $db_result as $_tree => $branches ){

				if(!$branch && !$ancestor_cached){

					$cache_image["trees"][$_tree] = true;
					unset($cache_image["branches"][$_tree]);

					$ancestor_cached = true;
				}

				foreach( $branches as $_branch => $nodes ){

					if(!$node && !$ancestor_cached){

						$cache_image["branches"][$_tree][$_branch] = true;
					}

					foreach( $nodes as $_node => $val ){

						$cache_image["keys"][$_tree][$_branch][$_node] = $val;
					}
					unset($_node, $val);

				}
				unset($_branch, $nodes);

			}
			unset($_tree, $branches);

			// Clear empty walks from dictionary arrays
			$cache_image["branches"] = FOX_sUtil::arrayPrune($cache_image["branches"], 1);


			// Update the persistent cache
			// ===================================================

			try {
				 self::writeCache($cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache write error",
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));
			}

			// Update the class cache
			$this->cache = $cache_image;

		}

		return true;

	}


	/**
	 * Fetches a node, branch, tree, or all nodes from the cache or database
	 *
	 * If an object is not in the cache yet, it will be retrieved from the database
	 * and added to the cache. Multiple items in the *lowest level group* specified
	 * can be retrieved in a single query by passing their names or id's as an array.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $tree | Single $tree as int. Multiple as array of ints.
	 * @param int/array $branch | Single $branch as int. Multiple as array of ints.
	 * @param string/array $node | Single node name as string. Multiple as array of strings.
	 * @param bool &$valid | True if all requested objects exist in the db. False if not.
	 *
	 * @return mixed | Exception on failure. Requested objects on success.
	 */

	public function get($trees=null, $branches=null, $nodes=null, &$valid=null){


		$valid = true;

		// CASE 1: One or more $nodes
		// =====================================================================
		if($trees && $branches && $nodes){

			// Trap attempting to pass array of tree's, or branches when specifying node

			if( is_array($trees) || is_array($branches) ){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Attempted to specify multiple trees or branches when specifying nodes",
					'data'=>array('trees'=>$trees, 'branch'=>$branches, 'nodes'=>$nodes),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}

			if( !is_array($nodes) ){
				$nodes = array($nodes);
			}

			// Find all the nodes that have been requested but are not in the cache
			$missing_nodes = array();
			$cache_loaded = false;

			foreach($nodes as $node){

				$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
				$tree_cached = FOX_sUtil::keyTrue($trees, $this->cache["trees"]);
				$branch_cached = FOX_sUtil::keyTrue($branch, $this->cache["branches"][$trees]);
				$node_exists = FOX_sUtil::keyExists($node, $this->cache["keys"][$trees][$branches]);

				// If the node doesn't exist in the class cache array, fetch it from the persistent cache
				if(!$all_cached && !$tree_cached && !$branch_cached && !$node_exists && !$cache_loaded){

					try {
						self::loadCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Cache read error",
							'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
							'child'=>$child
						));
					}

					$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
					$tree_cached = FOX_sUtil::keyTrue($trees, $this->cache["trees"]);
					$branch_cached = FOX_sUtil::keyTrue($branch, $this->cache["branches"][$trees]);
					$node_exists = FOX_sUtil::keyExists($node, $this->cache["keys"][$trees][$branches]);

					$cache_loaded = true;

				}

				// If the requested node doesn't exist in the persistent cache, add it to the missing nodes array
				if(!$all_cached && !$tree_cached && !$branch_cached && !$node_exists){

					$missing_nodes[] = $node;
				}

			}
			unset($node);


			// Load all missing keys in a single query
			if( count($missing_nodes) > 0 ){

				try {
					$valid = self::load($trees, $branches, $missing_nodes, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Error in self::load()",
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested nodes
			foreach($nodes as $node){

				$result[$node] = $this->cache["keys"][$trees][$branches][$node];
			}
			unset($node);

			return $result;

		}

		// CASE 2: One or more $branches
		// =====================================================================

		elseif($trees && $branches && !$nodes){

			// Trap attempting to pass array of tree's when specifying branch

			if( is_array($trees) || is_array($branches) ){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Attempted to specify multiple trees when specifying branch",
					'data'=>array('trees'=>$trees, 'branch'=>$branches, 'nodes'=>$nodes),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}

			if( !is_array($branches) ){
				$branches = array($branches);
			}

			// Find all the branches that have been requested but are not in the cache
			$missing_branches = array();
			$cache_loaded = false;

			foreach($branches as $branch){

				$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
				$tree_cached = FOX_sUtil::keyTrue($trees, $this->cache["trees"]);
				$branch_cached = FOX_sUtil::keyTrue($branch, $this->cache["branches"][$trees]);

				// If the branch doesn't exist in the class cache array, fetch it from the persistent cache
				if( !$all_cached && !$tree_cached && !$branch_cached && !$cache_loaded){

					try {
						self::loadCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>5,
							'text'=>"Cache read error",
							'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
							'child'=>$child
						));
					}

					$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
					$tree_cached = FOX_sUtil::keyTrue($trees, $this->cache["trees"]);
					$branch_cached = FOX_sUtil::keyTrue($branch, $this->cache["branches"][$trees]);

					$cache_loaded = true;

				}

				// If the branch doesn't exist in the persistent cache, add it to the missing branches array
				if( !$all_cached && !$tree_cached && !$branch_cached ){

					$missing_branches[] = $branch;
				}

			}
			unset($branch);


			// Load all missing branches in a single query
			if( count($missing_branches) != 0 ){

				try {
					$valid = self::load($trees, $missing_branches, null, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Error in self::load()",
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested branches
			foreach($branches as $branch){

				$result[$branch] = $this->cache["keys"][$trees][$branch];
			}
			unset($branch);

			return $result;

		}

		// CASE 3: One or more $trees
		// =====================================================================
		elseif($trees && !$branches && !$nodes){

			if( !is_array($trees) ){
				$trees = array($trees);
			}

			// Find all the trees that have been requested but are not in the cache
			$missing_trees = array();
			$cache_loaded = false;

			foreach($trees as $tree){

				$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
				$tree_cached = FOX_sUtil::keyTrue($tree, $this->cache["trees"]);

				// If the tree doesn't exist in the class cache array, fetch it from the persistent cache
				if( !$all_cached  && !$tree_cached && !$cache_loaded){

					try {
						self::loadCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>7,
							'text'=>"Cache read error",
							'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
							'child'=>$child
						));
					};

					$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
					$tree_cached = FOX_sUtil::keyTrue($tree, $this->cache["trees"]);

					$cache_loaded = true;

				}

				// If the tree doesn't exist in the persistent cache, add it to the missing branches array
				if( !$all_cached && !$tree_cached ){

					$missing_trees[] = $tree;
				}

			}
			unset($tree);


			// Load all missing branches in a single query
			if( count($missing_trees) != 0 ){

				try {
					$valid = self::load($missing_trees, null, null, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>8,
						'text'=>"Error in self::load()",
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested branches
			foreach($tree as $tree){

				$result[$tree] = $this->cache["keys"][$tree];
			}
			unset($tree);

			return $result;

		}
		// CASE 4: Load entire data store
		// =====================================================================
		elseif(!$trees && !$branches && !$nodes) {

			$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);

			// If the tree doesn't exist in the class cache array, fetch it from the persistent cache
			if(!$all_cached){

				try {
					self::loadCache();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>9,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					));
				};

				$all_cached = FOX_sUtil::keyTrue("all_cached", $this->cache);
			}

			if(!$all_cached){

				$valid = self::load(null, null, null, $skip_load=true);
			}

			return $this->cache["keys"];

		}
		// CASE 4: Bad input (Example: $trees=null, $branches=array, $nodes=null)
		// =====================================================================
		else {

			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Bad input format",
				'data'=>array('trees'=>$trees, 'branches'=>$branches, 'nodes'=>$nodes),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

	}

	/**
	 * Fetches entire datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getAll(){

		try {
			$result = self::get(null, null, null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}

	/**
	 * Fetches an entire tree
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $tree | single tree ass tring. Multiple trees as array of string.
	 * @param bool $valid | true if all requested trees exist.
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getTree($tree, &$valid=null){


		try {
			$result = self::get($tree, null, null, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('tree'=>$tree),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Fetches an entire branch. If the branch is not in the cache yet, it
	 * will be retrieved from the database and added to the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $tree | tree name
	 * @param string/array $branch | single branch as string. Multiple branches as array of string.
	 * @param bool $valid | true if all requested branches exist.
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getBranch($tree, $branch, &$valid=null){

;
		try {
			$result = self::get($tree, $branch, null, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('tree'=>$tree, 'branch'=>$branch),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Fetches a key's value, filter function, and filter function config data as an array. If the key's
	 * data is not in the cache yet, it will be retrieved from the database and added to the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $tree | tree name
	 * @param string $branch | branch name
	 * @param string/array $node | single node as string. Multiple nodes as array of string.
	 * @param bool $valid | true if all requested nodes exist
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getNode($tree, $branch, $node, &$valid=null){


		try {
			$result = self::get($tree, $branch, $node, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		return $result;

	}


	/**
         * Returns a node's value
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $tree | tree name
	 * @param string $branch | branch name
	 * @param string/array $node | single node as string. Multiple nodes as array of string.
	 * @param bool $valid | true if all requested branches exist.
	 *
	 * @return mixed | Exception on failure. Node contents on single item. Array of node contents on multiple items.
         */

	public function getNodeVal($tree, $branch, $node, &$valid=null){


		// Get the requested node or nodes
		// ===================================================

		try {
			$db_result = self::getNode($tree, $branch, $node, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Process result into correct format
		// ===================================================

		if( is_array($node) ){

			$result = array();

			foreach( $db_result as $_node => $data ){

				$result[$_node] = $data["val"];
			}
			unset($_node);
		}
		else {
			$result = $db_result["val"];
		}

		return $result;


	}


	/**
	 * Updates an existing node if the $tree-$branch-$node tuple already exists
	 * in the db, or creates a new node if it doesn't.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string $node | Node name.
	 * @param mixed  $val | Value to store to node
	 * @param string $filter | The filter function to validate data stored to node
	 * @param array $ctrl | Filter function's control args
	 *
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function writeNode($tree, $branch, $node, $val, $filter, $ctrl=null){


		$db = new FOX_db();
		$struct = $this->_struct();

		// Trap empty keys, and keys that map to ints. We can't use ints as keys
		// or strings that map to keys due to the way PHP handles array indexing
		// in our trie structures. For example, if we had an array with the keys
		// "red", "green", "blue", and (string)"1"; PHP would *overwrite* the
		// "green" key when we tried to save key (string)"1", because it converts
		// it to (int)1 and addresses the array in indexed mode instead of associative
		// mode. There is no way to override this behavior in PHP.

		if( ( ($tree == (int)$tree ) || !is_string($tree) || empty($tree) ) ||
		    ( ($branch == (int)$branch ) || !is_string($branch) || empty($branch) ) ||
		    ( ($node == (int)$node ) || !is_string($node) || empty($node) ) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Tree, Branch, and Node keys must be valid strings that do not map to ints",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		// Trap attempting to use a nonexistent filter
		$cls = new FOX_sanitize();

		if( !method_exists($cls, $filter) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Filter method doesn't exist",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'filter'=>$filter),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		// Run node value through the specified filter
		// =====================================================

		$filter_valid = null; $filter_error = null; // Passed by reference

		try {
			$processed_val = $cls->{$filter}($val, $ctrl, $filter_valid, $filter_error);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in filter function",
				'data'=>array('filter'=>$filter, 'val'=>$val, 'ctrl'=>$ctrl,
					      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		if(!$filter_valid){

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Filter function reports value data isn't valid",
				'data'=>array('filter'=>$filter, 'val'=>$val, 'ctrl'=>$ctrl,
					      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}


		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$args = array(	"tree"=>$tree, "branch"=>$branch, "node"=>$node,
				"val"=> $processed_val, "filter"=>$filter, "ctrl"=>$ctrl
		);

		$columns = null;

		try {
			$rows_changed = $db->runIndateQuery($struct, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error writing to database",
				'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Rebuild the cache image
		// ===========================================================

		$cache_image["keys"][$tree][$branch][$node]["val"] = $processed_val;
		$cache_image["keys"][$tree][$branch][$node]["filter"] = $filter;
		$cache_image["keys"][$tree][$branch][$node]["ctrl"] = $ctrl;


		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Cache write error",
				'data'=>array('cache_image'=>$cache_image),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;


		return (int)$rows_changed;

	}


	/**
	 * Updates an existing key
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string $node | Node name
	 * @param mixed  $val | Value to store to node
	 *
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function setNode($tree, $branch, $node, $val){


		if( empty($tree) || empty($branch) || empty($node) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid tree, branch, or node",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));
		}

		// Grab the node's info so we know it's $filter and $ctrl args
		// ============================================================

		$valid = false;

		try {
			$node_data = self::getNode($tree, $branch, $node, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::getNode()",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		if(!$valid){

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Attempted to set data for nonexistent node",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		$filter = $node_data["filter"];
		$ctrl = $node_data["ctrl"];

		try {
			$rows_changed = self::writeNode($tree, $branch, $node, $val, $filter, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error in self::writeNode()",
				'data'=> array('tree'=>$tree, 'branch'=>$branch, 'node'=>$node,
					       'val'=>$val, 'filter'=>$filter, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		return $rows_changed;


	}


	/**
	 * Deletes one or more nodes from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string/array $nodes | Single node as string. Multiple nodes as array of string.
	 *
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropNode($tree, $branch, $nodes) {


		$db = new FOX_db();
		$struct = $this->_struct();


		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branch),
				array("col"=>"node", "op"=>"=", "val"=>$nodes)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Rebuild the cache image
		// ===========================================================

		if(!is_array($nodes)){
			$nodes = array($nodes);
		}

		foreach( $nodes as $node){

			unset($cache_image["keys"][$tree][$branch][$node]);
		}
		unset($node);

		$cache_image["keys"] = FOX_sUtil::arrayPrune($cache_image["keys"], 2);


		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache write error",
				'data'=>array('cache_image'=>$cache_image),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;


		return (int)$rows_changed;

	}


	/**
	 * Deletes one or more branches from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $tree | Tree name
	 * @param string/array $branches | Single branch as string. Multiple branches as array of string.
	 *
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropBranch($tree, $branches) {


		$db = new FOX_db();
		$struct = $this->_struct();


		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$tree),
				array("col"=>"branch", "op"=>"=", "val"=>$branches)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Rebuild the cache image
		// ===========================================================

		if(!is_array($branches)){
			$branches = array($branches);
		}

		foreach($branches as $branch){

			unset($cache_image["keys"][$tree][$branch]);
			unset($cache_image["branches"][$tree][$branch]);
		}
		unset($branch);

		$cache_image["branches"] = FOX_sUtil::arrayPrune($cache_image["branches"], 1);
		$cache_image["keys"] = FOX_sUtil::arrayPrune($cache_image["keys"], 2);


		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache write error",
				'data'=>array('cache_image'=>$cache_image),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;

		return (int)$rows_changed;

	}


	/**
	 * Deletes one or more trees from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $trees | Single tree as string. Multiple trees as array of string.
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropTree($trees) {


		$db = new FOX_db();
		$struct = $this->_struct();


		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================


		$args = array(
				array("col"=>"tree", "op"=>"=", "val"=>$trees)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		if(!is_array($trees)){
			$trees = array($trees);
		}

		// Rebuild the cache image
		// ===========================================================

		foreach($trees as $tree){

			unset($cache_image["keys"][$tree]);
			unset($cache_image["branches"][$tree]);
			unset($cache_image["trees"][$tree]);
		}
		unset($tree);

		// Clear empty walks
		$cache_image["branches"] = FOX_sUtil::arrayPrune($cache_image["branches"], 1);
		$cache_image["keys"] = FOX_sUtil::arrayPrune($cache_image["keys"], 2);


		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache write error",
				'data'=>array('cache_image'=>$cache_image),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;

		return (int)$rows_changed;

	}


	/**
	 * Deletes the entire datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropAll() {


		$db = new FOX_db();
		$struct = $this->_struct();


		// Lock the cache
		// ===========================================================

		try {
			self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Clear the database
		// ===========================================================

		try {
			$db->runTruncateTable($struct);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error truncating database table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Flush the cache
		// ===========================================================

		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error flushing cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		return true;

	}



	/**
	 * Drops one or more nodes within a single branch for ALL TREES in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $branch | name of branch
	 * @param string/array $node | single node as string. Multiple nodes as array of string.
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropSiteKey($branch, $nodes) {


		$db = new FOX_db();
                $struct = $this->_struct();

		// Lock the cache
		// ===========================================================

		try {
			self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$args = array(
				array("col"=>"branch", "op"=>"=", "val"=>$branch),
				array("col"=>"node", "op"=>"=", "val"=>$nodes)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Flush the cache
		// ===========================================================

		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error flushing cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		return (int)$rows_changed;

	}


	/**
	 * Drops one or more branches for ALL TREES in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $branches | single branch as string. Multiple branches as array of string.
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropSiteBranch($branches) {


		$db = new FOX_db();
		$struct = $this->_struct();


		// Lock the cache
		// ===========================================================

		try {
			self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$args = array(
				array("col"=>"branch", "op"=>"=", "val"=>$branches)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		// Flush the cache
		// ===========================================================

		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error flushing cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}


		return (int)$rows_changed;

	}




} // End of class FOX_dataStore_monolithic_L3_base

?>