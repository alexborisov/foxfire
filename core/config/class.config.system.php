<?php

/**
 * FOXFIRE CONFIGURATION CLASS
 * Handles all configuration settings for the plugin
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

class FOX_config extends FOX_dataStore_paged_L4_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache
					    // locking. Inherited from FOX_dataStore_monolithic_L3_base.

	var $mCache;			    // Local copy of memory cache singleton

	var $install_classes_loaded;	    // True if the base install classes have been loaded
	var $uninstall_classes_loaded;	    // True if the base uninstall classes have been loaded

	var $key_delimiter = "^";	    // The character the class uses to split keys into "tree", "branch", and "node"
					    // when parsing returned forms.

	// ============================================================================================================ //

	public static $struct = array(

		"table" => "FOX_config",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_config",
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
			global $fox;
			$this->process_id = &$fox->process_id;
		}
		
		$this->init();		

	}
	
	/**
	 * Fetches an entire plugin
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $tree | single tree as string. Multiple trees as array of string.
	 * @param bool $valid | true if all requested trees exist.
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getPlugin($plugin, &$valid=null){


		$struct = $this->_struct();
		$validator_result = array();

		try {

			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);				
	
			// If a single plugin name is sent in, we validate it individually instead of automatically 
			// spinning it into an array and validating the array. This lets us trap strings that PHP 
			// automatically converts to ints ("17")

			if( !is_array($plugin) ){

				$mode = 'single';
				
				$validator_result['tree'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L4_col]['php'],
									'format'=>'scalar',
									'var'=>$plugin
				));					
			}
			else {
				$mode = 'multi';
				
				foreach( $plugin as $key => $val ){

					$validator_result['tree'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L4_col]['php'],
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
					'data'=>array('plugin'=>$plugin, 'msg'=>$val),
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
			$db_result = parent::getL4($plugin, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::getL3()",
				'data'=> array('plugin'=>$plugin),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		$result = array();
			
		if($mode == 'single'){
		    
			// When operating in 'single' mode, getL4() will return NULL 
			// on no result
		    
			if($db_result){
			    
				foreach($db_result as $tree_name => $branches){

					foreach($branches as $branch_name => $nodes){

						foreach($nodes as $node_name => $node_data){

							$result[$tree_name][$branch_name][$node_name] = $node_data['val']; 
						}
						unset($node_name, $node_data);				
					}
					unset($branch_name, $nodes);
				}
				unset($tree_name, $branches);				
			}
			else {
				$result = null;
			}
			
		}
		else {
			
			foreach($db_result as $plugin_name => $trees){
			    
				foreach($trees as $tree_name => $branches){

					foreach($branches as $branch_name => $nodes){

						foreach($nodes as $node_name => $node_data){

							$result[$plugin_name][$tree_name][$branch_name][$node_name] = $node_data['val']; 
						}
						unset($node_name, $node_data);				
					}
					unset($branch_name, $nodes);
				}
				unset($tree_name, $branches);	
			}
			unset($plugin_name, $trees);			
		}
		
		return $result;

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

				$mode = 'single';
				
				$validator_result['tree'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L3_col]['php'],
									'format'=>'scalar',
									'var'=>$tree
				));					
			}
			else {
				$mode = 'multi';
				
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
			$db_result = parent::getL3($plugin, $tree, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::getL3()",
				'data'=> array('plugin'=>$plugin,'tree'=>$tree),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		$result = array();
		
		if($mode == 'single'){
		    
			// When operating in 'single' mode, getL3() will return NULL 
			// on no result
		    
			if($db_result){
			    
				foreach($db_result as $branch_name => $nodes){

					foreach($nodes as $node_name => $node_data){

						$result[$branch_name][$node_name] = $node_data['val']; 
					}
					unset($node_name, $node_data);				
				}
				unset($branch_name, $nodes);
			}
			else {
				$result = null;
			}
			
		}
		else {
			
			foreach($db_result as $tree_name => $branches){
			    
				foreach($branches as $branch_name => $nodes){

					foreach($nodes as $node_name => $node_data){

						$result[$tree_name][$branch_name][$node_name] = $node_data['val']; 
					}
					unset($node_name, $node_data);				
				}
				unset($branch_name, $nodes);
			}
			unset($tree_name, $branches);			
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

			if( !is_array($branch) ){

				$mode = 'single';
				
				$validator_result['branch'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L2_col]['php'],
									'format'=>'scalar',
									'var'=>$branch
				));					
			}
			else {
				$mode = 'multi';
				
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
			$db_result = parent::getL2($plugin, $tree, $branch, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::getL2()",
				'data'=> array('plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		$result = array();
			
		if($mode == 'single'){
		    
			// When operating in 'single' mode, getL2() will return NULL 
			// on no result
		    
			if($db_result){
			    
				foreach($db_result as $node_name => $node_data){

					$result[$node_name] = $node_data['val']; 
				}
				unset($node_name, $node_data);				
			}
			else {
				$result = null;
			}
		}
		else {			
			foreach($db_result as $branch_name => $nodes){
			    
				foreach($nodes as $node_name => $node_data){

					$result[$branch_name][$node_name] = $node_data['val']; 
				}
				unset($node_name, $node_data);				
			}
			unset($branch_name, $nodes);
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

			if( !is_array($node) ){

				$mode = 'single';
				
				$validator_result['node'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L1_col]['php'],
									'format'=>'scalar',
									'var'=>$node
				));					
			}
			else {
				$mode = 'multi';
				
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
			$db_result = parent::getL1($plugin, $tree, $branch, $node, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::getL1()",
				'data'=> array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		
		if($mode == 'single'){
		    
			$result = $db_result['val'];
		}
		else {
			$result = array();
			
			foreach($db_result as $node_name => $node_data){

				$result[$node_name] = $node_data['val']; 
			}
			unset($node_name, $node_data);
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
	 * @return int | Exception on failure. (int)0 existing node unchaged, (int)1 new node inserted, (int)2 existing node updated
	 */

	public function addNode($plugin, $tree, $branch, $node, $node_val, $filter, $ctrl=null){


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
				'data'=> array( 'plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'node_val'=>$node_val,
						'filter'=>$filter),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Run node value through the specified filter
		// =====================================================

		$filter_valid = null; $filter_error = null; // Passed by reference

		try {
			$processed_val = $cls->{$filter}($node_val, $ctrl, $filter_valid, $filter_error);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error in filter function",
				'data'=>array(  'plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'node_val'=>$node_val,
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
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'node_val'=>$node_val,
					      'filter'=>$filter, 'ctrl'=>$ctrl,
					      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Update the database
		// ===========================================================

		$set_val = array(
			    'filter'=>$filter,
			    'filter_ctrl'=>$ctrl,
			    'val'=>$processed_val		    
		);

		$set_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = parent::setL1($plugin, $tree, $branch, $node, $set_val, $set_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in parent::setL1()",
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'set_val'=>$set_val),
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
	 * @return int | Exception on failure. (int)0 node unchaged, (int)1 node updated
	 */

	public function setNode($plugin, $tree, $branch, $node, $node_val){


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
			$node_data = parent::getL1($plugin, $tree, $branch, $node, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::getL1()",
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
			$processed_val = $cls->{$node_data["filter"]}($node_val, $node_data["filter_ctrl"], $filter_valid, $filter_error);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in filter function",
				'data'=>array(  'plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'node_val'=>$node_val,
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
				'data'=>array( 'plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'node_val'=>$node_val,
					       'filter'=>$node_data["filter"], 'filter_ctrl'=>$node_data["filter_ctrl"],
					       'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Update the database
		// ===========================================================

		$set_val = array(
			    'filter'=>$node_data["filter"],
			    'filter_ctrl'=>$node_data["filter_ctrl"],
			    'val'=>$processed_val		    
		);

		$set_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = parent::setL1($plugin, $tree, $branch, $node, $set_val, $set_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in parent::setL1()",
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node, 'set_val'=>$set_val),
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

	public function dropNode($plugin, $tree, $branch, $node) {


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

			if( !is_array($node) ){

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
					'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$key, 'msg'=>$val),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);

		
		$drop_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = parent::dropL1($plugin, $tree, $branch, $node, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::dropL1()",
				'data'=> array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'node'=>$node),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

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

	public function dropBranch($plugin, $tree, $branch) {


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

			if( !is_array($branch) ){

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
		
		$drop_ctrl = array(
			'validate'=>false	    
		);
		
		try {
			$rows_changed = parent::dropL2($plugin, $tree, $branch, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::dropL2()",
				'data'=> array('plugin'=>$plugin,'tree'=>$tree, 'branch'=>$branch),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

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

	public function dropTree($plugin, $tree) {


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
		
		$drop_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = parent::dropL3($plugin, $tree, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::dropL3()",
				'data'=> array('plugin'=>$plugin,'tree'=>$tree),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return (int)$rows_changed;

	}


	/**
	 * Deletes one or more plugins from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $plugin | Single plugin as string. Multiple plugins as array of string.
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function dropPlugin($plugin) {


		$struct = $this->_struct();
		$validator_result = array();

		try {	
	
			// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
			// a validator throws an exception, it will contain all info needed for debugging

			$validator = new FOX_dataStore_validator($struct);
			
			// If a single tree name is sent in, we validate it individually instead of automatically 
			// spinning it into an array and validating the array. This lets us trap strings that PHP 
			// automatically converts to ints ("17")

			if( !is_array($plugin) ){

				$validator_result['plugin'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L4_col]['php'],
									'format'=>'scalar',
									'var'=>$plugin
				));					
			}
			else {

				foreach( $plugin as $key => $val ){

					$validator_result['plugin'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L4_col]['php'],
										'format'=>'scalar',
										'var'=>$val
					));	

					if( $validator_result['plugin'] !== true ){

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
					'data'=>array('plugin'=>$plugin, 'msg'=>$val),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    

		}
		unset($key, $val);			
		
		$drop_ctrl = array(
			'validate'=>false		    
		);
		
		try {
			$rows_changed = parent::dropL4($plugin, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling parent::dropL4()",
				'data'=> array('plugin'=>$plugin),
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
			$raw_plugin = trim( $full_name[0] );			
			$raw_tree = trim( $full_name[1] );
			$raw_branch = trim( $full_name[2] );
			$raw_key = trim( $full_name[3] );

			// Passed by reference
			$plugin_valid = null; $tree_valid = null; $branch_valid = null; $key_valid = null;
			$plugin_error = null; $tree_error = null; $branch_error = null; $key_error = null;

			try {
				$plugin = $san->keyName($raw_plugin, null, $plugin_valid, $plugin_error);			    
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

			if(!$plugin_valid){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Called with invalid plugin name",
					'data'=>array('raw_tree'=>$raw_plugin, 'san_error'=>$plugin_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}			
			elseif(!$tree_valid){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Called with invalid tree name",
					'data'=>array('raw_tree'=>$raw_tree, 'san_error'=>$tree_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$branch_valid){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Called with invalid branch name",
					'data'=>array('raw_branch'=>$raw_branch, 'san_error'=>$branch_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$key_valid){

				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Called with invalid key name",
					'data'=>array('raw_key'=>$raw_key, 'san_error'=>$key_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

			$query_keys[$plugin][$tree][$branch][$key] = true;

			unset($option, $plugin, $raw_plugin, $tree, $raw_tree, $branch, $raw_branch, $key, $raw_key);
			unset($plugin_error, $tree_error, $branch_error, $key_error, $plugin_valid, $tree_valid, $branch_valid, $key_valid);
		
		}

		if( count( array_keys($query_keys) ) > 1){

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Attempting to set keys for multiple plugins",
				'data'=>array_keys($query_keys),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		$get_ctrl = array(
			'validate'=>false,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		try {
			// We do not use getMulti's $valid flag to trap nonexistent keys
			// because we want to throw an error listing the key names
		    
			$current_keys = parent::getMulti($query_keys, $get_ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error calling parent::getMulti()",
				'data'=>$query_keys,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		// Iterate through all of the requested keys, cast the submitted form
		// data for each key to the format specified in the key's db entry
		// ====================================================================

		$rows_changed = 0;
		$invalid_keys = array();
		$update_keys = array();

		foreach($query_keys as $plugin => $trees){
		    
			foreach($trees as $tree => $branches){

				foreach($branches as $branch => $nodes){

					foreach($nodes as $node => $fake_var){

						if( !FOX_sUtil::keyExists($current_keys[$plugin][$tree][$branch], $node) ){
						    
							$invalid_keys[] = "Plugin: $plugin Tree: $tree Branch: $branch Key: $key";
							continue;
						}
							
						$filter = $current_keys[$plugin][$tree][$branch][$node]["filter"];
						$filter_ctrl = $current_keys[$plugin][$tree][$branch][$node]["filter_ctrl"];

						// Remove any escaping PHP has added to the posted form value						
						$post_key =  $plugin . $this->key_delimiter . $tree . $this->key_delimiter;
						$post_key .= $branch . $this->key_delimiter . $key;
						$post[$post_key] = FOX_sUtil::formVal($post[$post_key]);


						// Run key value through the specified filter
						// =====================================================

						$filter_valid = null; $filter_error = null; // Passed by reference

						try {
							$new_val = $san->{$filter}($post[$post_key], $filter_ctrl, $filter_valid, $filter_error);
						}
						catch (FOX_exception $child) {

							throw new FOX_exception( array(
								'numeric'=>9,
								'text'=>"Error in filter function",
								'data'=>array('filter'=>$filter, 'val'=>$post[$post_key], 'ctrl'=>$ctrl,
									    'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							));
						}

						if(!$filter_valid){

							throw new FOX_exception( array(
								'numeric'=>10,
								'text'=>"Filter function reports value data isn't valid",
								'data'=>array('filter'=>$filter, 'val'=>$post[$post_key], 'ctrl'=>$ctrl,
									    'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>null
							));
						}

						// If the new value doesn't match the stored value, update the key
						// ====================================================================

						if( $new_val != $current_keys[$plugin][$tree][$branch][$node]["val"]){

							$update_keys[$plugin][$tree][$branch][$node] = array(
								'filter'=>$filter,
								'filter_ctrl'=>$filter_ctrl,
								'val'=>$new_val							    
							);
						}


					}   // ENDOF: foreach($nodes as $node => $fake_var)
					unset($node, $fake_var);

				} // ENDOF: foreach($branches as $branch => $nodes)
				unset($branch, $nodes);

			} // ENDOF: foreach($trees as $tree => $branches)
			unset($tree, $branches);
		
		} // ENDOF: foreach($query_keys as $plugin => $trees)
		unset($plugin, $trees);

		
		if($invalid_keys){
		    
			throw new FOX_exception( array(
				'numeric'=>11,
				'text'=>"Attempted to set one or more nonexistent keys",
				'data'=>$invalid_keys,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}
		
		if($update_keys){
		    
			$set_ctrl = array(
				'validate'=>false,
				'mode'=>'trie'
			);
		
			try {
				$rows_changed = parent::setMulti($update_keys, $set_ctrl);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>12,
					'text'=>"Error in parent::setMulti()",
					'data'=>array('update_keys'=>$update_keys, 'set_ctrl'=>$set_ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}		    
		    
		}		

		
		return (int)$rows_changed;



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

	public function printKeyName($plugin, $tree, $branch, $key){


		echo 'name="' . self::getKeyName($plugin, $tree, $branch, $key) . '"';

	}

	public function getKeyName($plugin, $tree, $branch, $key){

		$key_name = ($plugin . $this->key_delimiter . $tree . $this->key_delimiter . $branch . $this->key_delimiter . $key);

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

	public function printKeyVal($plugin, $tree, $branch, $key, $validate=false){


		$result = 'value="';
		$is_valid = false; // Passed by reference


		try {
			$result .= self::getNode($plugin, $tree, $branch, $key, $is_valid);
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
				'data'=>array('plugin'=>$plugin, 'tree'=>$tree, 'branch'=>$branch, 'key'=>$key),
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

		foreach( $this->print_keys as $key_name => $fake_var) {

			$result .= $key_name;

			if($keys_left != 0){
				$result .= ", ";
				$keys_left--;
			}
		}
		unset($fake_var);

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