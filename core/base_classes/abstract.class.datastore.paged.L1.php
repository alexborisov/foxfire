<?php

/**
 * FOXFIRE L1 PAGED ABSTRACT DATASTORE CLASS
 * Implements a highly efficient 1st order paged datastore
 * 
 * FEATURES
 * --------------------------------------
 *  -> ACID compliant
 *  -> Transient cache support
 *  -> Persistent cache support
 *  -> Progressive cache loading
 *  -> SQL transaction support
 *  -> Fully atomic operations
 *  -> Advanced error handling
 *  -> Multi-thread safe
 *  -> Multi-server safe 
 *	
 * DESIGN NOTES
 * --------------------------------------
 *  -> For detailed info about why class features are implemented in
 *     specific ways, see the notes section at the end of this file.
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Base Classes
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

abstract class FOX_dataStore_paged_L1_base extends FOX_db_base {

    
    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache 
					    // locking. Loaded by descendent class.
	
	var $cache;			    // Main cache array for this class
	
	var $db;			    // Local copy of database singleton
	
	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache 
					    // operations. Loaded by descendent class.	
	
	var $wildcard = '*';		    // String to use a the "wildcard" character when using trie structures as
					    // selectors. Since "*" is an illegal name for an SQL column, it will never
					    // create conflicts. @see http://en.wikipedia.org/wiki/Wildcard_character
	
	var $hashing_on;		    // Hash walk token values
	var $hashtable;			    // Hash table instance used for hashing tokens
	
	var $debug_on;			    // Send debugging info to the debug handler	
	var $debug_handler;		    // Local copy of debug singleton	
	

	/* ================================================================================================================
	 *	Cache Strategy: "paged"
	 *
	 *	=> ARR array $cache | Cache pages array
	 * 
 	 *	    => ARR array $L5 | Single cache page    ---------------------------------------------------------
	 * 
	 *		=> ARR @param array $keys | L5 datastore
	 *		    => ARR string '' | L4 id
	 *			=> ARR string '' | L3 id
	 *			    => ARR string | L2 id
	 *				=> KEY string | L1 id
	 *				    => VAL mixed | serialized key data
	 * 
	 *		=> VAL bool $all_cached | True if cache page has authority (all rows loaded from db)
	 * 
	 *		=> ARR array $L4 | L4 cache LUT
	 *		    => KEY string '' | L4 id
	 *			=> VAL bool | True if L4 node has authority. False if not.
	 * 
	 *		=> ARR array $L3 | L3 cache LUT
	 *		    => ARR string '' | L4 id
	 *			=> KEY string '' | L3 id
	 *			    => VAL bool | True if L3 node has authority. False if not.
	 * 
	 *		=> ARR array $L2 | L2 cache LUT
	 *		    => ARR string '' | L4 id
	 *			=> ARR string '' | L3 id 
	 *			    => KEY string | L2 id
	 *				=> VAL bool | True if L2 node has authority. False if not.
	 * 
	 *	    ----------------------------------------------------------------------------------------------------
	 * 
	 * */


	// ============================================================================================================ //

	
	/**
	 * Initializes the class. This function MUST be called in the __construct() method
	 * of descendent classes. 
	 * 
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function init($args=null){
	    
	
		// Column hashing
		// ===========================================================
	    
		if(FOX_sUtil::keyExists('hash_columns', $args) ){
		    
			$this->hashtable = new FOX_hashTable();
			$this->hashing_on = true;		    
		}
		else {
			$this->hashing_on = false;		    		    
		}
		
		// Debug events
		// ===========================================================
		
		if(FOX_sUtil::keyExists('debug_on', $args) && ($args['debug_on'] == true) ){
		    
			$this->debug_on = true;
		    
			if(FOX_sUtil::keyExists('debug_handler', $args)){

				$this->debug_handler =& $args['debug_handler'];		    
			}
			else {
				global $fox;
				$this->debug_handler =& $fox->debug_handler;		    		    
			}	    
		}
		else {
			$this->debug_on = false;		    		    
		}
		
		// Database singleton
		// ===========================================================
		
		if(FOX_sUtil::keyExists('db', $args) ){
		    
			$this->db =& $args['db'];		    
		}
		else {
			$this->db = new FOX_db( array('pid'=>$this->process_id) );		    		    
		}			
			    
		
                $struct = $this->_struct();		
		$columns = array_keys($struct['columns']);
		
		$this->L5_col = $columns[0];		
		$this->L4_col = $columns[1];
		$this->L3_col = $columns[2];
		$this->L2_col = $columns[3];
		$this->L1_col = $columns[4];	
		$this->L0_col = $columns[5];		
		
		$this->order = 5;
								
		$this->init = true;
	    
	}
	    
	
	/**
	 * Fetches one or more L1 objects from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2 | Single L2
	 * @param int/string $L1s | Single L1 as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $r_mode | Response format - 'matrix' | 'trie'
	 * 
	 * @param bool &$valid | True if all requested objects exist, false if not.
	 * @return mixed | Exception on failure. Data object on success.
	 */

	public function getL1($L5, $L4, $L3, $L2, $L1s, $ctrl=null, &$valid=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if($this->debug_on){
		    
			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'r_mode'=>'trie'		    
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);
		
				 
		if($ctrl['validate'] != false){		   

			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"validate_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}	
			
			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator_result = array();
			
			try {
			    
				// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
				// a validator throws an exception, it will contain all info needed for debugging
			    
				$validator = new FOX_dataStore_validator($struct);	

				$validator_result['L5'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L5_col]['php'],
									'format'=>'scalar',
									'var'=>$L5
				));							

				$validator_result['L4'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L4_col]['php'],
									'format'=>'scalar',
									'var'=>$L4
				));	

				$validator_result['L3'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L3_col]['php'],
									'format'=>'scalar',
									'var'=>$L3
				));	

				$validator_result['L2'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L2_col]['php'],
									'format'=>'scalar',
									'var'=>$L2
				));	
				
				// If a single L1 is sent in, we validate it *before* spinning it into an array,
				// so we can trap strings that PHP automatically converts to ints ("17")
				
				if( !is_array($L1s) ){

					$validator_result['L1'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L1_col]['php'],
										'format'=>'scalar',
										'var'=>$L1s
					));					
				}
				else {

					foreach( $L1s as $key => $val ){

						$validator_result['L1'] = $validator->validateKey( array(
											'type'=>$struct['columns'][$this->L1_col]['php'],
											'format'=>'scalar',
											'var'=>$val
						));	
						
						if( $validator_result['L1'] !== true ){

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
						'text'=>"Invalid " . $key . " key",
						'data'=>$val,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));			    
				}			    
			    
			}
			unset($key, $val);
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"validate_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			
			
		}		

		// Fetch items
		// ==========================
		
		if( !is_array($L1s) ){
		    
			$single = true;
			$L1s = array($L1s);
		}
		else{
			$single = false;
		}		
		
		$get_data = array( $L5=>array( $L4=>array( $L3=>array( $L2=>array() ))));

		foreach( $L1s as $L1 ){
		    
			$get_data[$L5][$L4][$L3][$L2][$L1] = true;			
		}
		unset($L1);
				
		
		$get_ctrl = array(
				    'validate'=>false,
				    'q_mode'=>'trie',
				    'r_mode'=>$ctrl['r_mode']
		);
				
		try {
			$result = self::getMulti($get_data, $get_ctrl, $valid);	
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error calling self::getMulti()",
				'data'=>array('get_data'=>$get_data, 'get_ctrl'=>$get_ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		
		if($single){
		    
			// If operating in single-item mode, 'lift' the data object out 
			// of the results array
		    
			if( $get_ctrl['r_mode'] == 'matrix' ){
			    
				$result = array_shift($result);
				$result = $result[$this->L0_col];
			}
			else {	
				$L1 = array_pop($L1s);			    
				$result = $result[$L5][$L4][$L3][$L2][$L1];
			}
		}
		else {
		    
			// If using the 'trie' response format with multiple L1 end  
			// nodes, 'lift' the parent L2 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$result = $result[$L5][$L4][$L3][$L2];
			}		    
		    
		}
		
		if($this->debug_on){
		    
			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return $result;

	}
	
	
	/**
	 * Fetches multiple L5->L1 walks from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * [MATRIX MODE] 
         * @param array $data | Array of row arrays 
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string $L1 | Single L1 id as int/string
	 * 
	 * [TRIE MODE]
         * @param array $data | array of L5's in the form "L5_id"=>"L4s"	
	 *	=> ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"	 
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param NULL	 
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $q_mode | Query format - 'matrix' | 'trie'
	 *	=> VAL @param string $r_mode | Response format - 'matrix' | 'trie'
	 * 
	 * @param bool &$valid | True if all requested objects exist, false if not.	 
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getMulti($data, $ctrl=null, &$valid=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){
		    
			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);		
					
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();				
		
		$get_data = array();
			
		if($ctrl['q_mode'] == 'matrix'){
		    
				
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,					    
						'text'=>"matrix_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}			    
			    
				$row_valid = false;			    

				try {			    
					$validator = new FOX_dataStore_validator($struct);	
				
					$row_ctrl = array(				    
							    'end_node_format'=>'scalar'			    
					);

					foreach( $data as $row ){   
						
						$row_valid = $validator->validateMatrixRow($row, $row_ctrl);
				    					
						if($row_valid !== true){						    
							break;
						}
						
					}																
					unset($row);
				
				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}
				
				if($row_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));					    					    
				}
				
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,					    
						'text'=>"matrix_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}				
			
			}

			// Loft the individual rows into a trie (to merge overlapping objects) then clip
			// the tree to get the highest order objects we need to fetch
			// =============================================================================			
				
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"matrix_transform_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
				
			$columns = array(
					$this->L5_col, 
					$this->L4_col, 
					$this->L3_col, 
					$this->L2_col, 
					$this->L1_col
			);
			
			$trie = FOX_trie::loftMatrix($data, $columns, $loft_ctrl=null);
			
			$clip_ctrl = null;
			
			try {
				$get_data = FOX_trie::clipAssocTrie($trie, $columns, $clip_ctrl);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in FOX_trie::clipAssocTrie()",
					'data'=>array('trie'=>$trie, 'columns'=>$columns, 'clip_ctrl'=>$clip_ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"matrix_transform_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			
						
		}
		elseif($ctrl['q_mode'] == 'trie'){		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   
			    
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,					    
						'text'=>"trie_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}			    

				$struct = $this->_struct();

				try {			    
					$validator = new FOX_dataStore_validator($struct);
				
					$val_ctrl = array(
						'order'=>$this->order,
						'mode'=>'control'				    
					);

					$tree_valid = $validator->validateTrie($data, $val_ctrl);
					
				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}				

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));			    
				}
				
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,					    
						'text'=>"trie_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}				
			}
			
			$get_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Invalid ctrl['q_mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		    
		}
		
		
		// Trap "SELECT * WHERE TRUE"
		// ===========================================================		
						    
		if( $ctrl['trap_*'] == true ){

			if( !array_keys($get_data) ){

			    // @see http://en.wikipedia.org/wiki/Universal_set 

			    $error_msg =  "INTERLOCK TRIP: One or more of the conditions set in the \$data array reduces to the universal set, ";
			    $error_msg .= "which is equivalent to 'WHERE 1 = 1'. Running this command would have selected the entire datastore. ";				
			    $error_msg .= "If this is actually your design intent, set \$ctrl['trap_*'] = false to disable this interlock."; 

			    throw new FOX_exception( array(
				    'numeric'=>7,
				    'text'=>"$error_msg",
				    'data'=>$data,
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
			    ));	

			}
		}		    		    				
		
		// Find all requested objects that don't have authority in the class cache (L5 to L2),
		// or which don't exist in the class cache (L1) and try to load them from the persistent cache
		// ==============================================================================================
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"class_cache_prescan_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
			
		try {
			$cache_fetch = self::notInClassCache($get_data, $this->cache);	
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error in self::notInClassCache()",
				'data'=>array('get_data'=>$get_data),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    			
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"class_cache_prescan_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		
		
		if($cache_fetch){
		    
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"persistent_cache_fetch_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
				
			try {			    
				$cache_pages = self::readCachePage( array_keys($cache_fetch) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>9,
					'text'=>"Error reading from persistent cache",
					'data'=>array('cache_fetch'=>array_keys($cache_fetch)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    			
			}
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"persistent_cache_fetch_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			

			foreach( $cache_pages as $page_id => $page_image ){

				// NOTE: because of the way cache operations are implemented throughout
				// this class, the persistent cache *always* has authority over the class
				// cache, which makes it impossible for a situation to occur where there
				// are entries in the class cache that are not in the persistent cache. As
				// a result, we can safely overwrite pages in the class cache with pages 
				// from the persistent cache.

				$this->cache[$page_id] = $page_image;

			}
			unset($page_id, $page_image);


			// Find all requested objects that didn't have authority in the class cache (L5 to L2),
			// or which didn't exist in the class cache (L1) and try to load them from the database
			// =====================================================================================		

			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"class_cache_postscan_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
				
			$db_fetch = self::notInClassCache($cache_fetch, $this->cache);
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"class_cache_postscan_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			

			if($db_fetch){

				// Lock affected cache pages for all requested items, to prevent
				// corrupted keys in case another thread attempts to do a write
				// operation while we're reading from the database
				// ===========================================================

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"persistent_cache_lock_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}

				try {
					self::lockCachePage( array_keys($db_fetch) );

				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>10,
						'text'=>"Error locking cache",
						'data'=>$db_fetch,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));		    
				}

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"persistent_cache_lock_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}	
				
				$columns = null;

				$args = array(
						'key_col'=>array(
								    $this->L5_col, 
								    $this->L4_col, 
								    $this->L3_col, 
								    $this->L2_col,
								    $this->L1_col				    
						),
						'args'=>$db_fetch
				);

				$db_ctrl = array(
						    'args_format'=>'trie',
						    'format'=>'array_key_array',
						    'key_col'=>array(
									$this->L5_col,
									$this->L4_col,
									$this->L3_col,
									$this->L2_col,
									$this->L1_col
						    ),		    
						    'hash_key_vals'=>$this->hashing_on 
				);			

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,					    
						'text'=>"db_fetch_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
					
				try {
					$db_result = $this->db->runSelectQuery($struct, $args, $columns, $db_ctrl);	
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>11,
						'text'=>"Error while reading from database",
						'data'=>array('args'=>$args, 'columns'=>$columns, 'db_ctrl'=>$db_ctrl),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));		    			
				}
				
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,					    
						'text'=>"db_fetch_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}				

				if($db_result){

					if($this->debug_on){

						extract( $this->debug_handler->event( array(
							'pid'=>$this->process_id,						    
							'text'=>"db_result_transform_start",
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'parent'=>$this,
							'vars'=>compact(array_keys(get_defined_vars()))
						)));		    
					}
					
					// Copy L5 pages in $this->cache to $update_cache as needed, then merge the db
					// results into the L5 $update_cache pages. This saves memory over duplicating
					// the entire $this->cache variable.
					// ============================================================================				

					$update_cache = array();

					foreach( $db_fetch as $L5 => $L4s ){		

						// If we're loading an entire L5 page from the db, we don't need to
						// merge into the class cache page (if it exists) because the result
						// from the database has authority
								
						if( count($L4s) == 0 ){	    

							// Overwrite keys

							if( FOX_sUtil::keyExists($L5, $db_result) ){

								// The L5 object now has authority
								$update_cache[$L5]['all_cached'] = true;

								// Update descendent LUT's
								unset($update_cache[$L5][$this->L4_col][$L4]);
								unset($update_cache[$L5][$this->L3_col][$L4]);
								unset($update_cache[$L5][$this->L2_col][$L4]);

								$update_cache[$L5]["keys"] = $db_result[$L5];				
							}
						}
						else {
							// However, for L4 or lower order objects, we have to merge into
							// the L5 class cache page (if it exists) because the database
							// result doesn't have L5 authority

							if( FOX_sUtil::keyExists($L5, $this->cache) ){

								$update_cache[$L5] = $this->cache[$L5];				
							}
						}

						foreach( $L4s as $L4 => $L3s ){

							if( count($L3s) == 0 ){	

								if( FOX_sUtil::keyExists($L4, $db_result[$L5]) ){

									// The L4 object now has authority
									$update_cache[$L5][$this->L4_col][$L4] = true;

									// Update descendent LUT's
									unset($update_cache[$L5][$this->L3_col][$L4]);
									unset($update_cache[$L5][$this->L2_col][$L4]);	
									
									$update_cache[$L5]["keys"][$L4] = $db_result[$L5][$L4];				
								}
							}			    

							foreach( $L3s as $L3 => $L2s ){

								if( count($L2s) == 0 ){

									if( FOX_sUtil::keyExists($L3, $db_result[$L5][$L4]) ){

										// The L3 object now has authority
										$update_cache[$L5][$this->L3_col][$L4][$L3] = true;

										// Update descendent LUT's
										unset($update_cache[$L5][$this->L2_col][$L4][$L3]);	
										
										$update_cache[$L5]["keys"][$L4][$L3] = $db_result[$L5][$L4][$L3];				
									}
								}				    

								foreach( $L2s as $L2 => $L1s ){

									if( count($L1s) == 0 ){	    

										if( FOX_sUtil::keyExists($L2, $db_result[$L5][$L4][$L3]) ){

											// The L2 object now has authority
											$update_cache[$L5][$this->L2_col][$L4][$L3][$L2] = true;
											
											$update_cache[$L5]["keys"][$L4][$L3][$L2] = $db_result[$L5][$L4][$L3][$L2];				
										}
									}

									foreach( $L1s as $L1 => $fake_var){

										if( FOX_sUtil::keyExists($L1, $db_result[$L5][$L4][$L3][$L2]) ){

											$update_cache[$L5]["keys"][$L4][$L3][$L2][$L1] = $db_result[$L5][$L4][$L3][$L2][$L1];				
										}
									}
									unset($L1, $fake_var);
								}
								unset($L2, $L1s);
							}
							unset($L3, $L2s);
						}
						unset($L4, $L3s);


						// Clear empty walks from the LUT's
						// ==========================================================================		

						if( FOX_sUtil::keyExists($L5, $db_result) ){
						    
							$update_cache[$L5][$this->L2_col] = FOX_sUtil::arrayPrune($update_cache[$L5][$this->L2_col], 3);
							$update_cache[$L5][$this->L3_col] = FOX_sUtil::arrayPrune($update_cache[$L5][$this->L3_col], 2);	
							$update_cache[$L5][$this->L4_col] = FOX_sUtil::arrayPrune($update_cache[$L5][$this->L4_col], 1);
						}

					}
					unset($L5, $L4s);
					
					if($this->debug_on){

						extract( $this->debug_handler->event( array(
							'pid'=>$this->process_id,						    
							'text'=>"db_result_transform_end",
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'parent'=>$this,
							'vars'=>compact(array_keys(get_defined_vars()))
						)));		    
					}									
					
				}  // ENDOF: if($db_result)

				
			}  // ENDOF: if($db_fetch)
			
		
		} // ENDOF: if($cache_fetch)
		
		
		// Build the updated cache image, and generate the result from the image (this 
		// lets us still return a result in the event of a cache write failure)
		// ==========================================================================			
					
		if($update_cache){
		    
			$cache_image = $this->cache;
		    
			foreach( $update_cache as $page_id => $page_image ){

				$cache_image[$page_id] = $page_image;
			}
			unset($page_id, $page_image);		
		}
		else {
			// Just bind by reference to save memory
			$cache_image =& $this->cache;
		}
		
		$result = array();
		$valid = true;		
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"result_build_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		foreach( $get_data as $L5 => $L4s ){		
					    
			// Handle "true", "null" etc end nodes. The algorithm is implemented this
			// way to avoid excessive if-else nesting indentation. We know that any
			// non-array keys are valid end nodes because the trie passed validation
			// at the beginning of the class method
		    
			if( !is_array($L4s) ){	 
			    
				$L4s = array();	
			}
				
			if( count($L4s) == 0 ){	    
			    				
				if( FOX_sUtil::keyExists($L5, $cache_image) ){
				    
					$result[$L5] = $cache_image[$L5]['keys'];				
				}
				else {
					$valid = false;
				}
			}
			
			foreach( $L4s as $L4 => $L3s ){
			    
				if( !is_array($L3s) ){	    
							    
					$L3s = array();	
				}
			
				if( count($L3s) == 0 ){	

					if( FOX_sUtil::keyExists($L4, $cache_image[$L5]['keys']) ){

						$result[$L5][$L4] = $cache_image[$L5]['keys'][$L4];				
					}
					else {
						$valid = false;
					}					
				}			    

				foreach( $L3s as $L3 => $L2s ){
				    
					if( !is_array($L2s) ){	    

						$L2s = array();	
					}
					
					if( count($L2s) == 0 ){

						if( FOX_sUtil::keyExists($L3, $cache_image[$L5]['keys'][$L4]) ){

							$result[$L5][$L4][$L3] = $cache_image[$L5]['keys'][$L4][$L3];				
						}
						else {
							$valid = false;
						}						
					}				    

					foreach( $L2s as $L2 => $L1s ){

						if( !is_array($L1s) ){	    

							$L1s = array();	
						}
				
						if( count($L1s) == 0 ){	    
						    
							if( FOX_sUtil::keyExists($L2, $cache_image[$L5]['keys'][$L4][$L3]) ){

								$result[$L5][$L4][$L3][$L2] = $cache_image[$L5]['keys'][$L4][$L3][$L2];				
							}
							else {
								$valid = false;
							}							
						}
					
						foreach( $L1s as $L1 => $fake_var){						    
							
							if( FOX_sUtil::keyExists($L1, $cache_image[$L5]['keys'][$L4][$L3][$L2]) ){
							    
								$result[$L5][$L4][$L3][$L2][$L1] = $cache_image[$L5]['keys'][$L4][$L3][$L2][$L1];				
							}
							else {
								$valid = false;
							}							
						}
						unset($L1, $fake_var);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			}
			unset($L4, $L3s);
		}
		unset($L5, $L4s);
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"result_build_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		if($ctrl['r_mode'] == 'matrix'){
		    
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"result_matrix_transform_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
			$flatten_ctrl = array('mode'=>'data');	    // Set to 'data' mode so flattenAssocTrie() 
								    // returns the values inside the trie's end nodes			
			$flatten_cols = array(
						$this->L5_col,
						$this->L4_col,
						$this->L3_col,
						$this->L2_col,
						$this->L1_col,
						$this->L0_col	    // Include the L0 column so flattenAssocTrie()								    
			);					    // has a key name to put the end node value in
			
			try {
				$result = FOX_trie::flattenAssocTrie($result, $flatten_cols, $flatten_ctrl);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>12,
					'text'=>"Error converting result to 'matrix' format",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"result_matrix_transform_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			
		    
		}
		elseif($ctrl['r_mode'] != 'trie'){
		    
			throw new FOX_exception( array(
				'numeric'=>13,
				'text'=>"Invalid ctrl['r_mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		    
		}
		
		
		// Write updated page images to persistent cache
		// ===========================================================

		if($update_cache){  // Trap no changed pages
		    
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"persistent_cache_write_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
			try {
				self::writeCachePage($update_cache);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>14,
					'text'=>"Error writing to persistent cache",
					'data'=>array('update_cache'=>$update_cache, 'result'=>$result),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"persistent_cache_write_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			
		}
		
		// Flush any locked persistent cache pages that were created
		// for keys which didn't exist in the database
		// ===========================================================
		
		if($db_fetch){
		    
			$updated_pages = array();
		    
			if($update_cache){
			
				$updated_pages = array_keys($update_cache);
			}
			
			$flush_pages = array_diff( array_keys($db_fetch), $updated_pages);		
		}

		if($flush_pages){ 
		    
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"persistent_cache_flush_pages_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
			try {
				self::flushCachePage($flush_pages);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>15,
					'text'=>"Error flushing pages from persistent cache",
					'data'=>array('flush_pages'=>$flush_pages),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,				    
					'text'=>"persistent_cache_flush_pages_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			
		}
		
		// Overwrite the class cache with the new cache image
		// ===========================================================
		
		$this->cache = $cache_image;
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
			
		return $result;
		
	}	
	
	
	/**
	 * Given a *minimum spanning trie* of objects to check, returns a minimum spanning trie
	 * of objects in the original trie which don't have authority in the class cache.
	 * 
	 * @see FOX_trie::clipAssocTrie()
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $data | array of L5's in the form "L5_id"=>"L4s"	
	 *	=> ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"	 
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param NULL	 
	 * 
	 * @param array $cache_image | cache image to check against
	 * 
	 * @return array | Exception on failure. Data array on success.
	 */
	
	public function notInClassCache($data, $cache_image){
	    
	    
		//  1) If an object has authority in the class cache skip it
		//  2) If the object doesn't have authority and has no descendents, flag it
		//  3) Otherwise, repeat the algorithm on the object's descendents
	    
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}	    
	    
		$result = array();
	    	    
		foreach( $data as $L5 => $L4s ){				    
		    
			$L5_has_auth = FOX_sUtil::keyTrue('all_cached', $cache_image[$L5]);
		    
			if(!$L5_has_auth){  					
			    		
			    	// Handle "true", "null" etc end nodes. The algorithm is implemented this
				// way to avoid excessive if-else nesting indentation. We know that any
				// non-array keys are valid end nodes because the trie passed validation
				// in the caller method
			    
				if( !is_array($L4s) ){	   
				    
					$L4s = array();	
				}
				
				if( count($L4s) == 0 ){	 
				    
					$result[$L5] = array();					
				}
				
				foreach( $L4s as $L4 => $L3s ){ 

					$L4_has_auth = FOX_sUtil::keyTrue($L4, $cache_image[$L5][$this->L4_col]);
					
					if(!$L4_has_auth){  

						if( !is_array($L3s) ){	    // Handle "true", "null" etc end nodes	

							$L3s = array();
						}	
						
						if( count($L3s) == 0 ){	    

							$result[$L5][$L4] = array();				
						}				

						foreach( $L3s as $L3 => $L2s ){

							$L3_has_auth = FOX_sUtil::keyTrue($L3, $cache_image[$L5][$this->L3_col][$L4]);
							
							if(!$L3_has_auth){ 			

								if( !is_array($L2s) ){	    // Handle "true", "null" etc end nodes	

									$L2s = array();
								}
						
								if( count($L2s) == 0 ){	    

									$result[$L5][$L4][$L3] = array();				
								}				    

								foreach( $L2s as $L2 => $L1s ){
								    
									$L2_has_auth = FOX_sUtil::keyTrue($L2, $cache_image[$L5][$this->L2_col][$L4][$L3]);
								    
									if(!$L2_has_auth){

										if( !is_array($L1s) ){	    // Handle "true", "null" etc end nodes	

											$L1s = array();
										}
						
										if( count($L1s) == 0 ) {

											$result[$L5][$L4][$L3][$L2] = array();				
										}

										foreach( $L1s as $L1 => $val){

											if( !FOX_sUtil::keyExists($L1, $cache_image[$L5]["keys"][$L4][$L3][$L2]) ){

												$result[$L5][$L4][$L3][$L2][$L1] = true;											
											}
										}
										unset($L1, $val);
									}
								}
								unset($L2, $L1s);
							}
						}
						unset($L3, $L2s);
					}
				}
				unset($L4, $L3s);
			}
		}
		unset($L5, $L4s);
		
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		
		return $result;
		
	    
	}
	
	
		
	// ###########################################################################################################################
	// ###########################################################################################################################
	
	
	/**
	 * Adds a single L1 walk that DOES NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2 | Single L2
	 * @param int/string $L1 | Single L1
	 * @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function addL1($L5, $L4, $L3, $L2, $L1, $val, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		
		
		$data = array( array( 
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3, 
				$this->L2_col=>$L2, 
				$this->L1_col=>$L1, 
				$this->L0_col=>$val
		));
		
		try {
			$result = self::addL1_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::addL1_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		
		return $result;

	}

	
	/**
	 * Adds multiple L1 walks which DO NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string $L1 | Single L1 id as int/string
	 *	    => VAL @param vs $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addL1_multi($data, $ctrl=null){
			
			
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	
		
		$ctrl['mode'] = 'matrix';
		
		
		try {						
			$result = self::addMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::addMulti()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return $result;

	}	
	
	
	/**
	 * Creates multiple L5->L1 walks. This method is used when adding walks that DO NOT ALREADY EXIST
	 * in the store. This is an atomic operation. In the event of a collision with one or more walks
	 * already present in the store, no data will be written, and the method will throw an error.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * [MATRIX MODE] 
         * @param array $data | Array of row arrays 
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string $L1 | Single L1 id as int/string
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
	 * [TRIE MODE]
         * @param array $data | array of L5's in the form "L5_id"=>"L4s"	
	 *	=> ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"	 
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value	 
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $mode | Operation mode 'matrix' | 'trie'
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addMulti($data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'mode'=>'trie'
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);		
			
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();		
		
		$update_data = array();
			
		if($ctrl['mode'] == 'matrix'){		   
		
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)	

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}			    

				$row_valid = false;	

				$row_ctrl = array(				    
						    'end_node_format'=>'scalar',
				);				

				try {			    
					$validator = new FOX_dataStore_validator($struct);
				
					foreach( $data as $id => $row ){   			

						$row_valid = $validator->validateMatrixRow($row, $row_ctrl);

						if($row_valid !== true){
							break;					    					    
						}

						$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];

					} 
					unset($id, $row);

				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}
				
				if($row_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));					    					    
				}				
			
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
			}
			else {
			    
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_transform_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
				foreach( $data as $row ){   
					
					$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];
				} 
				unset($row);	

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_transform_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
			}
							
		}
		elseif($ctrl['mode'] == 'trie'){		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	  
			    
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"trie_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}			    

				try {			    
					$validator = new FOX_dataStore_validator($struct);	
				
					$valid_ctrl = array(
						'order'=>$this->order,
						'mode'=>'data',
						'clip_order'=>0		    
					);

					$tree_valid = $validator->validateTrie($data, $valid_ctrl);
				
				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}				

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));			    
				}
				
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"trie_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}				
			}
			
			$update_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Invalid ctrl['mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		    
		}
				
		
		// Lock affected cache pages
		// ===========================================================

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
				
		try {
			$cache_pages = self::lockCachePage( array_keys($update_data) );
			$update_cache = $cache_pages;
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Error locking cache",
				'data'=>$update_data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}			
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		
		// Build db insert array and updated cache pages array
		// ===========================================================		

		$insert_data = array();
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		

		foreach( $update_data as $L5 => $L4s ){

			foreach( $L4s as $L4 => $L3s ){

				foreach( $L3s as $L3 => $L2s ){

					foreach( $L2s as $L2 => $L1s ){

						foreach( $L1s as $L1 => $val){

							$insert_data[] = array(
										$this->L5_col=>$L5,
										$this->L4_col=>$L4,
										$this->L3_col=>$L3,
										$this->L2_col=>$L2,
										$this->L1_col=>$L1,
										$this->L0_col=>$val
							);
								
							$update_cache[$L5]["keys"][$L4][$L3][$L2][$L1] = $val;

						}
						unset($L1, $val);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			}
			unset($L4, $L3s);
		}
		unset($L5, $L4s);
			
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}			
		
		// Write to database
		// ===========================================================
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_write_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		

		try {
			$rows_changed = $this->db->runInsertQueryMulti($struct, $insert_data, $columns=null);
		}
		catch (FOX_exception $child) {

		    
			// Try to unlock the cache pages we locked
		    
			try {
				self::writeCachePage($cache_pages);
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>8,
					'text'=>"Error while writing to the database. Error unlocking cache pages.",
					'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 'insert_data'=>$insert_data),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
			
		}
			
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_write_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		
				
		
		// Write updated cache page images to persistent cache
		// ===========================================================

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_write_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		try {
			self::writeCachePage($update_cache);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Error writing to cache",
				'data'=>$update_cache,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}			

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_write_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Write updated cache page images to class cache
		// ===========================================================
		
		foreach($update_cache as $L5 => $page_image){

			$this->cache[$L5] = $page_image;
		}
		unset($L5, $page_image);		


		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		
		return (int)$rows_changed;

	}	
	
	
	// ###########################################################################################################################
	// ###########################################################################################################################
	

	/**
	 * Creates or updates a single L1 walk which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2 | Single L2
	 * @param int/string $L1 | Single L1
	 * @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setL1($L5, $L4, $L3, $L2, $L1, $val, $ctrl=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3, 
				$this->L2_col=>$L2, 
				$this->L1_col=>$L1, 
				$this->L0_col=>$val
		));		

		
		try {
			$result = self::setL1_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::setL1_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return $result;

	}

	
	/**
	 * Creates or updates multiple L1 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string $L1 | Single L1 id as int/string
	 *	    => VAL @param vs $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setL1_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	
		
		$ctrl['mode'] = 'matrix';
				
		try {						
			$result = self::setMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::setMulti()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return $result;

	}	
	
	
	/**
	 * Creates or updates multiple L5->L1 walks which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * [MATRIX MODE] 
         * @param array $data | Array of row arrays 
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string $L1 | Single L1 id as int/string
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
	 * [TRIE MODE]
         * @param array $data | array of L5's in the form "L5_id"=>"L4s"	
	 *	=> ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"	 
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value	 
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $mode | Operation mode 'matrix' | 'trie'
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setMulti($data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'mode'=>'trie'
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);		
		
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();								

		$update_data = array();
								
				
		if($ctrl['mode'] == 'matrix'){
		    			
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
		
				$row_valid = false;			    

				try {			    
					$validator = new FOX_dataStore_validator($struct);
				
					$row_ctrl = array(				    
							    'end_node_format'=>'scalar'
					);				

					foreach( $data as $row ){   

						$row_valid = $validator->validateMatrixRow($row, $row_ctrl);

						if($row_valid !== true){
							break;				    					    
						}

						$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];

					} 
					unset($row);

				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}
				
				if($row_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));					    					    
				}				
			
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
			}
			else {
			    
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_transform_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
				foreach( $data as $row ){   
					
					$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];
				} 
				unset($row);	

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_transform_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}	
				
			}
							
		}
		elseif($ctrl['mode'] == 'trie'){		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"trie_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
				try {			    
					$validator = new FOX_dataStore_validator($struct);	
								
					$valid_ctrl = array(
						'order'=>$this->order,
						'mode'=>'data',
						'clip_order'=>0		    
					);

					$tree_valid = $validator->validateTrie($data, $valid_ctrl);
					
				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}				

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));			    
				}
				
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"trie_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}				
			}
			
			$update_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Invalid ctrl['mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		    
		}
				
		
		// Lock affected cache pages
		// ===========================================================

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
				
		try {
			$cache_pages = self::lockCachePage( array_keys($update_data) );
			$update_cache = $cache_pages;
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Error locking cache",
				'data'=>$update_data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Build db indate array and updated cache pages array
		// ===========================================================		

		$indate_data = array();	
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		
		
		foreach( $update_data as $L5 => $L4s ){

			foreach( $L4s as $L4 => $L3s ){

				foreach( $L3s as $L3 => $L2s ){

					foreach( $L2s as $L2 => $L1s ){

						foreach( $L1s as $L1 => $val){

							$indate_data[] = array(
										$this->L5_col=>$L5,
										$this->L4_col=>$L4,
										$this->L3_col=>$L3,
										$this->L2_col=>$L2,
										$this->L1_col=>$L1,
										$this->L0_col=>$val
							);								

							// Overwrite the temp class cache array with the data we set in the db query
							$update_cache[$L5]["keys"][$L4][$L3][$L2][$L1] = $val;

						}
						unset($L1, $val);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			}
			unset($L4, $L3s);
		}
		unset($L5, $L4s);
			
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Write to database
		// ===========================================================		


		// CASE 1: Transactions aren't required.
		// --------------------------------------------
		
		if( count($indate_data) == 1 ){
						
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"db_indate_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
			try {
				$rows_changed = $this->db->runIndateQuery($struct, $indate_data[0], $columns=null);
			}
			catch (FOX_exception $child) {

				// Try to unlock the cache pages we locked

				try {
					self::writeCachePage($cache_pages);
				}
				catch (FOX_exception $child_2) {

					throw new FOX_exception( array(
						'numeric'=>8,
						'text'=>"Error while writing to the database. Error unlocking cache pages.",
						'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 'indate_data'=>$indate_data),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));		    
				}									

				throw new FOX_exception( array(
					'numeric'=>9,
					'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
					'data'=>$indate_data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}			
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"db_indate_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
		}
		
		// CASE 2: Transactions are required.
		// --------------------------------------------
		
		else {			

			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"db_transaction_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			
			try {
				$this->db->beginTransaction();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>10,
					'text'=>"Couldn't initiate transaction",
					'data'=>$data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}			
			
			$rows_changed = 0;
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"db_indate_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
			foreach( $indate_data as $indate_row){
			    
			    
				try {
					$rows_changed += (int)$this->db->runIndateQuery($struct, $indate_row, $columns=null);
				}
				catch (FOX_exception $child) {


					// Try to unlock the cache pages we locked

					try {
						self::writeCachePage($cache_pages);
					}
					catch (FOX_exception $child_2) {

						throw new FOX_exception( array(
							'numeric'=>11,
							'text'=>"Error while writing to the database. Error unlocking cache pages.",
						'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 'indate_data'=>$indate_data),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>$child
						));		    
					}									

					throw new FOX_exception( array(
						'numeric'=>12,
						'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
						'data'=>$indate_row,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));

				}			    
			    
			}
			unset($indate_row);
									
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"db_indate_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
			try {
				$this->db->commitTransaction();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>13,
					'text'=>"Error commiting transaction to database",
					'data'=>$data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
			
			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@	
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"db_transaction_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			

		}
		
		
		// NOTE: we update the class cache before the persistent cache, so that if the
		// persistent cache write fails, the class cache will still in the correct
		// state. Any cache pages that fail to update if the persistent cache throws an
		// error during the write operation will remain locked, causing them to be purged 
		// on the next read operation.
		
		
		// Write updated cache page images to class cache
		// ===========================================================
		
		foreach($update_cache as $L5 => $page_image){

			$this->cache[$L5] = $page_image;
		}
		unset($L5, $page_image);
		
		
		// Write updated cache page images to persistent cache
		// ===========================================================
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_write_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		

		try {
			self::writeCachePage($update_cache);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>14,
				'text'=>"Error writing to cache",
				'data'=>$update_cache,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}			

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_write_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return (int)$rows_changed;
	
	}
	
	
	// #####################################################################################################################
	// #####################################################################################################################
	
		
	/**
	 * Drops one or more L1 branches within a single L5->L2 walk from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5 id as int/string
	 * @param int/string $L4 | Single L4 id as int/string
	 * @param int/string $L3 | Single L3 id as int/string
	 * @param int/string $L2 | Single L2 id as int/string
	 * @param int/string/array $L1s | Single L1 id as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropL1($L5, $L4, $L3, $L2, $L1s, $ctrl=null) {

		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
			
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);
		
		
		// Validate
		// ===================================================
		
		if($ctrl['validate'] != false){		   

			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"validate_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator_result = array();
			
			try {
			    
				// All of the validator calls are wrapped in a single try{} block to reduce code size. If 
				// a validator throws an exception, it will contain all info needed for debugging
			    
				$validator = new FOX_dataStore_validator($struct);	

				$validator_result['L5'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L5_col]['php'],
									'format'=>'scalar',
									'var'=>$L5
				));

				$validator_result['L4'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L4_col]['php'],
									'format'=>'scalar',
									'var'=>$L4
				));

				$validator_result['L3'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L3_col]['php'],
									'format'=>'scalar',
									'var'=>$L3
				));

				$validator_result['L2'] = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L2_col]['php'],
									'format'=>'scalar',
									'var'=>$L2
				));

				// If a single L1 is sent in, we validate it *before* spinning it into an array,
				// so we can trap strings that PHP automatically converts to ints ("17")
				
				if( !is_array($L1s) ){

					$validator_result['L1'] = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->L1_col]['php'],
										'format'=>'scalar',
										'var'=>$L1s
					));					
				}
				else {

					foreach( $L1s as $key => $val ){

						$validator_result['L1'] = $validator->validateKey( array(
											'type'=>$struct['columns'][$this->L1_col]['php'],
											'format'=>'scalar',
											'var'=>$val
						));	

						// Break the loop if we hit an invalid key
						
						if( $validator_result['L1'] !== true ){

							break;
						}

					}
					unset($key, $val);
				}
			
			}
			catch( FOX_exception $child ){
			    			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error in validator",
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
						'text'=>"Invalid " . $key . " key",
						'data'=>$val,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));			    
				}			    
			    
			}
			unset($key, $val);
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"validate_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
		} // ENDOF: if($ctrl['validate'] != false)		
					
		
		// Spin into trie format
		// ===================================================
		
		if( !is_array($L1s) ){		    
			$L1s = array($L1s);
		}
		
		$data = array();
		
		foreach($L1s as $key => $val){
		    
			$data[$L5][$L4][$L3][$L2][$val] = true;		    
		}
		unset($key, $val);
		
				
		// Drop nodes
		// ===================================================
		
		$drop_ctrl = array(
				    'mode'=>'trie',
				    'validate'=>false
		);
		
		try {
			$rows_changed = self::dropMulti($data, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling self::dropMulti()",
				'data'=>array('data'=>$data, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return $rows_changed;
		
	}
	
	
	/**
	 * Drops multiple [L5->L2 walk + L1 branch] arrays from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string/array $L1 | Single L1 id as int/string, multiple as array of int/string.
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropL1_multi($data, $ctrl=null){	

	
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);
		
		$struct = $this->_struct();
		                			  
		$validator_result = false;
		$processed = array();

		
		// Build args array
		// ==========================
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}		
		
		try {	
			if($ctrl['validate'] != false){

				$validator = new FOX_dataStore_validator($struct);
			}

			foreach( $data as $row ){

				// Each variable has to be validated individually. If we spin the variables
				// into a trie, PHP will automatically convert strings that map to ints ("17")
				// into (int) keys, which will defeat the validators
			    
				if($ctrl['validate'] != false){

					if($this->debug_on){

						extract( $this->debug_handler->event( array(
							'pid'=>$this->process_id,			    
							'text'=>"validate_start",
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'parent'=>$this,
							'vars'=>compact(array_keys(get_defined_vars()))
						)));		    
					}
		
					if( is_array($row[$this->L1_col]) ){

						$row_ctrl = array(  'required_keys'=>array(
											    $this->L5_col,
											    $this->L4_col,
											    $this->L3_col,
											    $this->L2_col,
											    $this->L1_col
								    ),
								    'allowed_keys'=>array(
											    $this->L5_col,
											    $this->L4_col,
											    $this->L3_col,
											    $this->L2_col,
											    $this->L1_col
								    ),
								    'end_node_format'=>'array',
								    'array_ctrl'=>array(
											'mode'=>'inverse'
								    )
						);											
					}
					else {
						$row_ctrl = array(
								    'required_keys'=>array(
											    $this->L5_col,
											    $this->L4_col,
											    $this->L3_col,
											    $this->L2_col,
											    $this->L1_col
								    ),
								    'allowed_keys'=>array(
											    $this->L5_col,
											    $this->L4_col,
											    $this->L3_col,
											    $this->L2_col,
											    $this->L1_col
								    ),
								    'end_node_format'=>'scalar'
						);
					}

					$validator_result = $validator->validateMatrixRow($row, $row_ctrl);


					if($validator_result !== true){ 

						break;		    
					}	
					
					if($this->debug_on){

						extract( $this->debug_handler->event( array(
							'pid'=>$this->process_id,			    
							'text'=>"validate_end",
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'parent'=>$this,
							'vars'=>compact(array_keys(get_defined_vars()))
						)));		    
					}
		
				}

				// If the value is a single key, convert it to an array so the
				// foreach() loop can operate on it

				if( !is_array($row[$this->L1_col]) ){

					$row[$this->L1_col] = array($row[$this->L1_col]);
				}

				foreach( $row[$this->L1_col] as $L1 ){

					$processed[] = array(
								$this->L5_col => $row[$this->L5_col],
								$this->L4_col => $row[$this->L4_col],
								$this->L3_col => $row[$this->L3_col],
								$this->L2_col => $row[$this->L2_col],
								$this->L1_col => $L1					    
					);
				}
				unset($L1);

			}
			unset($row);					

		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator class",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// This structure has to be outside the validator try-catch block to prevent it from   
		// catching the exceptions we throw (which would cause confusing exception chains)

		if( ($ctrl['validate'] != false) && ($validator_result !== true) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid row in data array",
				'data'=>$validator_result,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}			    
			
		
		// Drop items
		// ==========================
		
		$drop_ctrl = array(
				    'mode'=>'matrix',
				    'validate'=>false
		);
				
		try {						
			$rows_changed = self::dropMulti($processed, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::dropMulti()",
				'data'=>array('data'=>$data, 'processed'=>$processed, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		
		return $rows_changed;	      
		
		
	}
	    

	/**
	 * Drops multiple L5->L1 walks from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * [MATRIX MODE] 
         * @param array $data | Array of row arrays 
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string $L2 | Single L2 id as int/string
	 *	    => VAL @param int/string $L1 | Single L1 id as int/string
	 * 
	 * [TRIE MODE]
         * @param array $data | array of L5's in the form "L5_id"=>"L4s"	
	 *	=> ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"	 
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param NULL	 
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $mode | Operation mode 'matrix' | 'trie'
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropMulti($data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'mode'=>'trie',
			'trap_*'=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);		
					
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();				
		
		$del_data = array();
			
		if($ctrl['mode'] == 'matrix'){
		    
				
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
		
				$row_valid = false;			    

				try {			    
					$validator = new FOX_dataStore_validator($struct);
				
					$row_ctrl = array(				    
							    'end_node_format'=>'scalar'
					);				

					foreach( $data as $row ){   

						$row_valid = $validator->validateMatrixRow($row, $row_ctrl);

						if($row_valid !== true){
							break;				    					    
						}
					} 
					unset($row);

				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}
				
				if($row_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));					    					    
				}				
			
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"matrix_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
		
			}

			// Loft the individual rows into a trie, to merge overlapping entries, then clip
			// the tree to get the highest order cache LUT's affected by the delete
						
			$columns = array(
					$this->L5_col, 
					$this->L4_col, 
					$this->L3_col, 
					$this->L2_col, 
					$this->L1_col
			);
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"matrix_transform_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
			$trie = FOX_trie::loftMatrix($data, $columns, null);
			
			$del_data = FOX_trie::clipAssocTrie($trie, $columns, null);
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"matrix_transform_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}			
			
							
		}
		elseif($ctrl['mode'] == 'trie'){
		    		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   

				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"trie_validate_start",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
		
				try {			    
					$validator = new FOX_dataStore_validator($struct);
				
					$val_ctrl = array(
						'order'=>5,
						'mode'=>'control',
						'allow_wildcard'=>false		    
					);		

					$tree_valid = $validator->validateTrie($data, $val_ctrl);
				
				}
				catch( FOX_exception $child ){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Error in validator",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));			    			    
				}				

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));			    
				}
				
				if($this->debug_on){

					extract( $this->debug_handler->event( array(
						'pid'=>$this->process_id,			    
						'text'=>"trie_validate_end",
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'parent'=>$this,
						'vars'=>compact(array_keys(get_defined_vars()))
					)));		    
				}
				
			}
			
			$del_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Invalid ctrl['mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		    
		}
		
		
		// Trap "DELETE * WHERE TRUE"
		// ===========================================================		
						    
		if( $ctrl['trap_*'] == true ){

			if( !array_keys($del_data) ){

			    // @see http://en.wikipedia.org/wiki/Universal_set 

			    $error_msg =  "INTERLOCK TRIP: One or more of the conditions set in the \$data array reduces to the universal set, ";
			    $error_msg .= "which is equivalent to 'WHERE 1 = 1'. Running this command would have cleared the entire datastore. ";				
			    $error_msg .= "If this is actually your design intent, set \$ctrl['trap_*'] = false to disable this interlock."; 

			    throw new FOX_exception( array(
				    'numeric'=>7,
				    'text'=>"$error_msg",
				    'data'=>$data,
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
			    ));	

			}
		}		    
		    				
		
		// Lock affected cache pages
		// ===========================================================

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		try {
			$cache_pages = self::lockCachePage( array_keys($del_data) );
			$update_cache = $cache_pages;
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error locking cache",
				'data'=>$del_data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}			

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		
		// Build db insert array and updated cache pages array
		// ===========================================================	
		
		$dead_pages = array();		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"build_data_array_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		foreach( $del_data as $L5 => $L4s ){		
		    
			// Handle "true", "null" etc end nodes. The algorithm is implemented this
			// way to avoid excessive if-else nesting indentation. We know that any
			// non-array keys are valid end nodes because the trie passed validation
			// at the beginning of the class method
		    
			if( !is_array($L4s) ){	 
			    
				$L4s = array();	
			}			
			
			if( count($L4s) == 0 ){			// If the trie has no L4 structures, delete the
								// entire cache page from the class cache, and flag
				$dead_pages[] = $L5;		// the page to be flushed from the persistent cache			    
				unset($update_cache[$L5]);
				
				continue;				
			}
			
			foreach( $L4s as $L4 => $L3s ){
			    
				if( !is_array($L3s) ){	 

					$L3s = array();	
				}
			
				if( count($L3s) == 0 ){	    // If the L4 structure has no L3 structures, 
							    // delete its descendents' cache entries

					unset($update_cache[$L5][$this->L4_col][$L4]);
					unset($update_cache[$L5][$this->L3_col][$L4]);
					unset($update_cache[$L5][$this->L2_col][$L4]);
					unset($update_cache[$L5]["keys"][$L4]);
				}			    

				foreach( $L3s as $L3 => $L2s ){
				    
					if( !is_array($L2s) ){	 

						$L2s = array();	
					}
				
					if( count($L2s) == 0 ){	    // If the L3 structure has no L2 structures, 
								    // delete its descendents' cache entries

						unset($update_cache[$L5][$this->L3_col][$L4][$L3]);
						unset($update_cache[$L5][$this->L2_col][$L4][$L3]);
						unset($update_cache[$L5]["keys"][$L4][$L3]);
					}				    

					foreach( $L2s as $L2 => $L1s ){

						if( !is_array($L1s) ){	 

							$L1s = array();	
						}
					
						if( count($L1s) == 0 ){	    // If the L2 structure has no L1 structures, 
									    // delete its descendents' cache entries

							unset($update_cache[$L5][$this->L2_col][$L4][$L3][$L2]);
							unset($update_cache[$L5]["keys"][$L4][$L3][$L2]);
						}
					
						foreach( $L1s as $L1 => $val){

							unset($update_cache[$L5]["keys"][$L4][$L3][$L2][$L1]);
						}
						unset($L1, $val);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			}
			unset($L4, $L3s);
			
			
			// Clear empty walks from the keystore and LUT's
			// ==========================================================================		

			$update_cache[$L5]['keys']	  = FOX_sUtil::arrayPrune($update_cache[$L5]['keys'], 4);			
			$update_cache[$L5][$this->L2_col] = FOX_sUtil::arrayPrune($update_cache[$L5][$this->L2_col], 3);
			$update_cache[$L5][$this->L3_col] = FOX_sUtil::arrayPrune($update_cache[$L5][$this->L3_col], 2);	
			$update_cache[$L5][$this->L4_col] = FOX_sUtil::arrayPrune($update_cache[$L5][$this->L4_col], 1);
			
			
			if( count($update_cache[$L5]['keys']) == 0 ){	    // If a cache page is empty after being pruned, delete 
									    // the entire cache page from the class cache, and flag
				$dead_pages[] = $L5;			    // the page to be flushed from the persistent cache
				unset($update_cache[$L5]);
			}			
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"build_data_array_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
		}
		unset($L5, $L4s);
			
		
		// Clear the specified structures from the DB
		// ===========================================================
				
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_delete_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$args = array(
				'key_col'=>array(
						    $this->L5_col, 
						    $this->L4_col, 
						    $this->L3_col, 
						    $this->L2_col,
						    $this->L1_col				    
				),
				'args'=>$data
		);

		$del_ctrl = array(
				    'args_format'=>$ctrl['mode'],
				    'hash_key_vals'=>false 
		);			

		try {
			$rows_changed = $this->db->runDeleteQuery($struct, $args, $del_ctrl);
		}
		catch (FOX_exception $child) {

		    
			// Try to unlock the cache pages we locked
		    
			try {
				self::writeCachePage($cache_pages);
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>9,
					'text'=>"Error while writing to the database. Error unlocking cache pages.",
					'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 
						      'del_args'=>$args, 'del_ctrl'=>$del_ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
					'data'=>array('del_args'=>$args, 'del_ctrl'=>$del_ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
			
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_delete_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// NOTE: we *must* update the class cache before the persistent cache so that if 
		// the persistent cache write fails, the class cache will still in the correct
		// state. If we failed to do this, the class cache could end up with 'ghost' pages
		// that no longer exist in the db. If the persistent cache throws an error during  
		// the write operation, any pages that fail to update will remain locked, causing  
		// them to be purged on the next read operation. This guarantees cache coherency.
		
		
		// Write updated cache page images to class cache
		// ===========================================================
		
		foreach($update_cache as $L5 => $page_image){

			$this->cache[$L5] = $page_image;
		}
		unset($L5, $page_image);
		
				
		// Flush dead pages from the class cache
		// ===========================================================		

		foreach($dead_pages as $L5){

			unset($this->cache[$L5]);
		}
		unset($L5);
		

		// Write updated cache page images to persistent cache
		// ===========================================================

		if($update_cache){  // Trap deleting nothing but L5's
		    
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"persistent_cache_write_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
			try {
				self::writeCachePage($update_cache);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>11,
					'text'=>"Error writing to cache",
					'data'=>$update_cache,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"persistent_cache_write_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
		}

		// Flush any dead pages from the cache
		// ===========================================================
		
		if($dead_pages){
		    
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"persistent_cache_flush_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
			try {
				self::flushCachePage($dead_pages);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>12,
					'text'=>"Error flushing pages from cache",
					'data'=>$dead_pages,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}

			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"persistent_cache_flush_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
			
		}								
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
			
		return (int)$rows_changed;

	}	
	
	
	// #####################################################################################################################
	// #####################################################################################################################
	
	
	/**
	 * Drops one or more items of the specified level from ALL WALKS in the datastore.
	 * 
	 * @version 1.0
	 * @since 1.0
	 * @param int $level | Level to drop items from	 
	 * @param int/string/array $items | Single item as int/string, multiple as array of int/string.
	 * 
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropGlobal($level, $items, $ctrl=null) {
		
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$col = "L" . $level . "_col";
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	
					    
		
		// Validate
		// ===================================================
		
		if($ctrl['validate'] != false){		   

			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"validate_start",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			try {			    
			    
				$validator = new FOX_dataStore_validator($struct);					

				// If a single item is sent in, we validate it *before* spinning it into an array,
				// so we can trap strings that PHP automatically converts to ints ("17")
				
				if( !is_array($items) ){

					$is_valid = $validator->validateKey( array(
										'type'=>$struct['columns'][$this->$col]['php'],
										'format'=>'scalar',
										'var'=>$items
					));					
				}
				else {

					foreach( $items as $key => $val ){

						$is_valid = $validator->validateKey( array(
											'type'=>$struct['columns'][$this->$col]['php'],
											'format'=>'scalar',
											'var'=>$val
						));	

						// Break the loop if we hit an invalid key
						
						if( $is_valid !== true ){

							break;
						}

					}
					unset($key, $val);
				}	
			
				
			}
			catch( FOX_exception $child ){
			    			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error in validator",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));			    			    
			}
			
			// This structure has to be outside the validator try-catch block to prevent it from   
			// catching the exceptions we throw (which would cause confusing exception chains)
						    
			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid item",
					'data'=>$is_valid,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}			    			
			
			if($this->debug_on){

				extract( $this->debug_handler->event( array(
					'pid'=>$this->process_id,			    
					'text'=>"validate_end",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'parent'=>$this,
					'vars'=>compact(array_keys(get_defined_vars()))
				)));		    
			}
		
		} // ENDOF: if($ctrl['validate'] != false){

		
		// Lock the entire cache namespace
		// ===========================================================

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		try {
			self::lockNamespace();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache namespace",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_delete_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$args = array(
				array("col"=>$this->$col, "op"=>"=", "val"=>$items)
		);

		try {
			$rows_changed = $this->db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error while deleting from database",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_delete_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_flush_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persisent_cache_flush_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return (int)$rows_changed;

	}


	/**
	 * Deletes the entire module data store, and flushes the cache. Generally
	 * used for testing and debug.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropAll() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Lock the entire cache namespace
		// ===========================================================

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		try {
			self::lockNamespace();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error locking cache namespace",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_lock_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_truncate_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		$struct = $this->_struct();		

		try {
			$this->db->runTruncateTable($struct);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while clearing the database",
				'data'=>null,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"db_truncate_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_flush_start",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
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
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"persistent_cache_flush_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		if($this->debug_on){

			extract( $this->debug_handler->event( array(
				'pid'=>$this->process_id,			    
				'text'=>"method_end",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'parent'=>$this,
				'vars'=>compact(array_keys(get_defined_vars()))
			)));		    
		}
		
		return true;

	}

	

    
} // End of class FOX_dataStore_paged_L1_base


?>