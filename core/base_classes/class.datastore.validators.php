<?php

/**
 * FOXFIRE PAGED ABSTRACT DATASTORE VALIDATORS HELPER CLASS
 * Validates data structures used in FoxFire datastore classes
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

class FOX_dataStore_validator {

    
    	var $struct;		    // Host class' database definition array
	
	var $cols;		    // Database columns array
	
	var $order;		    // Order of storage class. Example: [level_3][level_2][level_1] => value
				    // is a 3rd order datastore.
	
	
	// ============================================================================================================ //
	
	
    
	public function __construct($struct){
	    
	    
		$this->struct = $struct;
		
		$this->order_dict = array();
		$this->col_dict = array();		
		
		$this->order = count($struct['columns']) - 1;
		
		$col_index = $this->order;
		
		foreach( $struct['columns'] as $col_name => $data ){
		    
			$this->order_dict[$col_index] = array('db_col'=>$col_name, 'type'=>$data['php']);
			$this->col_dict[$col_name] = array('order'=>$col_index, 'type'=>$data['php']);			
			
			$col_index--;
		}
		unset($col_name, $data);
				
	}
	
	
	/**
	 * Validates a matrix row structure
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $row | matrix row structure to validate
	 *  
         * @param array $ctrl | Control parameters
	 * 
	 *	=> VAL @param string $end_node_format | End node format 'scalar', 'array', or 'trie'
	 * 
	 *	=> VAL @param array $array_ctrl | Control parameters when operating in 'array' mode
	 *		=> VAL @param string $mode | Array format 'normal' or 'inverse'
	 *		=> VAL @param string $end_node_format | End node format 'scalar', 'array', or 'trie'
	 * 
	 *	=> VAL @param array $trie_ctrl | Control parameters when operating in 'trie' mode
	 *		=> VAL @param string $mode | Trie format 'data' or 'control'
	 *		=> VAL @param bool $allow_wildcard | Allow wildcard character to be used
	 *		=> VAL @param int $clip_order | Order to clip keys at when in 'data' mode
	 * 
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */	
	
	public function validateMatrixRow($row, $ctrl=null) {
	    
	    
		$ctrl_default = array(
					'allowed_keys'=>array_keys($this->col_dict),
					'required_keys'=>array(),
					'ignored_keys'=>array($this->order_dict[0]['db_col']),	// Ignore the L0 column
					'allow_foreign_keys'=>false,
					'end_node_format'=>'scalar', 
					'array_ctrl'=>array(
							    'mode'=>'normal'
					),
					'trie_ctrl'=>array(
							    'mode'=>'control',
							    'allow_wildcard'=>false,
							    'wildcard_token'=>'*',
							    'clip_order'=>1	
					)		    
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	    
	    
		
		// Invert 'allowed_keys', 'required_keys', and 'ignored_keys' 
		// ============================================================
		
		if( !empty($ctrl['allowed_keys']) ){
		    
			$temp = array();

			foreach( $ctrl['allowed_keys'] as $id => $val){

				$temp[$val] = true;
			}
			unset($id, $val);

			$ctrl['allowed_keys'] = $temp;
			
			unset($temp);		
		}		
		
		if( !empty($ctrl['required_keys']) ){
		    
			$temp = array();

			foreach( $ctrl['required_keys'] as $id => $val){

				$temp[$val] = true;
			}
			unset($id, $val);

			$ctrl['required_keys'] = $temp;	

			unset($temp);		
		}
		
		if( !empty($ctrl['ignored_keys']) ){
		    
			$temp = array();

			foreach( $ctrl['ignored_keys'] as $id => $val){

				$temp[$val] = true;
			}
			unset($id, $val);

			$ctrl['ignored_keys'] = $temp;	

			unset($temp);		
		}		
		
			
		// Trap missing keys
		// ============================================================
		
		foreach( $ctrl['required_keys'] as $key => $fake_var){
		    		    
			if( !FOX_sUtil::keyExists($key, $row) ){

				return array(
					'numeric'=>1,	
					'message'=>"Row is missing a required key: '$key'",
					'var'=>$row
				);	    		    
			}		    			
		}
		unset($key, $fake_var);
		
		
		// Trap foreign keys
		// ============================================================
		
		if($ctrl['allow_foreign_keys'] == false){
		    
			foreach( $row as $key => $val ){

				if( !FOX_sUtil::keyExists($key, $ctrl['allowed_keys']) ){

					return array(
						'numeric'=>2,	
						'message'=>"Row contains a foreign key: '$key', and 'allow_foreign_keys' is false",
						'var'=>$row
					);	    		    
				}		    
			}
			unset($key, $val);
		}
		
		
		// Process row
		// ============================================================
		
		$lowest_order_key = true;
		
		for( $order = 1; $order <= $this->order; $order++ ){

			// Skip ignored keys
		    
			if( FOX_sUtil::keyExists($this->order_dict[$order]['db_col'], $ctrl['ignored_keys']) ){	
			    
				continue;
			}
		    
			if( FOX_sUtil::keyExists($this->order_dict[$order]['db_col'], $row) ){

			    
				if($lowest_order_key){
				    				    
					$lowest_order_key = false;
				    
					switch($ctrl['end_node_format']){

						case "scalar" : {
						    
							$val_args = array(
									'type'=>$this->order_dict[$order]['type'],
									'format'=>'scalar',
									'var'=>$row[$this->order_dict[$order]['db_col']]
							);
							
							try {			
								$check_result = self::validateKey($val_args);
							}
							catch (FOX_exception $child) {

								throw new FOX_exception( array(
									'numeric'=>1,
									'text'=>"Error in self::validateKey()",
									'data'=>$val_args,
									'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
									'child'=>$child
								));	
							}						    

							if( $check_result !== true ){

								return array(	
										'numeric'=>3,				    
										'message'=>$check_result,
										'row'=>$row, 
										'key'=>$this->order_dict[$order]['db_col'],
										'var'=>$row[$this->order_dict[$order]['db_col']]
								);				    
							}

						} break;

						case "array" : {

							if( !is_array($row[$this->order_dict[$order]['db_col']]) ){
							    							    
								return array(	
										'numeric'=>4,				    
										'message'=>"Must be an array",
										'row'=>$row, 
										'key'=>$this->order_dict[$order]['db_col'],
										'var'=>$row[$this->order_dict[$order]['db_col']]
								);							    
							}

							if( $ctrl['array_ctrl']['mode'] == 'normal' ){


								// In 'normal' mode, array end nodes are structured as
								// 
								//	array( 'K1' => true,
								//	       'K2' => true )
								//	       
								// ==================================================

								foreach( $row[$this->order_dict[$order]['db_col']] as $key => $val ){

								    
									$val_args = array(
											    'type'=>$this->order_dict[$order]['type'],
											    'format'=>'scalar',
											    'var'=>$key
									);

									try {			
										$check_result = self::validateKey($val_args);
									}
									catch (FOX_exception $child) {

										throw new FOX_exception( array(
											'numeric'=>2,
											'text'=>"Error in self::validateKey()",
											'data'=>$val_args,
											'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
											'child'=>$child
										));	
									}						

									if($check_result !== true){
									    
										break;
									}

								}
								unset($key, $val);

								if( $check_result !== true ){

									return array(	
											'numeric'=>5,				    
											'message'=>$check_result,
											'row'=>$row, 
											'key'=>$this->order_dict[$order]['db_col'],
											'var'=>$row[$this->order_dict[$order]['db_col']]
									);				    
								}								    

							}
							elseif($ctrl['array_ctrl']['mode'] == 'inverse') {

								// In 'inverse' mode, array end nodes are structured as
								// 
								//	array( 0 => 'K1',
								//	       1 => 'K2' )
								//	       
								// ==================================================						    

								foreach( $row[$this->order_dict[$order]['db_col']] as $key => $val ){
								    
									$val_args = array(
											    'type'=>$this->order_dict[$order]['type'],
											    'format'=>'scalar',
											    'var'=>$val
									);

									try {			
										$check_result = self::validateKey($val_args);
									}
									catch (FOX_exception $child) {

										throw new FOX_exception( array(
											'numeric'=>3,
											'text'=>"Error in self::validateKey()",
											'data'=>$val_args,
											'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
											'child'=>$child
										));	
									}								    

									if($check_result !== true){
									    
										break;
									}

								}
								unset($key, $val);

								if( $check_result !== true ){

									return array(	
											'numeric'=>6,				    
											'message'=>$check_result,
											'row'=>$row, 
											'key'=>$this->order_dict[$order]['db_col'],
											'var'=>$row[$this->order_dict[$order]['db_col']]
									);				    
								}								    

							}
							else {
							    
								throw new FOX_exception( array(
									'numeric'=>4,
									'text'=>"Invalid ['array_ctrl']['mode'] parameter",
									'data'=>$ctrl['array_ctrl']['mode'],
									'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
									'child'=>null
								));							    
							    
							}

						} break;

						case "trie" : {	

							if( !isset($ctrl['trie_ctrl']['order']) ){
							    
								$ctrl['trie_ctrl']['order'] = $order;	
							}

							try {			
								$check_result = self::validateTrie(
												    $row[$this->order_dict[$order]['db_col']],
												    $ctrl['trie_ctrl']
								);
							}
							catch (FOX_exception $child) {

								throw new FOX_exception( array(
									'numeric'=>5,
									'text'=>"Error in self::validateTrie()",
									'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
									'child'=>$child
								));	
							}							

							if( $check_result !== true ){  

								return array(	
										'numeric'=>7,				    
										'message'=>$check_result,
										'row'=>$row, 
										'key'=>$this->order_dict[$order]['db_col'],
										'var'=>$row[$this->order_dict[$order]['db_col']]
								);				    
							}

						} break;

						default : {

							throw new FOX_exception( array(
								'numeric'=>6,
								'text'=>"Invalid end_node_format parameter",
								'data'=>$ctrl['end_node_format'],
								'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
								'child'=>null
							));								    							    
						}

					}												
			       
			        }
				else {
				    
					$val_args = array(
							    'type'=>$this->order_dict[$order]['type'],
							    'format'=>'scalar',
							    'var'=>$row[$this->order_dict[$order]['db_col']]
					);

					try {			
						$check_result = self::validateKey($val_args);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>7,
							'text'=>"Error in self::validateKey()",
							'data'=>$val_args,
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>$child
						));	
					}										

					if( $check_result !== true ){

						return array(	
								'numeric'=>8,				    
								'message'=>$check_result,
								'row'=>$row, 
								'key'=>$this->order_dict[$order]['db_col'],
								'var'=>$row[$this->order_dict[$order]['db_col']]
						);				    
					}				    
				    
				}
				
			}

		}
				
		return true;
	    
	}
	
	
	/**
	 * Validates a trie structure 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | trie structure to validate
	 *  
         * @param array $ctrl | Control parameters
	 *	=> VAL @param int $order | Order of trie structure
	 *	=> VAL @param string $mode | Operation mode 'control' | 'data'
	 *	=> VAL @param bool $allow_wildcard | Allow wildcards (universal selector) to be used in control tries
	 *	=> VAL @param string $wildcard_token | String to use as wildcard token
	 *	=> VAL @param int $clip_order | Order to clip keys at when in 'data' mode	 
	 * 
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateTrie($data, $ctrl=null){
	    
	    
		$ctrl_default = array(
			'order'=>$this->order,
			'mode'=>'control',
			'allow_wildcard'=>false,
			'wildcard_token'=>'*',		    
			'clip_order'=>false		    
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	
		
		
	    	if( ($ctrl['order'] > $this->order) || ($ctrl['order'] < 0) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Specified order is not valid",
				'data'=>array("order"=>$ctrl['order'], "class_order"=>$this->order),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		
	    	if( ($ctrl['mode'] != 'data') && ($ctrl['mode'] != 'control') ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid 'mode' parameter",
				'data'=>array('ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}			
		
	    	if( ($ctrl['mode'] == 'data') && !is_int($ctrl['clip_order']) ){
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"The 'clip_order' parameter must be set when operating in 'data' mode",
				'data'=>array('ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}
		
	    	if( ($ctrl['mode'] == 'data') && ($ctrl['allow_wildcard'] != false) ){
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Wildcard selectors cannot be used in 'data' mode",
				'data'=>array('ctrl'=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}		
	    		
		try {
		    
			if($ctrl['order'] == 0){
			    
				// We're at L0 in a walk
				return true;
			}
			    			    
			if( is_array($data) && !empty($data) ){

			    
				foreach( $data as $parent_id => $children ){

				    
					if( !(($ctrl['allow_wildcard'] == true) && ($parent_id == $ctrl['wildcard_token'])) ){
					    
					    
						$check_result = self::validateKey(array(
											'type'=>$this->order_dict[$ctrl['order']]['type'],
											'format'=>'scalar',
											'var'=>$parent_id
						));				

						if( $check_result !== true ){

							return array(	'numeric'=>1,
									'message'=>$check_result,
									'key'=>$ctrl['order'], 
									'val'=>$parent_id
							);			    
						}					    
					}
					
					$child_result = self::validateTrie(
									    $children, 
									    array(
										    'order'=>$ctrl['order'] - 1,
										    'mode'=>$ctrl['mode'],
										    'allow_wildcard'=>$ctrl['allow_wildcard'],
										    'clip_order'=>$ctrl['clip_order'],
									    )
					);

					if( $child_result !== true ){

						if( FOX_sUtil::keyExists('trace', $child_result) ){

							$trace = array_merge($child_result['trace'], array( $ctrl['order'] => $parent_id) );
						}
						else {

						    $trace = array( $ctrl['order'] => $parent_id);
						}

						return array(	
								'numeric'=>2,
								'message'=>$child_result['message'],
								'key'=>$ctrl['order'], 
								'val'=>$parent_id,
								'trace'=>$trace
						);			    
					}					

				}
				unset($parent_id, $children);

			}
			else {

				if( $ctrl['mode'] == 'control' ){

					if( !is_array($data) && (!is_bool($data) || ($data === false)) ){

						$message =  "Each walk in a control trie must terminate with either (bool)true, ";
						$message .= "or an empty array, or must extend to the L1 key.";						

						return array(	'numeric'=>3,
								'message'=>$message,
								'key'=>$ctrl['order'], 
								'val'=>$parent_id,
						);					    
					}
					else {					
						return true;
					}

				}
				elseif( $ctrl['mode'] == 'data' ){


					if( $ctrl['order'] == ($ctrl['clip_order'] - 1) ){

						if( !is_array($data) && (!is_bool($data) || ($data === false)) ){

							$message =  "Each walk in a data trie, that terminates at the clip_order, must ";
							$message .= "terminate with an empty array.";						

							return array(	'numeric'=>4,
									'message'=>$message,
									'key'=>$ctrl['order'], 
									'val'=>$parent_id,
							);					    
						}
						else {					
							return true;
						}					    						
					}
					else {

						$message =  "Each walk in a data trie must terminate either at the clip_order ";
						$message .= "or at the L1 key.";						

						return array(	'numeric'=>5,
								'message'=>$message,
								'key'=>$ctrl['order'], 
								'val'=>$parent_id,
						);
					}

				}				    				    


			} // ENDOF: if( is_array($data) && !empty($data) )
			
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->order_dict),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		
		return true;		
						    
	}
	

	/**
	 * Checks that the datatype and value of a $L[x] key's NAME matches the datatype of the 
	 * corresponding column in the descendent class's database table.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $args | control args
	 *	=> VAL @param string $type | database column type ("int" | "string")
	 *	=> VAL @param string $format | acceptable formats ("scalar" | "array" | "both")
	 *	=> VAL @param string $var | variable to test
	 * 
	 * @return bool/array | Exception on failure, (bool)true on pass, data array on fail.
	 */

	public function validateKey($args){
	    
	    						
		if( empty($args['type']) || (($args['type'] != 'int') && ($args['type'] != 'string')) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Type must be either 'int' or 'string'",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));					
		}
		
		if( empty($args['format'])  
		    || (($args['format'] != 'scalar') && ($args['format'] != 'array') ) 
		){
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Format must be either 'scalar' or 'array'",
				'data'=>$args,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));					
		}		
		
		$result = true;		
		
		if( $args['var'] === null ){

			$result = array(	
				'numeric'=>1,			    
				'message'=>"Attempted to pass a null parameter.",
				'var'=>$args['var']				    
			);
			
			return $result;						
		}		
		
		if( $args['type'] == 'int' ){
		    		    
		    
			switch($args['format']){
								
				case "scalar" : {

					if( !is_int($args['var']) ){
					    
						$result = array(
						    	'numeric'=>2,	
							'message'=>"Must be an int (not just a string that converts to an int)",
							'var'=>$args['var']
						);					    					    
					}

				} break;

				case "array" : {
				    
					if( !is_array($args['var']) ){
					    
						$message_str =  "Must be an array where each key is an int ";
						$message_str .= "(not just a string that converts to an int)";
						
						$result = array(
						    	'numeric'=>3,	
							'message'=>$message_str,
							'var'=>$args['var']					    
						);
						
					}
					else {

						foreach( $args['var'] as $key => $val){


							if( !is_int($val) || empty($val) ){

								$message_str =  "Each key in the array must must be an int ";	
								$message_str .= "(not just a string that converts to an int)";								
								
								$result = array(	
								    	'numeric'=>4,	
									'message'=>$message_str,
									'var'=>$args['var'],
									'faulting_key'=>$key							    
								);

								break;
							}					    
						}	
						unset($key, $val);
					}				    				    
				    
				} break;
			    								
			}
									
			
		}		
		elseif( $args['type'] == 'string' ){
		    		    
		    
			switch($args['format']){
								
				case "scalar" : {

					if( !is_string($args['var']) || empty($args['var']) ){
					    
						$result = array(
						    	'numeric'=>5,	
							'message'=>"Must be a non-null string.",
							'var'=>$args['var']
						);					    					    
					}
					elseif( is_numeric($args['var']) ){
					    
						$message_str =  "Cannot be a string that PHP will auto-convert to an int when ";	
						$message_str .= "used as an array key. For example, (string)'7'. ";
						
						$result = array(
						    	'numeric'=>6,	
							'message'=>$message_str,
							'var'=>$args['var']
						);					    					    
					}					
					

				} break;

				case "array" : {
				    
					if( !is_array($args['var']) ){
					    
						$result = array(
						    	'numeric'=>7,	
							'message'=>"Must be an array, where each key is a string.",
							'var'=>$args['var']						    
						);
						
					}
					else {

						foreach( $args['var'] as $key => $val){


							if( !is_string($val) || empty($val) ){

								$result = array(
								    	'numeric'=>8,	
									'message'=>"Each key in the array must be a string.",
									'var'=>$args['var'],
									'faulting_key'=>$key							    
								);

								break;
							}
							elseif( is_numeric($val) ){
					    
								$message_str =  "Cannot be a string that PHP will auto-convert to an int when ";	
								$message_str .= "used as an array key. For example, (string)'7'. ";

								$result = array(
									'numeric'=>9,	
									'message'=>$message_str,
									'var'=>$args['var'],
									'faulting_key'=>$key								    
								);					    					    
							}
						}	
						unset($key, $val);
					}				    				    
				    
				} break;

			}
			
		}	
		
		return $result;	
			    
	}	
	
	
    
} // End of class FOX_dataStore_validator


?>