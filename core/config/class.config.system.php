<?php

/**
 * BP-MEDIA CONFIGURATION CLASS
 * Handles all configuration settings for the plugin
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Config
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class BPM_config extends FOX_dataStore_paged_L4_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache
					    // locking. Inherited from FOX_dataStore_monolithic_L3_base.

	var $mCache;			    // Local copy of memory cache singleton

	var $install_classes_loaded;	    // True if the base install classes have been loaded
	var $uninstall_classes_loaded;	    // True if the base uninstall classes have been loaded

	var $key_delimiter = "^";	    // The character the class uses to split keys into "tree", "branch", and "node"
					    // when parsing returned forms.

	// ============================================================================================================ //

	public static $struct = array(

		"table" => "BPM_config",
		"engine" => "InnoDB",
		"cache_namespace" => "BPM_config",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "plugin" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,
			// This forces every combination to be unique
			"index"=>array("name"=>"top_level_index",	"col"=>array("plugin", "tree", "branch", "node"), "index"=>"PRIMARY"), "this_row"=>true),
		    "tree" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "branch" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "node" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "data" =>	array(	"php"=>"serialize", "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>"",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
		 )
	);	

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
			global $bpm;
			$this->process_id = &$bpm->process_id;
		}
		
		$this->init();		

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
	 * Fetches an entire tree
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $tree | single tree as string. Multiple trees as array of string.
	 * @param bool $valid | true if all requested trees exist.
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getTree($plugin, $tree, &$valid=null){


		$struct = $this->_struct();
		$validator_result = array();

		try {

			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);	


			$validator_result['plugin'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'scalar',
								'var'=>$plugin
			));	
	
			// If a single tree name is sent in, we validate it individually instead of automatically 
			// spinning it into an array and validating the array. This lets us trap strings that PHP 
			// automatically converts to ints ("17")

			if( !is_array($tree) ){

				$validator_result['tree'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L3_col]['php'],
									'format'=>'scalar',
									'var'=>$tree
				));					
			}
			else {

				foreach( $tree as $key => $val ){

					$validator_result['tree'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L3_col]['php'],
										'format'=>'scalar',
										'var'=>$val
					));	

					if( $validator_result['tree'] !== true ){

						break;
					}
				}
				unset($key, $val);
			}

		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator class",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		// This structure has to be outside the validator try-catch block to prevent it from   
		// catching the exceptions we throw (which would cause confusing exception chains)

		foreach( $validator_result as $key => $val ){

			if($val !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid " . $key . " name",
					'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'msg'=>$val),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);			
		
		$get_ctrl = array(
			'validate'=>false,
			'r_mode'=>'trie'		    
		);
		
		try {
			$result = self::getL3($plugin, $tree, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling self::getL3()",
				'data'=> array('plugin'=>$plugin,'tree'=>$tree),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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

	public function getBranch($plugin, $tree, $branch, &$valid=null){

	    
		$struct = $this->_struct();
		$validator_result = array();

		try {

			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);	


			$validator_result['plugin'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'scalar',
								'var'=>$plugin
			));	
	
			$validator_result['tree'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'scalar',
								'var'=>$tree
			));	
			
			// If a single branch name is sent in, we validate it individually instead of automatically 
			// spinning it into an array and validating the array. This lets us trap strings that PHP 
			// automatically converts to ints ("17")

			if( !is_array($tree) ){

				$validator_result['branch'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L2_col]['php'],
									'format'=>'scalar',
									'var'=>$branch
				));					
			}
			else {

				foreach( $branch as $key => $val ){

					$validator_result['branch'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L2_col]['php'],
										'format'=>'scalar',
										'var'=>$val
					));	

					if( $validator_result['branch'] !== true ){

						break;
					}
				}
				unset($key, $val);
			}

		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator class",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		// This structure has to be outside the validator try-catch block to prevent it from   
		// catching the exceptions we throw (which would cause confusing exception chains)

		foreach( $validator_result as $key => $val ){

			if($val !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid " . $key . " name",
					'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'msg'=>$val),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);			
		
		$get_ctrl = array(
			'validate'=>false,
			'r_mode'=>'trie'		    
		);
		
		try {
			$result = self::getL2($plugin, $tree, $branch, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling self::getL2()",
				'data'=> array('plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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

	public function getNode($plugin, $tree, $branch, $node, &$valid=null){

	    
		$struct = $this->_struct();
		$validator_result = array();

		try {

			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);	


			$validator_result['plugin'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'scalar',
								'var'=>$plugin
			));	
	
			$validator_result['tree'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'scalar',
								'var'=>$tree
			));	
			
			$validator_result['branch'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L2_col]['php'],
								'format'=>'scalar',
								'var'=>$branch
			));	
			
			
			// If a single branch name is sent in, we validate it individually instead of automatically 
			// spinning it into an array and validating the array. This lets us trap strings that PHP 
			// automatically converts to ints ("17")

			if( !is_array($tree) ){

				$validator_result['node'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L1_col]['php'],
									'format'=>'scalar',
									'var'=>$node
				));					
			}
			else {

				foreach( $node as $key => $val ){

					$validator_result['node'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L1_col]['php'],
										'format'=>'scalar',
										'var'=>$val
					));	

					if( $validator_result['node'] !== true ){

						break;
					}
				}
				unset($key, $val);
			}

		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator class",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		// This structure has to be outside the validator try-catch block to prevent it from   
		// catching the exceptions we throw (which would cause confusing exception chains)

		foreach( $validator_result as $key => $val ){

			if($val !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid " . $key . " name",
					'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'msg'=>$val),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);

		
		$get_ctrl = array(
			'validate'=>false,
			'r_mode'=>'trie'		    
		);
		
		try {
			$result = self::getL1($plugin, $tree, $branch, $node, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling self::getL1()",
				'data'=> array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
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

	public function writeNode($plugin, $tree, $branch, $node, $val, $filter, $ctrl=null){


		$struct = $this->_struct();
		$validator_result = array();

		try {

			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);	


			$validator_result['plugin'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'scalar',
								'var'=>$plugin
			));	
	
			$validator_result['tree'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'scalar',
								'var'=>$tree
			));	
			
			$validator_result['branch'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L2_col]['php'],
								'format'=>'scalar',
								'var'=>$branch
			));	
			
			$validator_result['node'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L1_col]['php'],
								'format'=>'scalar',
								'var'=>$node
			));			

		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator class",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		// This structure has to be outside the validator try-catch block to prevent it from   
		// catching the exceptions we throw (which would cause confusing exception chains)

		foreach( $validator_result as $key => $val ){

			if($val !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid " . $key . " name",
					'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'msg'=>$val),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);

		
		// Trap attempting to use a nonexistent filter
		$cls = new FOX_sanitize();

		if( !method_exists($cls, $filter) ){

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Filter method doesn't exist",
				'data'=> array( 'plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val,
						'filter'=>$filter),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'numeric'=>4,
				'text'=>"Error in filter function",
				'data'=>array(  'plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val,
						'filter'=>$filter, 'ctrl'=>$ctrl,
						'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		if(!$filter_valid){

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Filter function reports value data isn't valid",
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val,
					      'filter'=>$filter, 'ctrl'=>$ctrl,
					      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Update the database
		// ===========================================================

		$val = array(
			    'filter'=>$filter,
			    'filter_ctrl'=>$ctrl,
			    'val'=>$processed_val		    
		);

		$set_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = self::setL1($plugin, $tree, $branch, $node, $val, $set_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in self::setL1()",
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}				

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

	public function setNode($plugin, $tree, $branch, $node, $val){


		$struct = $this->_struct();
		$validator_result = array();

		try {

			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);	


			$validator_result['plugin'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'scalar',
								'var'=>$plugin
			));	
	
			$validator_result['tree'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'scalar',
								'var'=>$tree
			));	
			
			$validator_result['branch'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L2_col]['php'],
								'format'=>'scalar',
								'var'=>$branch
			));	
			
			$validator_result['node'] = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L1_col]['php'],
								'format'=>'scalar',
								'var'=>$node
			));
				
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator class",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		// This structure has to be outside the validator try-catch block to prevent it from   
		// catching the exceptions we throw (which would cause confusing exception chains)

		foreach( $validator_result as $key => $val ){

			if($val !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid " . $key . " name",
					'data'=>$val,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);
		

		// Grab the node's info so we know it's $filter and $ctrl args
		// ============================================================

		$valid = false;

		$get_ctrl = array(
			'validate'=>false,
			'r_mode'=>'trie'		    
		);
		
		try {
			$node_data = self::getL1($plugin, $tree, $branch, $node, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling self::getL1()",
				'data'=> array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		if(!$valid){

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Attempted to set data for nonexistent node",
				'data'=> array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Trap attempting to use a nonexistent filter (typically due to manual editing of the database)
		// =====================================================
		
		$cls = new FOX_sanitize();

		if( !method_exists($cls, $node_data["filter"]) ){

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Filter method doesn't exist",
				'data'=> array( 'plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch, 'node'=>$node,
						'filter'=>$node_data["filter"]),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Run node value through the specified filter
		// =====================================================

		$filter_valid = null; $filter_error = null; // Passed by reference

		try {
			$processed_val = $cls->{$node_data["filter"]}($val, $node_data["filter_ctrl"], $filter_valid, $filter_error);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in filter function",
				'data'=>array(  'plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val,
						'filter'=>$node_data["filter"], 'filter_ctrl'=>$node_data["filter_ctrl"],
						'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		if(!$filter_valid){

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Filter function reports value data isn't valid",
				'data'=>array( 'plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val,
					       'filter'=>$node_data["filter"], 'filter_ctrl'=>$node_data["filter_ctrl"],
					       'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Update the database
		// ===========================================================

		$val = array(
			    'filter'=>$node_data["filter"],
			    'filter_ctrl'=>$node_data["filter_ctrl"],
			    'val'=>$processed_val		    
		);

		$set_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = self::setL1($plugin, $tree, $branch, $node, $val, $set_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in self::setL1()",
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'val'=>$val),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}				


		return (int)$rows_changed;


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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
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
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		return (int)$rows_changed;

	}	


	/**
         * Processes config variables passed via an HTML form
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param array $post | HTML form array
         * @return int | Exception on failure. Number of rows changed on success.
         */

	public function processHTMLForm($post){


		if( empty($post['key_names']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"No key names posted with form",
				'data'=> $post,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$san = new BPM_sanitize();

		// Explode fields array into individual key names
		$options = explode(',', stripslashes($post['key_names']));

		// Convert the encoded full name into its corresponding $tree, $branch,
		// and $key, then add the group to the query array
		// ====================================================================

		$query_keys = array();

		foreach($options as $option) {

			$full_name = explode($this->key_delimiter, $option);

			// Process the raw form strings into proper key names
			$raw_tree = trim( $full_name[0] );
			$raw_branch = trim( $full_name[1] );
			$raw_key = trim( $full_name[2] );

			// Passed by reference
			$tree_valid = null; $branch_valid = null; $key_valid = null;
			$tree_error = null; $branch_error = null; $key_error = null;

			try {
				$tree = $san->keyName($raw_tree, null, $tree_valid, $tree_error);
				$branch = $san->keyName($raw_branch, null, $branch_valid, $branch_error);
				$key = $san->keyName($raw_key, null, $key_valid, $key_error);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in sanitizer function",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			if(!$tree_valid){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Called with invalid tree name",
					'data'=>array('raw_tree'=>$raw_tree, 'san_error'=>$tree_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$branch_valid){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Called with invalid branch name",
					'data'=>array('raw_branch'=>$raw_branch, 'san_error'=>$branch_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$key_valid){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Called with invalid key name",
					'data'=>array('raw_key'=>$raw_key, 'san_error'=>$key_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

			$query_keys[$tree][$branch][$key] = true;

		}
		unset($option, $tree, $raw_tree, $branch, $raw_branch, $key, $raw_key);
		unset($tree_error, $branch_error, $key_error, $tree_valid, $branch_valid, $key_valid);


		// Fold all requested keys into the minimum number of queries possible,
		// then fetch the data for those keys
		// ====================================================================

		foreach($query_keys as $tree => $query_branch_array){

			foreach($query_branch_array as $branch => $query_keys_array){

				$keys = array_keys($query_keys_array);

				// Note: we don't validate if the requested keys exist at this
				// stage because get() only tells us that "one or more" keys
				// weren't valid. Its much more useful to list the actual missing
				// keys, which we do in the next stage of the algorithm.

				try {
					self::get($tree, $branch, $keys);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Error in self::get()",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}
			}
		}
		unset($tree, $query_branch_array, $branch, $query_keys_array, $keys);


		// Iterate through all of the requested keys, cast the submitted form
		// data for each key to the format specified in the key's db entry
		// ====================================================================

		$rows_changed = 0;

		foreach($query_keys as $tree => $current_branch_array){

			foreach($current_branch_array as $branch => $current_keys_array){

				foreach($current_keys_array as $key => $fake_var){

					$filter = $this->cache["keys"][$tree][$branch][$key]["filter"];
					$ctrl = $this->cache["keys"][$tree][$branch][$key]["ctrl"];

					if(!is_string($filter) ){

						throw new FOX_exception( array(
							'numeric'=>7,
							'text'=>"Trying to set nonexistent key. Tree: $tree | Branch: $branch | Key: $key",
							'data'=>array('current_keys_array'=>$current_keys_array,
								      'tree'=>$tree, 'branch'=>$branch, 'key'=>$key),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));
					}

					// Remove any escaping PHP has added to the posted form value
					$post_key = $tree . $this->key_delimiter . $branch . $this->key_delimiter . $key;
					$post[$post_key] = FOX_sUtil::formVal($post[$post_key]);


					// Run key value through the specified filter
					// =====================================================

					$filter_valid = null; $filter_error = null; // Passed by reference

					try {
						$new_val = $san->{$filter}($post[$post_key], $ctrl, $filter_valid, $filter_error);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>8,
							'text'=>"Error in filter function",
							'data'=>array('filter'=>$filter, 'val'=>$post[$post_key], 'ctrl'=>$ctrl,
								      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					if(!$filter_valid){

						throw new FOX_exception( array(
							'numeric'=>9,
							'text'=>"Filter function reports value data isn't valid",
							'data'=>array('filter'=>$filter, 'val'=>$post[$post_key], 'ctrl'=>$ctrl,
								      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));
					}

					// If the new value doesn't match the stored value, update the key
					// ====================================================================

					if( $new_val != $this->cache["keys"][$tree][$branch][$key]["val"] ){

						try {
							$rows_changed += self::setNode($tree, $branch, $key, $new_val);
						}
						catch (FOX_exception $child) {

							throw new FOX_exception( array(
								'numeric'=>10,
								'text'=>"Error setting key. Tree: $tree | Branch: $branch | Key: $key",
								'data'=>array('tree'=>$tree, 'branch'=>$branch,
									      'key'=>$key, 'new_val'=>$new_val),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							));
						}
					}


				}   // ENDOF: foreach($current_keys_array as $key => $fake_var)
				unset($key, $fake_var);


			} // ENDOF: foreach($current_branch_array as $branch => $current_keys_array)
			unset($branch, $current_keys_array);


		} // ENDOF: foreach($query_keys as $tree => $current_branch_array)
		unset($tree, $current_branch_array);


		return $rows_changed;



	} // ENDOF: function processHTMLForm


	/**
         * Resets the class's print_keys array, making it ready to accept a new batch of keys. This method
	 * MUST be called at the beginning of each admin form to clear old keys from the singleton.
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function initKeysArray(){

		$this->print_keys = array();
	}


	/**
         * Creates a composite keyname given the keys's tree, branch, and name. Adds the
	 * composited key name to the $print_keys array for printing the form's keys array.
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string $key | Key name
	 *
         * @return ECHO | ECHO composited key name on success.
         */

	public function printKeyName($tree, $branch, $key){


		echo 'name="' . self::getKeyName($tree, $branch, $key) . '"';

	}

	public function getKeyName($tree, $branch, $key){

		$key_name = ($tree . $this->key_delimiter . $branch . $this->key_delimiter . $key);

		// Add formatted key name to the $keys array
		$this->print_keys[$key_name] = true;

		return $key_name;

	}


	/**
         * Prints a key's value
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string $key | Key name
	 * @param string $validate | If set true, throws an error if key doesn't exist in datastore.
	 *
         * @return ECHO | ECHO exception on failure. ECHO composited key name on success.
         */

	public function printKeyVal($tree, $branch, $key, $validate=false){


		$result = 'value="';
		$is_valid = false; // Passed by reference


		try {
			$result .= self::getNodeVal($tree, $branch, $key, $is_valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::getNodeVal()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		if( ($validate == true) && !$is_valid ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Key doesn't exist",
				'data'=>array('tree'=>$tree, 'branch'=>$branch, 'key'=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		else {
			echo $result . '"';
		}

	}


	/**
         * Creates a composited string used to print a hidden field containing all of the key names enqueued
	 * using keyName() or getKeyName()
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param string $field_name | (optional) name to use for the form field
         * @return ECHO | composited hidden form field string
         */

	public function printKeysArray($field_name=null){

		echo self::getKeysArray($field_name);
	}

	public function getKeysArray($field_name=null){

		// Handle no $field_name being passed

		if(!$field_name){
		    $field_name = "key_names";
		}

                $result = '<input type="hidden" name="' . $field_name . '" value="';

		$keys_left = count( $this->print_keys) - 1;

		foreach( $this->print_keys as $key_name => $value) {

			$result .= $key_name;

			if($keys_left != 0){
				$result .= ", ";
				$keys_left--;
			}
		}

                $result .= '" />';

		return $result;

	}


	/**
         * Places the system in "install mode" by loading all the base install classes
	 * from the /core/config/install_base folder
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function installMode(){


		if(!$this->install_classes_loaded){

			$base_install_classes = glob( BPM_PATH_BASE .'/core/config/install_base/*.php');

			foreach ( $base_install_classes as $path ){

				if( file_exists($path) ){
					include_once ($path);
				}
			}

			$this->install_classes_loaded = true;

		}

	}


	/**
         * Places the system in "uninstall mode" by loading all the base uninstall classes
	 * from the /core/config/uninstall_base folder
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function uninstallMode(){

		if(!$this->uninstall_classes_loaded){

			$base_uninstall_classes = glob( BPM_PATH_BASE .'/core/config/uninstall_base/*.php');

			foreach ( $base_uninstall_classes as $path ){

				if( file_exists($path) ){
					include_once ($path);
				}
			}

			$this->uninstall_classes_loaded = true;

		}

	}



} // End of class BPM_config


/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_BPM_config(){

	$cls = new BPM_config();
	$cls->install();
}
add_action( 'bpm_install', 'install_BPM_config', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_BPM_config(){

	$cls = new BPM_config();
	$cls->uninstall();
}
add_action( 'bpm_uninstall', 'uninstall_BPM_config', 2 );


?>