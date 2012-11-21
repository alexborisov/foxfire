<?php

/**
 * FOXFIRE L5 PAGED ABSTRACT DATASTORE CLASS
 * Implements a highly efficient 5th order paged datastore
 * 
 * FEATURES
 * --------------------------------------
 *  -> Transient cache support
 *  -> Persistent cache support
 *  -> Progressive cache loading
 *  -> SQL transaction support
 *  -> Fully atomic operations
 *  -> Advanced error handling
 *  -> Multi-thread safe
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

abstract class FOX_dataStore_paged_L5_base extends FOX_db_base {

    
    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache 
					    // locking. Loaded by descendent class.
	
	var $cache;			    // Main cache array for this class
	
	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache 
					    // operations. Loaded by descendent class.	
	
	var $wildcard = '*';		    // String to use a the "wildcard" character when using trie structures as
					    // selectors. Since "*" is an illegal name for an SQL column, it will never
					    // create conflicts. @see http://en.wikipedia.org/wiki/Wildcard_character
	

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
	    
	    
		$args_default = array(
			'hash_columns'=>array()
		);

		$args = wp_parse_args($args, $args_default);
			    
                $struct = $this->_struct();		
		$columns = array_keys($struct['columns']);
		
		$this->L5_col = $columns[0];		
		$this->L4_col = $columns[1];
		$this->L3_col = $columns[2];
		$this->L2_col = $columns[3];
		$this->L1_col = $columns[4];	
		$this->L0_col = $columns[5];		
		
		if( count($args['hash_columns']) > 0 ){
		    
			$this->hashtable = new FOX_hashTable();
			$this->hash_columns = $args['hash_columns'];
			$this->hashing_active = true;
		}
		else {
			$this->hashing_active = false;
		}
						
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'r_mode'=>'trie'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		
		if( !is_array($L1s) ){
		    
			$single = true;
			$L1s = array($L1s);
		}
		else{
			$single = false;
		}
				 
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'both',
								'var'=>$L4
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid L4 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'both',
								'var'=>$L3
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Invalid L3 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L2_col]['php'],
								'format'=>'both',
								'var'=>$L2
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Invalid L2 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			foreach( $L1s as $L1 ){

				$is_valid = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L1_col]['php'],
									'format'=>'both',
									'var'=>$L1
				));	

				if($is_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Invalid L1 key",
						'data'=>array('key'=>$L1, 'error'=>$is_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}			
			}
			unset($L1);			
			
		}		

		// Fetch items
		// ==========================
		
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
		
		return $result;

	}

	
	/**
	 * Fetches one or more L2 objects from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2s | Single L2 as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $r_mode | Response format - 'matrix' | 'trie'
	 * 
	 * @param bool &$valid | True if all requested objects exist, false if not.
	 * @return mixed | Exception on failure. Data object on success.
	 */

	public function getL2($L5, $L4, $L3, $L2s, $ctrl=null, &$valid=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'r_mode'=>'trie'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		if( !is_array($L2s) ){
		    
			$single = true;
			$L2s = array($L2s);
		}
		else{
			$single = false;
		}
		
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'both',
								'var'=>$L4
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid L4 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'both',
								'var'=>$L3
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Invalid L3 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			
			foreach( $L2s as $L2 ){

				$is_valid = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L2_col]['php'],
									'format'=>'both',
									'var'=>$L2
				));	

				if($is_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Invalid L2 key",
						'data'=>array('key'=>$L2, 'error'=>$is_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}			
			}
			unset($L2);			
			
		}
		
		// Fetch items
		// ==========================

		$get_data = array( $L5=>array( $L4=>array( $L3=>array() )));

		foreach( $L2s as $L2 ){
		    
			$get_data[$L5][$L4][$L3][$L2] = true;			
		}
		unset($L2);
		
				
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
				'numeric'=>5,
				'text'=>"Error calling self::getMulti()",
				'data'=>array('get_data'=>$get_data, 'get_ctrl'=>$get_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		if($single){
		    
			// If using the 'trie' response format with a single L2 end  
			// nodes, 'lift' the L2 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$L2 = array_pop($L2s);			    
				$result = $result[$L5][$L4][$L3][$L2];
			}
		}
		else {
		    
			// If using the 'trie' response format with multiple L2 end  
			// nodes, 'lift' the parent L3 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$result = $result[$L5][$L4][$L3];
			}		    
		    
		}
		
		return $result;

	}
	
	
	/**
	 * Fetches one or more L3 objects from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3s | Single L3 as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $r_mode | Response format - 'matrix' | 'trie'
	 * 
	 * @param bool &$valid | True if all requested objects exist, false if not.
	 * @return mixed | Exception on failure. Data object on success.
	 */

	public function getL3($L5, $L4, $L3s, $ctrl=null, &$valid=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'r_mode'=>'trie'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		if( !is_array($L3s) ){
		    
			$single = true;
			$L3s = array($L3s);
		}
		else{
			$single = false;
		}
		
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'both',
								'var'=>$L4
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid L4 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
									
			foreach( $L3s as $L3 ){

				$is_valid = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L3_col]['php'],
									'format'=>'both',
									'var'=>$L3
				));	

				if($is_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid L3 key",
						'data'=>array('key'=>$L3, 'error'=>$is_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}			
			}
			unset($L3);			
			
		}
		
		// Fetch items
		// ==========================

		$get_data = array( $L5=>array( $L4=>array() ));

		foreach( $L3s as $L3 ){
		    
			$get_data[$L5][$L4][$L3] = true;			
		}
		unset($L3);		
		
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
				'numeric'=>4,
				'text'=>"Error calling self::getMulti()",
				'data'=>array('get_data'=>$get_data, 'get_ctrl'=>$get_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		if($single){
		    
			// If using the 'trie' response format with a single L3 end  
			// nodes, 'lift' the L3 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$L3 = array_pop($L3s);			    
				$result = $result[$L5][$L4][$L3];
			}
		}
		else {
		    
			// If using the 'trie' response format with multiple L3 end  
			// nodes, 'lift' the parent L4 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$result = $result[$L5][$L4];
			}		    
		    
		}
		
		return $result;

	}
	
	
	/**
	 * Fetches one or more L4 objects from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4s | Single L4 as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $r_mode | Response format - 'matrix' | 'trie'
	 * 
	 * @param bool &$valid | True if all requested objects exist, false if not.
	 * @return mixed | Exception on failure. Data object on success.
	 */

	public function getL4($L5, $L4s, $ctrl=null, &$valid=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'r_mode'=>'trie'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		if( !is_array($L4s) ){
		    
			$single = true;
			$L4s = array($L4s);
		}
		else{
			$single = false;
		}
		
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}				
									
			foreach( $L4s as $L4 ){

				$is_valid = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L4_col]['php'],
									'format'=>'both',
									'var'=>$L4
				));	

				if($is_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid L4 key",
						'data'=>array('key'=>$L4, 'error'=>$is_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}			
			}
			unset($L4);			
			
		}
		
		
		// Fetch items
		// ==========================		

		$get_data = array( $L5=>array() );

		foreach( $L4s as $L4 ){
		    
			$get_data[$L5][$L4] = true;			
		}
		unset($L4);		
		
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
				'numeric'=>3,
				'text'=>"Error calling self::getMulti()",
				'data'=>array('get_data'=>$get_data, 'get_ctrl'=>$get_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		if($single){
		    
			// If using the 'trie' response format with a single L4 end  
			// nodes, 'lift' the L4 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$L4 = array_pop($L4s);			    
				$result = $result[$L5][$L4];
			}
		}
		else {
		    
			// If using the 'trie' response format with multiple L4 end  
			// nodes, 'lift' the parent L5 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$result = $result[$L5];
			}		    
		    
		}
		
		return $result;

	}
	
	
	/**
	 * Fetches one or more L5 objects from the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5s | Single L5 as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 *	=> VAL @param string $r_mode | Response format - 'matrix' | 'trie'
	 * 
	 * @param bool &$valid | True if all requested objects exist, false if not.
	 * @return mixed | Exception on failure. Data object on success.
	 */

	public function getL5($L5s, $ctrl=null, &$valid=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'r_mode'=>'trie'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		if( !is_array($L5s) ){
		    
			$single = true;
			$L5s = array($L5s);
		}
		else{
			$single = false;
		}
		
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);								
									
			foreach( $L5s as $L5 ){

				$is_valid = $validator->validateKey( array(
									'type'=>$struct['columns'][$this->L5_col]['php'],
									'format'=>'both',
									'var'=>$L5
				));	

				if($is_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Invalid L5 key",
						'data'=>array('key'=>$L5, 'error'=>$is_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}			
			}
			unset($L5);			
			
		}
		
		
		// Fetch items
		// ==========================		

		$get_data = array();

		foreach( $L5s as $L5 ){
		    
			$get_data[$L5] = true;			
		}
		unset($L5);		
		
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
				'numeric'=>2,
				'text'=>"Error calling self::getMulti()",
				'data'=>array('get_data'=>$get_data, 'get_ctrl'=>$get_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		if($single){
		    
			// If using the 'trie' response format with a single L5 end  
			// nodes, 'lift' the L5 object out of the results array
		    
			if( $get_ctrl['r_mode'] == 'trie' ){
			    
				$L5 = array_pop($L5s);			    
				$result = $result[$L5];
			}
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);		
					
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();				
		
		$get_data = array();
			
		if($ctrl['q_mode'] == 'matrix'){
		    
				
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)
		    			    

				$validator = new FOX_dataStore_validator($struct);

				foreach( $data as $row ){   
			
					try {
						$row_valid = $validator->isRowSequential($row);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Error in FOX_dataStore_validator::isRowSequential()",
							'data'=>array('row'=>$row),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}				    					
					
					if( $row_valid !== true ){
					    
						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Invalid row in data array",
							'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));					    					    
					}																
					
				}																
				unset($row);			    
			
			}

			// Loft the individual rows into a trie (to merge overlapping objects) then clip
			// the tree to get the highest order objects we need to fetch
			// =============================================================================			
						
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
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
						
		}
		elseif($ctrl['q_mode'] == 'trie'){
		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   

				$validator = new FOX_dataStore_validator($struct);			
				$tree_valid = $validator->validateL5Trie($data);

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}				    
			}
			
			$get_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Invalid ctrl['q_mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
				    'numeric'=>6,
				    'text'=>"$error_msg",
				    'data'=>$data,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
			    ));	

			}
		}		    		    				
		
		// Find all requested objects that don't have authority in the class cache (L5 to L2),
		// or which don't exist in the class cache (L1) and try to load them from the persistent cache
		// ==============================================================================================			
				
		try {
			$cache_fetch = self::notInClassCache($get_data, $this->cache);				 
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Error in self::notInClassCache()",
				'data'=>array('get_data'=>$get_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    			
		}
		
		if($cache_fetch){
		    
			try {
				$cache_pages = self::readCachePage( array_keys($cache_fetch) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>8,
					'text'=>"Error reading from persistent cache",
					'data'=>array('cache_fetch'=>array_keys($cache_fetch)),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    			
			}		


			foreach( $cache_pages as $page_id => $page_image ){

				if($page_id == 99){
				
				    echo "HIT-1"; die;
				}
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

			$db_fetch = self::notInClassCache($cache_fetch, $this->cache);			

			if($db_fetch){

				$db = new FOX_db(); 

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
						    'hash_key_vals'=>$this->hashing_active 
				);			

				try {
					$db_result = $db->runSelectQuery($struct, $args, $columns, $db_ctrl);				
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>9,
						'text'=>"Error while reading from database",
						'data'=>array('args'=>$args, 'columns'=>$columns, 'db_ctrl'=>$db_ctrl),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    			
				}

				if($db_result){

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
		
		
		if($ctrl['r_mode'] == 'matrix'){
		    
		    
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
					'numeric'=>10,
					'text'=>"Error converting result to 'matrix' format",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}					
		    
		}
		elseif($ctrl['r_mode'] != 'trie'){
		    
			throw new FOX_exception( array(
				'numeric'=>11,
				'text'=>"Invalid ctrl['r_mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}
		
		
		// Write updated page images to persistent cache
		// ===========================================================

		if($update_cache){  // Trap no changed pages
		    
			try {
				self::writeCachePage($update_cache);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>12,
					'text'=>"Error writing to persistent cache",
					'data'=>array('update_cache'=>$update_cache, 'result'=>$result),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		}
		
		
		// Overwrite the class cache with the new cache image
		// ===========================================================
		
		$this->cache = $cache_image;
		
		
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		$ctrl['mode'] = 'matrix';
		
		
		try {						
			$result = self::addMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::addMulti()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}	
	
	
	/**
	 * Adds a single L2 trie structure that DOES NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2 | Single L2
	 * @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	=> KEY @param int/string | L1 id
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function addL2($L5, $L4, $L3, $L2, $L1s, $ctrl=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3, 
				$this->L2_col=>$L2, 
				$this->L1_col=>$L1s
		));		
		
		try {
			$result = self::addL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::addL2_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Adds multiple L2 trie structures that DO NOT ALREADY EXIST in the store
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
	 *	    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		=> KEY @param int/string | L1 id
	 *		    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addL2_multi($data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
				
		
		// Validate data array
		// ===========================================================
		
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
			
			foreach( $data as $row ){

				$row_valid = $validator->validateL2Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5->L2 walk.
		
		$set_data = array();
		
		foreach( $data as $row ){

		    
			if( !is_array($row[$this->L1_col]) ){

				$row[$this->L1_col] = array($row[$this->L1_col]);
			}
			
			foreach( $row[$this->L1_col] as $L1 => $L1_val ){

				$set_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$L1] = $L1_val;
			}
			unset($L1, $L1_val);
			
		}
		unset($row);		
		
		
		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::addMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::addMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Adds a single L3 trie structure that DOES NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	    => KEY @param int/string | L1 id
	 *		=> VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function addL3($L5, $L4, $L3, $L2s, $ctrl=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3, 
				$this->L2_col=>$L2s 
		));		
		
		try {
			$result = self::addL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::addL3_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Adds multiple L3 trie structures which DO NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		    => KEY @param int/string | L1 id
	 *			=> VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 * 	 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addL3_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
		
					
		// Validate data array
		// ===========================================================		
		
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL3Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5->L3 walk.
		
		$set_data = array();
		
		foreach( $data as $row ){
			
			foreach( $row[$this->L2_col] as $L2 => $L1s ){

				foreach( $L1s as $L1 => $L1_val ){

					$set_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$L2][$L1] = $L1_val;
				}
				unset($L1, $L1_val);
			}
			unset($L2, $L1s);			
		}
		unset($row);
		

		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::addMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::addMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		return $result;

	}	
	
	
	/**
	 * Adds a single L4 trie structure that DOES NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		=> KEY @param int/string | L1 id
	 *		    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function addL4($L5, $L4, $L3s, $ctrl=null){
	    
		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array( 
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3s 
		));		
		
		try {
			$result = self::addL4_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::addL4_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Adds multiple L4 trie structures which DO NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 * 	 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addL4_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
						
						
		// Validate data array
		// ===========================================================
		
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL4Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}		
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5 key.
		
		$set_data = array();
		
		foreach( $data as $row ){

			foreach( $row[$this->L3_col] as $L3 => $L2s ){

				foreach( $L2s as $L2 => $L1s ){

					foreach( $L1s as $L1 => $L1_val ){

						$set_data[$row[$this->L5_col]][$row[$this->L4_col]][$L3][$L2][$L1] = $L1_val;
					}
					unset($L1, $L1_val);
				}
				unset($L2, $L1s);
			}
			unset($L3, $L2s);				
		}
		unset($row);
		
		
		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::addMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::addMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}		


	/**
	 * Adds a single L5 trie structure that DOES NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param array $L4s | array of L4's in the form "L4_id"=>"L3s"
	 *	=> ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		    => KEY @param int/string | L1 id
	 *			=> VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function addL5($L5, $L4s, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4s
		));
		
		try {
			$result = self::addL5_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::addL5_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Adds multiple L5 trie structures which DO NOT ALREADY EXIST in the store
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"
	 *		=> ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *			=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			    => KEY @param int/string | L1 id
	 *				=> VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 * 	 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addL5_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
						
						
		// Validate data array
		// ===========================================================
		                					
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL5Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}	
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5 key.
		
		$set_data = array();
		
		foreach( $data as $row ){

			foreach( $row[$this->L4_col] as $L4 => $L3s ){
			    
				foreach( $L3s as $L3 => $L2s ){

					foreach( $L2s as $L2 => $L1s ){

						foreach( $L1s as $L1 => $L1_val ){

							$set_data[$row[$this->L5_col]][$L4][$L3][$L2][$L1] = $L1_val;
						}
						unset($L1, $L1_val);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			
			}
			unset($L4, $L3s);
		}
		unset($row);
		
		
		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::addMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::addMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'mode'=>'trie'
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);		
			
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();		
		
		$update_data = array();
			
		if($ctrl['mode'] == 'matrix'){
		    
				
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)
		    
			    
				$validator = new FOX_dataStore_validator($struct);
				
				foreach( $data as $id => $row ){   
			
					$row_valid = $validator->validateL1Row($row);
					
					if( $row_valid !== true ){
					    
						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Invalid row in data array",
							'data'=>array('faulting_row_id'=>$id,
								      'row_array'=>$row,
								      'error'=>$row_valid),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));					    					    
					}
												
					$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];

				} 
				unset($id, $row);			    
			
			}
			else {
			    
				foreach( $data as $row ){   
					
					$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];
				} 
				unset($row);			    			    
			}
							
		}
		elseif($ctrl['mode'] == 'trie'){
		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   

				$validator = new FOX_dataStore_validator($struct);			
				$tree_valid = $validator->validateL5Trie($data);

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}				    
			}
			
			$update_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Invalid ctrl['mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			    
		    
		}
				
		
		// Lock affected cache pages
		// ===========================================================

		try {
			$cache_pages = self::lockCachePage( array_keys($update_data) );
			$update_cache = $cache_pages;
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error locking cache",
				'data'=>$update_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}			
		
		
		// Build db insert array and updated cache pages array
		// ===========================================================		

		$insert_data = array();

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
			
		
		// Write to database
		// ===========================================================
		
		$db = new FOX_db(); 		

		try {
			$rows_changed = $db->runInsertQueryMulti($struct, $insert_data, $columns=null);
		}
		catch (FOX_exception $child) {

		    
			// Try to unlock the cache pages we locked
		    
			try {
				self::writeCachePage($cache_pages);
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Error while writing to the database. Error unlocking cache pages.",
					'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 'insert_data'=>$insert_data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
			
		}
				
							
		// Write updated cache page images to persistent cache
		// ===========================================================

		try {
			self::writeCachePage($update_cache);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error writing to cache",
				'data'=>$update_cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}			

		
		// Write updated cache page images to class cache
		// ===========================================================
		
		foreach($update_cache as $L5 => $page_image){

			$this->cache[$L5] = $page_image;
		}
		unset($L5, $page_image);		



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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		$ctrl['mode'] = 'matrix';
		
		
		try {						
			$result = self::setMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::setMulti()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}	
	
	
	/**
	 * Adds or updates a single L2 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2 | Single L2
	 * @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	=> KEY @param int/string | L1 id
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setL2($L5, $L4, $L3, $L2, $L1s, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3, 
				$this->L2_col=>$L2, 
				$this->L1_col=>$L1s
		));
		
		try {
			$result = self::setL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::setL2_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Creates or updates multiple L2 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore
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
	 *	    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		=> KEY @param int/string | L1 id
	 *		    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setL2_multi($data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
				
		
		// Validate data array
		// ===========================================================
		
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL2Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5->L2 walk.
		
		$set_data = array();
		
		foreach( $data as $row ){

			if( !is_array($row[$this->L1_col]) ){

				$row[$this->L1_col] = array($row[$this->L1_col]);
			}
			
			foreach( $row[$this->L1_col] as $L1 => $L1_val ){

				$set_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$L1] = $L1_val;
			}
			unset($L1, $L1_val);
		}
		unset($row);		
		
		
		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::setMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::setMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Adds or updates a single L3 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	    => KEY @param int/string | L1 id
	 *		=> VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setL3($L5, $L4, $L3, $L2s, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3, 
				$this->L2_col=>$L2s 
		));
		
		try {
			$result = self::setL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::setL3_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Creates or updates multiple L3 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		    => KEY @param int/string | L1 id
	 *			=> VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 * 	 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setL3_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
		
					
		// Validate data array
		// ===========================================================		
		
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL3Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5->L3 walk.
		
		$set_data = array();
		
		foreach( $data as $row ){
			
			foreach( $row[$this->L2_col] as $L2 => $L1s ){

				foreach( $L1s as $L1 => $L1_val ){

					$set_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$L2][$L1] = $L1_val;
				}
				unset($L1, $L1_val);
			}
			unset($L2, $L1s);			
		}
		unset($row);
		

		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::setMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::setMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		return $result;

	}	
	
	
	/**
	 * Adds or updates a single L4 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		=> KEY @param int/string | L1 id
	 *		    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setL4($L5, $L4, $L3s, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4, 
				$this->L3_col=>$L3s 
		));	
		
		try {
			$result = self::setL4_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::setL4_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Creates or updates multiple L4 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			=> KEY @param int/string | L1 id
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 * 	 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setL4_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
						
						
		// Validate data array
		// ===========================================================
		
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL4Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}		
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5 key.
		
		$set_data = array();
		
		foreach( $data as $row ){

			foreach( $row[$this->L3_col] as $L3 => $L2s ){

				foreach( $L2s as $L2 => $L1s ){

					foreach( $L1s as $L1 => $L1_val ){

						$set_data[$row[$this->L5_col]][$row[$this->L4_col]][$L3][$L2][$L1] = $L1_val;
					}
					unset($L1, $L1_val);
				}
				unset($L2, $L1s);
			}
			unset($L3, $L2s);				
		}
		unset($row);
		
		
		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::setMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::setMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}		


	/**
	 * Adds or updates a single L5 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param array $L4s | array of L4's in the form "L4_id"=>"L3s"
	 *	=> ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		    => KEY @param int/string | L1 id
	 *			=> VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key		 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setL5($L5, $L4s, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$data = array( array(
				$this->L5_col=>$L5, 
				$this->L4_col=>$L4s
		));
		
		try {
			$result = self::setL5_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::setL5_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;

	}
	
	
	/**
	 * Creates or updates multiple L5 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => ARR @param array $L4s | array of L4's in the form "L4_id"=>"L3s"
	 *		=> ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *		    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *			=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *			    => KEY @param int/string | L1 id
	 *				=> VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys
	 * 	 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setL5_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
						
						
		// Validate data array
		// ===========================================================
		                					
		if($ctrl['validate'] == true){
	    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);
				
			foreach( $data as $row ){

				$row_valid = $validator->validateL5Row($row);

				if( $row_valid !== true ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Invalid row in data array",
						'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));					    					    
				}			    
			}	    		    		    
		}	
		
		
		// Reduce the $data array into a trie
		// ===========================================================
		
		// NOTE: we have to fully traverse every trie array to handle the situation
		// where two rows in the data array contain the same L5 key.
		
		$set_data = array();
		
		foreach( $data as $row ){

			foreach( $row[$this->L4_col] as $L4 => $L3s ){
			    
				foreach( $L3s as $L3 => $L2s ){

					foreach( $L2s as $L2 => $L1s ){

						foreach( $L1s as $L1 => $L1_val ){

							$set_data[$row[$this->L5_col]][$L4][$L3][$L2][$L1] = $L1_val;
						}
						unset($L1, $L1_val);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			
			}
			unset($L4, $L3s);
		}
		unset($row);
		
		
		// Add to db
		// ===========================================================
		
		$set_ctrl = array(
			'validate'=>false,
			'mode'=>'trie'
		);
		
		try {						
			$result = self::setMulti($set_data, $set_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::setMulti()",
				'data'=>array('set_data'=>$set_data, 'set_ctrl'=>$set_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'mode'=>'trie'
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);		
		
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();								

		$update_data = array();
								
				
		if($ctrl['mode'] == 'matrix'){
		    
				
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)
		    
			    
				$validator = new FOX_dataStore_validator($struct);
				
				foreach( $data as $row ){   
			
					$row_valid = $validator->validateL1Row($row);
					
					if( $row_valid !== true ){
					    
						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Invalid row in data array",
							'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));					    					    
					}
												
					$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];

				} 
				unset($row);			    
			
			}
			else {
			    
				foreach( $data as $row ){   
					
					$update_data[$row[$this->L5_col]][$row[$this->L4_col]][$row[$this->L3_col]][$row[$this->L2_col]][$row[$this->L1_col]] = $row[$this->L0_col];
				} 
				unset($row);			    			    
			}
							
		}
		elseif($ctrl['mode'] == 'trie'){
		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   

				$validator = new FOX_dataStore_validator($struct);			
				$tree_valid = $validator->validateL5Trie($data);

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}				    
			}
			
			$update_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Invalid ctrl['mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			    
		    
		}
				
		
		// Lock affected cache pages
		// ===========================================================

		try {
			$cache_pages = self::lockCachePage( array_keys($update_data) );
			$update_cache = $cache_pages;
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error locking cache",
				'data'=>$update_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// Build db indate array and updated cache pages array
		// ===========================================================		

		$indate_data = array();		
		
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
			
			
		// Write to database
		// ===========================================================
		
		$db = new FOX_db(); 		


		// CASE 1: Transactions aren't required.
		// --------------------------------------------
		
		if( count($indate_data) == 1 ){
						
			
			try {
				$rows_changed = $db->runIndateQuery($struct, $indate_data[0], $columns=null);
			}
			catch (FOX_exception $child) {

				// Try to unlock the cache pages we locked

				try {
					self::writeCachePage($cache_pages);
				}
				catch (FOX_exception $child_2) {

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Error while writing to the database. Error unlocking cache pages.",
						'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 'indate_data'=>$indate_data),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}									

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
					'data'=>$indate_data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			
			

		}
		
		// CASE 2: Transactions are required.
		// --------------------------------------------
		
		else {			

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
			
			try {
				$db->beginTransaction();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>8,
					'text'=>"Couldn't initiate transaction",
					'data'=>$data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			
			
			$rows_changed = 0;
			

			foreach( $indate_data as $indate_row){
			    
			    
				try {
					$rows_changed += (int)$db->runIndateQuery($struct, $indate_row, $columns=null);
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
						'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 'indate_data'=>$indate_data),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}									

					throw new FOX_exception( array(
						'numeric'=>10,
						'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
						'data'=>$indate_row,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));

				}			    
			    
			}
			unset($indate_row);
									

			try {
				$db->commitTransaction();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>11,
					'text'=>"Error commiting transaction to database",
					'data'=>$data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@			

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

		try {
			self::writeCachePage($update_cache);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>12,
				'text'=>"Error writing to cache",
				'data'=>$update_cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}			

		
		return (int)$rows_changed;
	
	}
	
	
	/**
	 * Replaces a SINGLE L2 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L2->L1 walks for the L5->L2 intersect, then adding the new L2->L1 walks 
	 * contained in the $data structure. 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * @param int/string $L2 | Single L2 
	 * 	 
         * @param array $data | array of L1's in the form "L1_id"=>"L1_value"	
	 *	 => KEY @param int/string | L1 id
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL2($L5, $L4, $L3, $L2, $data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
				
				 
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'both',
								'var'=>$L4
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid L4 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'both',
								'var'=>$L3
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Invalid L3 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L2_col]['php'],
								'format'=>'both',
								'var'=>$L2
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Invalid L2 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			// VALIDATE L1 STRUCTURES
			
		}		

		// Replace items
		// ==========================
		
		$replace_data = array( $L5=>array( $L4=>array( $L3=>array( $L2=>$data ))));
				
		
		$replace_ctrl = array(
				    'validate'=>false
		);
				
		try {
			$rows_changed = self::replaceL2_multi($data, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error calling self::replaceL2_multi",
				'data'=>array('replace_data'=>$replace_data, 'replace_ctrl'=>$replace_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		return $rows_changed;
		
	}
		
	
	/**
	 * Replaces multiple L2 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L2->L1 walks for each L5->L2 intersect structure passed in the $data array, 
	 * then adding the new L2->L1 walks contained in the intersect structure. 
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
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL2_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}				
		
                $struct = $this->_struct();
		
		
		// Validate data array
		// ===========================================================

		if($ctrl['validate'] == true){
		    
		    
			$validator = new FOX_dataStore_validator($struct);
			
			$tree_valid = $validator->validateL5Trie($data);
			
			if($tree_valid !== true){
			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid key in data array",
					'data'=>$tree_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
					
		}
						

		// Lock all L5 cache pages in the $data array
		// ===========================================================
		
		$L5_ids = array_keys($data);
		
		try {
			$page_images = self::lockCachePage($L5_ids);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache pages",
				'data'=>array("pages"=>$L5_ids),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// 1) Build $insert_data array
		// 2) Calculate $del_args
		// 3) Rebuild cache page images
		// ================================================================

		$insert_data = array();	
		$del_args = array();		
		$page_images = $this->cache;

		foreach( $data as $L5 => $L4s ){				
			
			// Avoid creating redundant cache entries
		    
			if( FOX_sUtil::keyExists('all_cached', $page_images[$L5]) ){
			
				$parent_has_auth = true;			    
			}
			else {			    
				$parent_has_auth = false;			    
			}
			
			foreach( $L4s as $L4 => $L3s ){			

				if( !$parent_has_auth // performance optimization
				    && FOX_sUtil::keyExists($L4, $page_images[$L5][$this->L4_col]) ){

					$parent_has_auth = true;			    
				}
			
				foreach( $L3s as $L3 => $L2s ){										

					if( !$parent_has_auth // performance optimization
					    && FOX_sUtil::keyExists($L3, $page_images[$L5][$this->L3_col][$L4]) ){

						$parent_has_auth = true;			    
					}
					
					foreach( $L2s as $L2 => $L1s ){
					    
						// Clear all objects currently inside the L2
						unset($page_images[$L5]["keys"][$L4][$L3][$L2]);
					    
						$del_args[] = array(
								    $this->L5_col=>$L5, 
								    $this->L4_col=>$L4, 
								    $this->L3_col=>$L3, 
								    $this->L2_col=>$L2
						);					    

						if(!$parent_has_auth){	
						    
							$page_images[$L5][$this->L2_col][$L4][$L3][$L2] = true;
						}
												
						foreach( $L1s as $L1 => $val){

							$page_images[$L5]["keys"][$L4][$L3][$L2][$L1] = $val;

							$insert_data[] = array(
										$this->L5_col=>$L5,
										$this->L4_col=>$L4,
										$this->L3_col=>$L3,
										$this->L2_col=>$L2,
										$this->L1_col=>$L1,
										$this->L0_col=>$val
							);
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
		
		
		// Update the database
		// ===========================================================
		
		$db = new FOX_db();
			
		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		
		try {
			$db->beginTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Couldn't initiate transaction",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Clear all L5->L4 intersects from the db
		// ===========================================================

		$args = array(
				'key_col'=>array(
						    $this->L5_col, 
						    $this->L4_col, 
						    $this->L3_col, 
						    $this->L2_col
				),
				'args'=>$del_args
		);
		
		$del_ctrl = array('args_format'=>'matrix');
		
		try {
			$db->runDeleteQuery($struct, $args, $del_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error while deleting from the database. Error rolling back.",
					'data'=>array('rollback_exception'=>$child_2, 'args'=>$args, 'del_ctrl'=>$del_ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error while deleting from the database. Successful rollback.",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		


		// Insert updated walks
		// ===========================================================

		$insert_cols = null;
		$insert_ctrl = null;
		
		try {
			$rows_set = $db->runInsertQueryMulti($struct, $insert_data, $insert_cols, $insert_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Error rolling back.",
					'data'=>array('insert_data'=>$insert_data, 'rollback_exception'=>$child_2),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error while writing to the database. Successful rollback.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}				

		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		try {
			$db->commitTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error commiting transaction to database",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
				
		
		// Overwrite the locked L5 cache pages, releasing our lock
		// ===========================================================
		
		try {
			self::writeCachePage($page_images);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Cache set error",
				'data'=>$page_images,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// Write the temp class cache to the class cache
		// ===========================================================		
		
		$this->cache = $page_images;
		

		return (int)$rows_set;
		
	}
	
	/**
	 * Replaces a SINGLE L3 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L3->L1 walks for the L5->L3 intersect, then adding the new L3->L1 walks 
	 * contained in the $data structure. 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * @param int/string $L3 | Single L3
	 * 	 
         * @param array $data | array of L2's in the form "L2_id"=>"L1s"	
	 *	=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	    => KEY @param int/string | L1 id
	 *		=> VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL3($L5, $L4, $L3, $data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
				
				 
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'both',
								'var'=>$L4
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid L4 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L3_col]['php'],
								'format'=>'both',
								'var'=>$L3
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Invalid L3 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
			
			// VALIDATE L2 STRUCTURES
			
		}		

		// Replace items
		// ==========================
		
		$replace_data = array( $L5=>array( $L4=>array( $L3=>$data )));
				
		
		$replace_ctrl = array(
				    'validate'=>false
		);
				
		try {
			$rows_changed = self::replaceL3_multi($data, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error calling self::replaceL3_multi",
				'data'=>array('replace_data'=>$replace_data, 'replace_ctrl'=>$replace_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		return $rows_changed;
		
	}
	
	
	/**
	 * Replaces multiple L3 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore,  
	 * deleting all L3->L1 walks for each L5->L3 intersect structure passed in the $data array,  
	 * then adding the new L3->L1 walks contained in the intersect structure. 
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
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL3_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}				
		
                $struct = $this->_struct();		
		

		// Validate data array
		// ===========================================================

		if($ctrl['validate'] == true){
		    
		    
			$validator = new FOX_dataStore_validator($struct);
			
			$tree_valid = $validator->validateL5Trie($data);
			
			if($tree_valid !== true){
			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid key in data array",
					'data'=>$tree_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
					
		}
						

		// Lock all L5 cache pages in the $data array
		// ===========================================================
		
		$L5_ids = array_keys($data);
		
		try {
			$page_images = self::lockCachePage($L5_ids);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache pages",
				'data'=>array("pages"=>$L5_ids),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// 1) Build $insert_data array
		// 2) Calculate $del_args
		// 3) Rebuild cache page images
		// ================================================================

		$insert_data = array();	
		$del_args = array();		
		$page_images = array();

		foreach( $data as $L5 => $L4s ){	
			
			foreach( $L4s as $L4 => $L3s ){			

				foreach( $L3s as $L3 => $L2s ){
				    
					$del_args[] = array(
							    $this->L5_col=>$L5, 
							    $this->L4_col=>$L4, 
							    $this->L3_col=>$L3
					);
						
					$page_images[$L5][$this->L3_col][$L4][$L3] = true;										
					
					foreach( $L2s as $L2 => $L1s ){

						$page_images[$L5][$this->L2_col][$L4][$L3][$L2] = true;

						foreach( $L1s as $L1 => $val){

							$page_images[$L5]["keys"][$L4][$L3][$L2][$L1] = $val;

							$insert_data[] = array(
										$this->L5_col=>$L5,
										$this->L4_col=>$L4,
										$this->L3_col=>$L3,
										$this->L2_col=>$L2,
										$this->L1_col=>$L1,
										$this->L0_col=>$val
							);
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
		
		
		// Update the database
		// ===========================================================
		
		$db = new FOX_db();
			
		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		
		try {
			$db->beginTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Couldn't initiate transaction",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Clear all L5->L4 intersects from the db
		// ===========================================================

		$args = array(
				'key_col'=>array(
						    $this->L5_col, 
						    $this->L4_col, 
						    $this->L3_col
				),
				'args'=>$del_args
		);
		
		$del_ctrl = array('args_format'=>'matrix');
		
		try {
			$db->runDeleteQuery($struct, $args, $del_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error while deleting from the database. Error rolling back.",
					'data'=>array('rollback_exception'=>$child_2, 'args'=>$args, 'del_ctrl'=>$del_ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error while deleting from the database. Successful rollback.",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		


		// Insert updated walks
		// ===========================================================

		$insert_cols = null;
		$insert_ctrl = null;
		
		try {
			$rows_set = $db->runInsertQueryMulti($struct, $insert_data, $insert_cols, $insert_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Error rolling back.",
					'data'=>array('insert_data'=>$insert_data, 'rollback_exception'=>$child_2),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error while writing to the database. Successful rollback.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}				

		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		try {
			$db->commitTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error commiting transaction to database",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		// NOTE: we update the class cache before the persistent cache, so that if the
		// persistent cache write fails, the class cache will still in the correct
		// state. Any cache pages that fail to update if the persistent cache throws an
		// error during the write operation will remain locked, causing them to be purged 
		// on the next read operation.
		
		
		// Write the temp class cache to the class cache
		// ===========================================================
		
		foreach( $page_images as $L5 => $image ){
		    
			$this->cache[$L5] = $image;		    		    
		}
		unset($L5, $image);
		

		// Overwrite the locked L5 cache pages, releasing our lock
		// ===========================================================
		
		try {
			self::writeCachePage($page_images);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Cache set error",
				'data'=>$page_images,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return (int)$rows_set;
		
	}
	
	/**
	 * Replaces a SINGLE L4 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L4->L1 walks for the L5->L4 intersect, then adding the new L4->L1 walks 
	 * contained in the $data structure. 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * @param int/string $L4 | Single L4
	 * 	 
         * @param array $data | array of L3's in the form "L3_id"=>"L2s"		 
	 *	=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		=> KEY @param int/string | L1 id
	 *		    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL4($L5, $L4, $data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
				
				 
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L4_col]['php'],
								'format'=>'both',
								'var'=>$L4
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid L4 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}			
			
			// VALIDATE L3 STRUCTURES
			
		}		

		// Replace items
		// ==========================
		
		$replace_data = array( $L5=>array( $L4=>$data));
				
		
		$replace_ctrl = array(
				    'validate'=>false
		);
				
		try {
			$rows_changed = self::replaceL4_multi($data, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error calling self::replaceL4_multi",
				'data'=>array('replace_data'=>$replace_data, 'replace_ctrl'=>$replace_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		return $rows_changed;
		
	}
	
	
	/**
	 * Replaces multiple L4 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L4->L1 walks for each L5->L4 intersect structure passed in the $data array, 
	 * then adding the new L4->L1 walks contained in the intersect structure. 
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
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL4_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}				
		
                $struct = $this->_struct();
							

		// Validate data array
		// ===========================================================

		if($ctrl['validate'] == true){
		    		    
			$validator = new FOX_dataStore_validator($struct);
			
			$tree_valid = $validator->validateL5Trie($data);
			
			if($tree_valid !== true){
			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid key in data array",
					'data'=>$tree_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}					
		}
						

		// Lock all L5 cache pages in the $data array
		// ===========================================================
		
		$L5_ids = array_keys($data);
		
		try {
			$page_images = self::lockCachePage($L5_ids);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache pages",
				'data'=>array("pages"=>$L5_ids),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// 1) Build $insert_data array
		// 2) Calculate $del_args
		// 3) Rebuild cache page images
		// ================================================================

		$insert_data = array();	
		$del_args = array();		
		$page_images = array();

		foreach( $data as $L5 => $L4s ){	
			
			foreach( $L4s as $L4 => $L3s ){

				$page_images[$L5][$this->L4_col][$L4] = true;
				
				$del_args[] = array(
						    $this->L5_col=>$L5, 
						    $this->L4_col=>$L4
				);

				foreach( $L3s as $L3 => $L2s ){

					$page_images[$L5][$this->L3_col][$L4][$L3] = true;

					foreach( $L2s as $L2 => $L1s ){

						$page_images[$L5][$this->L2_col][$L4][$L3][$L2] = true;

						foreach( $L1s as $L1 => $val){

							$page_images[$L5]["keys"][$L4][$L3][$L2][$L1] = $val;

							$insert_data[] = array(
										$this->L5_col=>$L5,
										$this->L4_col=>$L4,
										$this->L3_col=>$L3,
										$this->L2_col=>$L2,
										$this->L1_col=>$L1,
										$this->L0_col=>$val
							);
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
		
		
		// Update the database
		// ===========================================================
		
		$db = new FOX_db();
			
		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		
		try {
			$db->beginTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Couldn't initiate transaction",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Clear all L5->L4 intersects from the db
		// ===========================================================

		$args = array(
				'key_col'=>array(
						    $this->L5_col, 
						    $this->L4_col
				),
				'args'=>$del_args
		);
		
		$del_ctrl = array('args_format'=>'matrix');
		
		try {
			$db->runDeleteQuery($struct, $args, $del_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error while deleting from the database. Error rolling back.",
					'data'=>array('rollback_exception'=>$child_2, 'args'=>$args, 'del_ctrl'=>$del_ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error while deleting from the database. Successful rollback.",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		


		// Insert updated walks
		// ===========================================================

		$insert_cols = null;
		$insert_ctrl = null;
		
		try {
			$rows_set = $db->runInsertQueryMulti($struct, $insert_data, $insert_cols, $insert_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Error rolling back.",
					'data'=>array('insert_data'=>$insert_data, 'rollback_exception'=>$child_2),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error while writing to the database. Successful rollback.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}				

		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		try {
			$db->commitTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error commiting transaction to database",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		// NOTE: we update the class cache before the persistent cache, so that if the
		// persistent cache write fails, the class cache will still in the correct
		// state. Any cache pages that fail to update if the persistent cache throws an
		// error during the write operation will remain locked, causing them to be purged 
		// on the next read operation.
		
		
		// Write the temp class cache to the class cache
		// ===========================================================
		
		foreach( $page_images as $L5 => $image ){
		    
			$this->cache[$L5] = $image;		    		    
		}
		unset($L5, $image);
		
		
		// Overwrite the locked L5 cache pages, releasing our lock
		// ===========================================================
		
		try {
			self::writeCachePage($page_images);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Cache set error",
				'data'=>$page_images,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		return (int)$rows_set;
		
	}
	
	
	/**
	 * Replaces a SINGLE L5 trie structure which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L5->L1 walks for the L5 intersect, then adding the new L5->L1 walks 
	 * contained in the $data structure. 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5
	 * 	 
         * @param array $data | array of L4's in the form "L4_id"=>"L3s"		 
	 *	=> ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		    => KEY @param int/string | L1 id
	 *			=> VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL5($L5, $data, $ctrl=null){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
				
				 
		if($ctrl['validate'] != false){		   

			// Each variable has to be validated individually. If we spin the variables
			// into a trie, PHP will automatically convert strings that map to ints ("17")
			// into (int) keys, which will defeat the validators
		    		    		    
			$struct = $this->_struct();
			
			$validator = new FOX_dataStore_validator($struct);	
			
			$is_valid = $validator->validateKey( array(
								'type'=>$struct['columns'][$this->L5_col]['php'],
								'format'=>'both',
								'var'=>$L5
			));	

			if($is_valid !== true){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid L5 key",
					'data'=>$is_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}							
			
			// VALIDATE L4 STRUCTURES
			
		}		

		// Replace items
		// ==========================
		
		$replace_data = array( $L5=>$data );
				
		
		$replace_ctrl = array(
				    'validate'=>false
		);
				
		try {
			$rows_changed = self::replaceL5_multi($data, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error calling self::replaceL5_multi",
				'data'=>array('replace_data'=>$replace_data, 'replace_ctrl'=>$replace_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		return $rows_changed;
		
	}
	
	
	/**
	 * Replaces multiple L5 trie structures which MAY OR MAY NOT ALREADY EXIST in the datastore,
	 * deleting all L5->L1 walks for each L5 trie structure in the $data array, then adding the 
	 * new walks contained in the structure. 
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
	 *			    => VAL @param bool/int/float/string/array/obj $val | key value
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 *
	 * @return int | Exception on failure. Int number of rows SET on success.
	 */

	public function replaceL5_multi($data, $ctrl=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}				
		
                $struct = $this->_struct();
		
		
		// Validate data array
		// ===========================================================

		if($ctrl['validate'] == true){
		    
			$validator = new FOX_dataStore_validator($struct);
		    
			$tree_valid = $validator->validateL5Trie($data);
			
			if($tree_valid !== true){
			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Invalid key in data array",
					'data'=>$tree_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
					
		} 
		
		// Lock all L5 cache pages in the $data array
		// ===========================================================
		
		$L5_ids = array_keys($data);
		
		try {
			self::lockCachePage($L5_ids);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache pages",
				'data'=>array("pages"=>$L5_ids),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// 1) Build $insert_data array
		// 2) Rebuild cache page images
		// ================================================================

		$update_cache = array();
		$insert_data = array(); 

		foreach( $data as $L5 => $L4s ){
		    
			$update_cache[$L5]['all_cached'] = true;	
			
			foreach( $L4s as $L4 => $L3s ){

				$update_cache[$L5][$this->L4_col][$L4] = true;

				foreach( $L3s as $L3 => $L2s ){

					$update_cache[$L5][$this->L3_col][$L4][$L3] = true;

					foreach( $L2s as $L2 => $L1s ){

						$update_cache[$L5][$this->L2_col][$L4][$L3][$L2] = true;

						foreach( $L1s as $L1 => $val){

							$update_cache[$L5]["keys"][$L4][$L3][$L2][$L1] = $val;

							$insert_data[] = array(
										$this->L5_col=>$L5,
										$this->L4_col=>$L4,
										$this->L3_col=>$L3,
										$this->L2_col=>$L2,
										$this->L1_col=>$L1,
										$this->L0_col=>$val
							);
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
		
		
		// Update the database
		// ===========================================================
		
		$db = new FOX_db();		
		
		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		
		try {
			$db->beginTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Couldn't initiate transaction",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Clear all entries for the L5s from the db
		// ===========================================================

		$args = array(
				array("col"=>$this->L5_col, "op"=>"=", "val"=>$L5_ids)
		);
		
		$del_ctrl = null;
		
		try {
			$db->runDeleteQuery($struct, $args, $del_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error while deleting from the database. Error rolling back.",
					'data'=>array('rollback_exception'=>$child_2, 'args'=>$args),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error while deleting from the database. Successful rollback.",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Insert updated walks
		// ===========================================================

		$insert_col = null;
		$insert_ctrl = null;
		
		try {
			$rows_set = $db->runInsertQueryMulti($struct, $insert_data, $insert_col, $insert_ctrl);
		}
		catch (FOX_exception $child) {
		    
			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Error rolling back.",
					'data'=>array('insert_data'=>$insert_data, 'rollback_exception'=>$child_2),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error while writing to the database. Successful rollback.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}				

		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		try {
			$db->commitTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error commiting transaction to database",
				'data'=>$insert_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// NOTE: we update the class cache before the persistent cache, so that if the
		// persistent cache write fails, the class cache will still in the correct
		// state. Any cache pages that fail to update if the persistent cache throws an
		// error during the write operation will remain locked, causing them to be purged 
		// on the next read operation.
		
		
		// Write the temp class cache to the class cache
		// ===========================================================
		
		foreach( $update_cache as $L5 => $image ){
		    
			$this->cache[$L5] = $image;		    		    
		}
		unset($L5, $image);
		
		
		// Overwrite the locked L5 cache pages, releasing our lock
		// ===========================================================
		
		try {
			self::writeCachePage($update_cache);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Cache set error",
				'data'=>$update_cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return (int)$rows_set;
		
	}
	
	
	// #####################################################################################################################
	// #####################################################################################################################
	
	
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Add default control params
		// ==========================

		$ctrl_default = array(
			'validate'=>true,
			'mode'=>'trie',
			'trap_*'=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);		
					
		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		// Validate data array
		// ===========================================================
		
                $struct = $this->_struct();				
		
		$del_data = array();
			
		if($ctrl['mode'] == 'matrix'){
		    
				
		    	if($ctrl['validate'] != false){	    // Performance optimization (saves 1 op per key)
		    
			    
				$validator = new FOX_dataStore_validator($struct);
				
				foreach( $data as $row ){   
			
					$row_valid = $validator->isRowSequential($row);
					
					if( $row_valid !== true ){
					    
						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Invalid row in data array",
							'data'=>array('faulting_row'=>$row, 'error'=>$row_valid),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));					    					    
					}																

				} 
				unset($row);			    
			
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
			
			$trie = FOX_trie::loftMatrix($data, $columns, $ctrl=null);
			
			$del_data = FOX_trie::clipAssocTrie($trie, $columns, $ctrl=null);
			
							
		}
		elseif($ctrl['mode'] == 'trie'){
		    
		    
			if($ctrl['validate'] != false){	    // Validate the $data array	   

				$validator = new FOX_dataStore_validator($struct);			
				$tree_valid = $validator->validateL5Trie($data);

				if($tree_valid !== true){

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Invalid key in data array",
						'data'=>$tree_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}				    
			}
			
			$del_data = $data;						
		    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Invalid ctrl['mode'] parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
				    'numeric'=>5,
				    'text'=>"$error_msg",
				    'data'=>$data,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
			    ));	

			}
		}		    
		    				
		
		// Lock affected cache pages
		// ===========================================================

		try {
			$cache_pages = self::lockCachePage( array_keys($del_data) );
			$update_cache = $cache_pages;
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error locking cache",
				'data'=>$del_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}			

		
		// Build db insert array and updated cache pages array
		// ===========================================================	
		
		$dead_pages = array();		

		foreach( $del_data as $L5 => $L4s ){		
		    
			if( count($L4s) == 0 ){	    // If the trie has no L4 structures, delete the
						    // entire cache page from the class cache, and flag
						    // the page to be flushed from the persistent cache
			    
				$dead_pages[] = $L5;
				unset($update_cache[$L5]);
			}
			
			foreach( $L4s as $L4 => $L3s ){
			    
				if( count($L3s) == 0 ){	    // If the L4 structure has no L3 structures, 
							    // delete its descendents' cache entries

					unset($update_cache[$L5][$this->L4_col][$L4]);
					unset($update_cache[$L5][$this->L3_col][$L4]);
					unset($update_cache[$L5][$this->L2_col][$L4]);
					unset($update_cache[$L5]["keys"][$L4]);
				}			    

				foreach( $L3s as $L3 => $L2s ){
				    
					if( count($L2s) == 0 ){	    // If the L3 structure has no L2 structures, 
								    // delete its descendents' cache entries

						unset($update_cache[$L5][$this->L3_col][$L4][$L3]);
						unset($update_cache[$L5][$this->L2_col][$L4][$L3]);
						unset($update_cache[$L5]["keys"][$L4][$L3]);
					}				    

					foreach( $L2s as $L2 => $L1s ){

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
		}
		unset($L5, $L4s);
			
		
		// Clear the specified structures from the DB
		// ===========================================================

		$db = new FOX_db(); 
				
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
			$rows_changed = $db->runDeleteQuery($struct, $args, $del_ctrl);
		}
		catch (FOX_exception $child) {

		    
			// Try to unlock the cache pages we locked
		    
			try {
				self::writeCachePage($cache_pages);
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Error unlocking cache pages.",
					'data'=>array('cache_exception'=>$child_2, 'cache_pages'=>$cache_pages, 
						      'del_args'=>$args, 'del_ctrl'=>$del_ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}									

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error while writing to the database. Successfully unlocked cache pages.",
					'data'=>array('del_args'=>$args, 'del_ctrl'=>$del_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
			
		}
			
		// NOTE: we update the class cache before the persistent cache so that if the
		// persistent cache write fails, the class cache will still in the correct
		// state. Any cache pages that fail to update if the persistent cache throws an
		// error during the write operation will remain locked, causing them to be pruged 
		// on the next read operation.
		
		
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
		    
			try {
				self::writeCachePage($update_cache);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>9,
					'text'=>"Error writing to cache",
					'data'=>$update_cache,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		}

		// Flush any dead pages from the cache
		// ===========================================================
		
		if($dead_pages){
		    
			try {
				self::flushCachePage($dead_pages);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>10,
					'text'=>"Error flushing pages from cache",
					'data'=>$dead_pages,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}		    		    		    
		}								
		
		return (int)$rows_changed;

	}	
	
		
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
	 * @param int/string/array $L1 | Single L1 id as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropL1($L5, $L4, $L3, $L2, $L1, $ctrl=null) {

		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		if( !is_array($L1) ){		    
			$L1 = array($L1);
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
					    
		$data = array();		
		$data[$L5][$L4][$L3][$L2] = $L1;
		
		
		if($ctrl['validate'] != false){
		    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);	
										
			$trie_valid = $validator->validateL5Trie($data);

			if($trie_valid !== true){	// Note the !==

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid arguments",
					'data'=>$trie_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
		    
		}		
		
		$drop_ctrl = array(
				    'mode'=>'trie',
				    'validate'=>false
		);
		
		try {
			$result = self::dropMulti($data, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error calling self::dropL1_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;
		
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		                
		
		// Expand any L1 arrays into individual matrix rows
		// ============================================================
		
		
		$processed = array();
		
		if($ctrl['validate'] != false){	    
			
			$struct = $this->_struct();
			$validator = new FOX_dataStore_validator($struct);				
		}
		
			
		foreach( $data as $row ){

		    
			if($ctrl['validate'] != false){
    
				$row_valid = $validator->validateL1Row_simple($row);

				if($row_valid !== true){    // Note the !==

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}
			}

			// If the value is a single key, convert it to an array so the
			// foreach() loop can operate on it

			if( !is_array($keys[$this->L1_col]) ){

				$row[$this->L1_col] = array($row[$this->L1_col]);
			}

			foreach( $keys[$L1_col] as $L1 ){

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
			

		
		
		$drop_ctrl = array(
				    'mode'=>'matrix',
				    'validate'=>false
		);
				
		try {						
			$result = self::dropMulti($processed, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::dropMulti()",
				'data'=>array('data'=>$data, 'processed'=>$processed, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;	      
		
	}
	    
	
	/**
	 * Drops one or more L2 branches within a single L5->L3 walk from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5 id as int/string
	 * @param int/string $L4 | Single L4 id as int/string
	 * @param int/string $L3 | Single L3 id as int/string
	 * @param int/string/array $L2 | Single L2 id as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropL2($L5, $L4, $L3, $L2, $ctrl=null) {

		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		if( !is_array($L2) ){		    
			$L2 = array($L2);
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
					    
		$data = array();		
		$data[$L5][$L4][$L3] = $L2;
		
		
		if($ctrl['validate'] != false){
		    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);	
										
			$trie_valid = $validator->validateL5Trie($data);

			if($trie_valid !== true){	// Note the !==

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid arguments",
					'data'=>$trie_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
		    
		}		
		
		$drop_ctrl = array(
				    'mode'=>'trie',
				    'validate'=>false
		);
		
		try {
			$result = self::dropMulti($data, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error calling self::dropL1_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;
		
	}
	
	
	/**
	 * Drops multiple [L5->L3 walk + L2 branch] arrays from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string $L3 | Single L3 id as int/string
	 *	    => VAL @param int/string/array $L2 | Single L2 id as int/string, multiple as array of int/string.
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropL2_multi($data, $ctrl=null){	

	
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		                
		
		// Expand any L2 arrays into individual matrix rows
		// ============================================================
		
		
		$processed = array();
		
		if($ctrl['validate'] != false){	   
			
			$struct = $this->_struct();
			$validator = new FOX_dataStore_validator($struct);			
		}
			
		foreach( $data as $row ){

		    
			if($ctrl['validate'] != false){
		    
				$row_valid = $validator->validateL2Row_simple($row);

				if($row_valid !== true){    // Note the !==

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}
			
			}

			// If the value is a single key, convert it to an array so the
			// foreach() loop can operate on it

			if( !is_array($keys[$this->L2_col]) ){

				$row[$this->L2_col] = array($row[$this->L2_col]);
			}

			foreach( $keys[$L2_col] as $L2 ){

				$processed[] = array(
							$this->L5_col => $row[$this->L5_col],
							$this->L4_col => $row[$this->L4_col],
							$this->L3_col => $row[$this->L3_col],
							$this->L2_col => $L2				    
				);
			}
			unset($L2);

		}
		unset($row);
					
		
		$drop_ctrl = array(
				    'mode'=>'matrix',
				    'validate'=>false
		);
				
		try {						
			$result = self::dropMulti($processed, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::dropMulti()",
				'data'=>array('data'=>$data, 'processed'=>$processed, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;	      
		
	}
	
	
	/**
	 * Drops one or more L3 branches within a single L5->L4 walk from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5 id as int/string
	 * @param int/string $L4 | Single L4 id as int/string
	 * @param int/string/array $L3 | Single L3 id as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropL3($L5, $L4, $L3, $ctrl=null) {

		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		if( !is_array($L3) ){		    
			$L3 = array($L3);
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
					    
		$data = array();		
		$data[$L5][$L4] = $L3;
		
		
		if($ctrl['validate'] != false){
		    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);	
										
			$trie_valid = $validator->validateL5Trie($data);

			if($trie_valid !== true){	// Note the !==

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid arguments",
					'data'=>$trie_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}
		    
		}		
		
		$drop_ctrl = array(
				    'mode'=>'trie',
				    'validate'=>false
		);
		
		try {
			$result = self::dropMulti($data, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error calling self::dropL1_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;
		
	}
	
	
	/**
	 * Drops multiple [L5->L4 walk + L3 branch] arrays from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string $L4 | Single L4 id as int/string
	 *	    => VAL @param int/string/array $L3 | Single L3 id as int/string, multiple as array of int/string.
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropL3_multi($data, $ctrl=null){	

	
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		                
		
		// Expand any L3 arrays into individual matrix rows
		// ============================================================
		
		
		$processed = array();
		
		if($ctrl['validate'] != false){	   
			
			$struct = $this->_struct();
			$validator = new FOX_dataStore_validator($struct);			
		}
			
		foreach( $data as $row ){

		    
			if($ctrl['validate'] != false){
		    
				$row_valid = $validator->validateL3Row_simple($row);

				if($row_valid !== true){    // Note the !==

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}
			
			}

			// If the value is a single key, convert it to an array so the
			// foreach() loop can operate on it

			if( !is_array($keys[$this->L3_col]) ){

				$row[$this->L3_col] = array($row[$this->L3_col]);
			}

			foreach( $keys[L3_col] as $L3 ){

				$processed[] = array(
							$this->L5_col => $row[$this->L5_col],
							$this->L4_col => $row[$this->L4_col],
							$this->L3_col => $L3			    
				);
			}
			unset($L3);

		}
		unset($row);
					
		
		$drop_ctrl = array(
				    'mode'=>'matrix',
				    'validate'=>false
		);
				
		try {						
			$result = self::dropMulti($processed, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::dropMulti()",
				'data'=>array('data'=>$data, 'processed'=>$processed, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;	      
		
	}
	
	
	/**
	 * Drops one or more L4 branches within a single L5 object from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L5 | Single L5 id as int/string
	 * @param int/string/array $L4 | Single L4 id as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropL4($L5, $L4, $ctrl=null) {

		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		if( !is_array($L4) ){		    
			$L4 = array($L4);
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
					    
		$data = array();		
		$data[$L5] = $L4;
		
		
		if($ctrl['validate'] != false){
		    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);	
										
			$trie_valid = $validator->validateL5Trie($data);

			if($trie_valid !== true){	// Note the !==

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid arguments",
					'data'=>$trie_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}		    
		}		
		
		$drop_ctrl = array(
				    'mode'=>'trie',
				    'validate'=>false
		);
		
		try {
			$result = self::dropMulti($data, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error calling self::dropL1_multi()",
				'data'=>array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;
		
	}
	
	
	/**
	 * Drops multiple [L5 + L4 branch] arrays from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string $L5 | Single L5 id as int/string
	 *	    => VAL @param int/string/array $L4 | Single L4 id as int/string, multiple as array of int/string.
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropL4_multi($data, $ctrl=null){	

	
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		                
		
		// Expand any L4 arrays into individual matrix rows
		// ============================================================
		
		
		$processed = array();
		
		if($ctrl['validate'] != false){	   
			
			$struct = $this->_struct();
			$validator = new FOX_dataStore_validator($struct);			
		}
			
		foreach( $data as $row ){

		    
			if($ctrl['validate'] != false){
		    
				$row_valid = $validator->validateL4Row_simple($row);

				if($row_valid !== true){    // Note the !==

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}
			
			}

			// If the value is a single key, convert it to an array so the
			// foreach() loop can operate on it

			if( !is_array($keys[$this->L4_col]) ){

				$row[$this->L4_col] = array($row[$this->L4_col]);
			}

			foreach( $keys[L4_col] as $L4 ){

				$processed[] = array(
							$this->L5_col => $row[$this->L5_col],
							$this->L4_col => $L4			    
				);
			}
			unset($L4);

		}
		unset($row);
					
		
		$drop_ctrl = array(
				    'mode'=>'matrix',
				    'validate'=>false
		);
				
		try {						
			$result = self::dropMulti($processed, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::dropMulti()",
				'data'=>array('data'=>$data, 'processed'=>$processed, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;	      
		
	}
	
	
	/**
	 * Drops one or more L5 objects from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string/array $L5 | Single L5 id as int/string, multiple as array of int/string.
	 * 
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate key	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropL5($L5, $ctrl=null) {

		
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		if( !is_array($L5) ){		    
			$L5 = array($L5);
		}
		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
					    
		
		if($ctrl['validate'] != false){
		    
			$struct = $this->_struct();		    
			$validator = new FOX_dataStore_validator($struct);	
										
			$trie_valid = $validator->validateL5Trie($L5);

			if($trie_valid !== true){	// Note the !==

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Invalid arguments",
					'data'=>$trie_valid,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			}		    
		}		
		
		$drop_ctrl = array(
				    'mode'=>'trie',
				    'validate'=>false
		);
		
		try {
			$result = self::dropMulti($L5, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error calling self::dropL1_multi()",
				'data'=>array('data'=>$L5, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;
		
	}
	
	
	/**
	 * Drops multiple L5 arrays from the datastore and cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of data arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int/string/array $L5 | Single L5 id as int/string, multiple as array of int/string.
	 *
         * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $validate | Validate keys	 
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropL5_multi($data, $ctrl=null){	

	
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$ctrl_default = array(
			"validate"=>true
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		                
		
		// Expand any L5 arrays into individual matrix rows
		// ============================================================
		
		
		$processed = array();
		
		if($ctrl['validate'] != false){	   
			
			$struct = $this->_struct();
			$validator = new FOX_dataStore_validator($struct);			
		}
			
		foreach( $data as $row ){

		    
			if($ctrl['validate'] != false){
		    
				$row_valid = $validator->validateL5Row_simple($row);

				if($row_valid !== true){    // Note the !==

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Invalid row in data array",
						'data'=>$row_valid,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));			    
				}			
			}

			// If the value is a single key, convert it to an array so the
			// foreach() loop can operate on it

			if( !is_array($keys[$this->L5_col]) ){

				$row[$this->L5_col] = array($row[$this->L5_col]);
			}

			foreach( $keys[L5_col] as $L5 ){

				$processed[] = array(
							$this->L5_col => $L5			    
				);
			}
			unset($L5);

		}
		unset($row);
					
		
		$drop_ctrl = array(
				    'mode'=>'matrix',
				    'validate'=>false
		);
				
		try {						
			$result = self::dropMulti($processed, $drop_ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::dropMulti()",
				'data'=>array('data'=>$data, 'processed'=>$processed, 'drop_ctrl'=>$drop_ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return $result;	      
		
	}
	
	
	
	// #####################################################################################################################
	// #####################################################################################################################
	
	
	/**
	 * Drops one or more L1's from *ALL L2 TRIES* in the datastore.
	 * 
	 * @version 1.0
	 * @since 1.0
	 * @param int/string/array $L1s | Single L1 as int/string, multiple as array of int/string.
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropL1Global($L1s) {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$struct = $this->_struct();				

		if( empty($L1s) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty args array",
				'data'=>$L1s,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$db = new FOX_db();
		
		$args = array(
				array("col"=>$this->L1_col, "op"=>"=", "val"=>$L1s)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return (int)$rows_changed;

	}


	/**
	 * Drops one or more L2's for *ALL L3 TRIES* in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/string/array $L2s | Single L2 as int/string, multiple as array of int/string.
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropL2Global($L2s) {
	    
	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		$struct = $this->_struct();			

		if( empty($L2s) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty args array",
				'data'=>$L2s,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$db = new FOX_db();
		
		$args = array(
				array("col"=>$this->L2_col, "op"=>"=", "val"=>$L2s)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		return (int)$rows_changed;

	}


	/**
	 * Drops one or more L3s for for *ALL L4 TRIES* in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/string $L3s | Single L3 as int/string, multiple as array of int/string.
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropL3Global($L3s) {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$struct = $this->_struct();				

		if( empty($L3s) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty args array",
				'data'=>$L3s,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$db = new FOX_db();
		
		$args = array(
				array("col"=>$this->L3_col, "op"=>"=", "val"=>$L3s)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		
		return (int)$rows_changed;

	}


	/**
	 * Drops one or more L4s for for *ALL L5 TRIES* in the datastore
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/string/array $L4s | Single L4 as int/string, multiple as array of int/string.
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropL4Global($L4s) {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		
		$struct = $this->_struct();					
		
		if( empty($L4s) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty args array",
				'data'=>$L4s,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$db = new FOX_db();
		
		$args = array(
				array("col"=>$this->L4_col, "op"=>"=", "val"=>$L4s)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		
		$db = new FOX_db();
		$struct = $this->_struct();		

		try {
			$db->runTruncateTable($struct);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing the database",
				'data'=>null,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Since this operation affects ALL L5 pages, we have to flush the 
		// entire cache namespace
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}	

	}

	

    
} // End of class FOX_dataStore_paged_L5_base


?>