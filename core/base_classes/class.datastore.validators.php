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
		$this->cols = array();
		$this->order = count($struct['columns']) - 1;
		
		$col_index = $this->order;
		
		foreach( $struct['columns'] as $col_name => $data ){
		    
			$this->cols[ 'L' . $col_index ] = array('db_col'=>$col_name, 'type'=>$data['php']);
			
			$col_index--;
		}
				
	}
	
	
	
	public function validateMatrixRow($row, $ctrl=null) {
	    
	    
		$ctrl_default = array(
					'order'=>$this->order,
					'end_node_format'=>'scalarArray', // scalar, array, trie
					'array_ctrl'=>array(
							    'mode'=>'normal'
					),
					'trie_ctrl'=>array(
							    'mode'=>'control',
							    'allow_wildcard'=>false,
							    'clip_order'=>1	
					)		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	    
	    
		
	    	if($ctrl['order'] > $this->order){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Specified order is too high for this storage class",
				'data'=>array("order"=>$ctrl['order'], "class_order"=>$this->order),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}		
		
		$row_order = ($ctrl['order'] - count($row)) + 1;
		
		if($row_order < 0){
		    
			return array(
				'numeric'=>1,	
				'message'=>"Row contains extra keys",
				'var'=>$row
			);	    		    
		}
			
		
		for($level=$row_order; $level <= $this->order; $level++){


			// Trap missing keys
			// ==================================================

			if(!FOX_sUtil::keyExists($this->cols['L' . $level]['db_col'], $row) ){
				
				return array(
					'numeric'=>2,	
					'message'=>"Row is missing at least one key",
					'var'=>array('row'=>$row, 'key'=>'L' . $level, 'val'=>$row['L' . $level])
				);				
			}


			// Validate the key
			// ==================================================

			// NOTE: we skip "L0" keys. The L0 key is the data key, and can contain ANY value
			// of ANY data type (NULL, float, object, etc). Since every possible input is valid,
			// there's no point in validating it.

			if($level > 0){		    

				try {
				    
					if( $level == $ctrl['order'] ) {
					    
					    
						if( (($ctrl['end_node_format'] == 'array') || ($ctrl['end_node_format'] == 'scalarArray'))
						    && is_array($row[$this->cols['L' . $level]['db_col']]) ){


							    if( $ctrl['array_ctrl']['mode'] == 'normal' ){


								    // In 'normal' mode, array end nodes are structured as
								    // 
								    //	array( 'K1' => true,
								    //	       'K2' => true )
								    //	       
								    // ==================================================

								    $check_result = self::validateKey(array(
									    'type'=>$this->cols['L' . $level]['type'],
									    'format'=>'array',
									    'var'=>$row[$this->cols['L' . $level]['db_col']]
								    ));
								    
								    if( $check_result !== true ){

									    return array(	
											    'numeric'=>3,				    
											    'message'=>$check_result,
											    'row'=>$row, 
											    'key'=>$this->cols['L' . $level]['db_col'],
											    'var'=>$row[$this->cols['L' . $level]['db_col']]
									    );				    
								    }								    

							    }
							    else {

								    // In 'inverse' mode, array end nodes are structured as
								    // 
								    //	array( 0 => 'K1',
								    //	       1 => 'K2' )
								    //	       
								    // ==================================================						    

								    foreach( $row[$this->cols['L' . $level]['db_col']] as $key => $val ){

									    $check_result = self::validateKey(array(
										    'type'=>$this->cols['L' . $level]['type'],
										    'format'=>'scalar',
										    'var'=>$val
									    ));

									    if($check_result !== true){
										    break;
									    }
									    
								    }
								    unset($key, $val);
								    
								    if( $check_result !== true ){

									    return array(	
											    'numeric'=>4,				    
											    'message'=>$check_result,
											    'row'=>$row, 
											    'key'=>$this->cols['L' . $level]['db_col'],
											    'var'=>$row[$this->cols['L' . $level]['db_col']]
									    );				    
								    }								    

							    }

						}
						elseif( $ctrl['end_node_format'] == 'trie'){
						    
							$ctrl['trie_ctrl']['order'] = $level;
							
							$check_result = self::validateTrie(
											    $row[$this->cols['L' . $level]['db_col']],
											    $ctrl['trie_ctrl']
							);

							if( $check_result !== true ){

								return array(	
										'numeric'=>5,				    
										'message'=>$check_result,
										'row'=>$row, 
										'key'=>$this->cols['L' . $level]['db_col'],
										'var'=>$row[$this->cols['L' . $level]['db_col']]
								);				    
							}							
						}						
						
					}
					else {
					    
						$check_result = self::validateKey(array(
											'type'=>$this->cols['L' . $level]['type'],
											'format'=>'scalar',
											'var'=>$row[$this->cols['L' . $level]['db_col']]
						));	

						if( $check_result !== true ){

							return array(	
									'numeric'=>6,				    
									'message'=>$check_result,
									'row'=>$row, 
									'key'=>$this->cols['L' . $level]['db_col'],
									'var'=>$row[$this->cols['L' . $level]['db_col']]
							);				    
						}						
					}

				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Error while validating structure inside row",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}					

			}

			


		}

		return true;
	    
	}
	
	
	public function validateL1Row($row) {
	    	    
	    
		try {
		    
			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L5']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L5']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L5']['db_col'], 
						'val'=>$row[$this->cols['L5']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L4']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L4']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L4']['db_col'], 
						'val'=>$row[$this->cols['L4']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L3']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L3']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L3']['db_col'], 
						'val'=>$row[$this->cols['L3']['db_col']]
				);		    
			}	

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L2']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L2']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L2']['db_col'], 
						'val'=>$row[$this->cols['L2']['db_col']]
				);		    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L1']['type'],
								    'format'=>'both',
								    'var'=>$row[$this->cols['L1']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L1']['db_col'], 
						'val'=>$row[$this->cols['L1']['db_col']]
				);		    
			}
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		return true;
			
	}
	
	
	public function validateL2Row($row) {
	      
		try {
		    
			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L5']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L5']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L5']['db_col'], 
						'val'=>$row[$this->cols['L5']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L4']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L4']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L4']['db_col'], 
						'val'=>$row[$this->cols['L4']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L3']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L3']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L3']['db_col'], 
						'val'=>$row[$this->cols['L3']['db_col']]
				);		    
			}	

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L2']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L2']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L2']['db_col'], 
						'val'=>$row[$this->cols['L2']['db_col']]
				);		    
			}
			
			$check_result = self::validateL1Trie($row[$this->cols['L1']['db_col']]);

			if( $check_result !== true ){

				return 	$check_result;	    
			}			
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;										   

			
	}
	
	
	public function validateL3Row($row) {
	    
	    
		try {
		    
			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L5']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L5']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L5']['db_col'], 
						'val'=>$row[$this->cols['L5']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L4']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L4']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L4']['db_col'], 
						'val'=>$row[$this->cols['L4']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L3']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L3']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L3']['db_col'], 
						'val'=>$row[$this->cols['L3']['db_col']]
				);		    
			}	

			$check_result = self::validateL2Trie($row[$this->cols['L2']['db_col']]);

			if( $check_result !== true ){

				return $check_result;		    
			}
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
								   
	    
	}
	
	
	
	public function validateL4Row($row) {
	    

		try {
		    
			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L5']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L5']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L5']['db_col'], 
						'val'=>$row[$this->cols['L5']['db_col']]
				);	    
			}

			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L4']['type'],
								    'format'=>'both',
								    'var'=>$row[$this->cols['L4']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L4']['db_col'], 
						'val'=>$row[$this->cols['L4']['db_col']]
				);	    
			}
			
			$check_result = self::validateL3Trie($row[$this->cols['L3']['db_col']]);			

			if( $check_result !== true ){

				return $check_result;		    
			}
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
										      	   
	}
	
	
	
	public function validateL5Row($row) {
	    
	    
		try {
		    
			$check_result = self::validateKey( array(
								    'type'=>$this->cols['L5']['type'],
								    'format'=>'scalar',
								    'var'=>$row[$this->cols['L5']['db_col']]
			));

			if( $check_result !== true ){

				return array(	'message'=>$check_result,
						'row'=>$row, 
						'key'=>$this->cols['L5']['db_col'], 
						'val'=>$row[$this->cols['L5']['db_col']]
				);	    
			}

			$check_result = self::validateL4Trie($row[$this->cols['L4']['db_col']]);			

			if( $check_result !== true ){

				return $check_result;		    
			}
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
								        	    
	}
	
	
	/**
	 * Validates a L1 trie structure (to be technically correct, a structure with depth = 1 isn't
	 * a tree, but pretending it is makes the design pattern easier to follow)
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	=> KEY @param int/string | L1 id
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateL1Trie($data){
	    	    

		try {

			foreach( $data as $L1 => $L1_val ){
				
				$check_result = self::validateKey( array(
									    'type'=>$this->cols['L1']['type'],
									    'format'=>'scalar',
									    'var'=>$L1
				));				

				if( $check_result !== true ){

					return array('L1'=>$L1);		    
				}

			}
			unset($L1, $L1_val);
						
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->cols),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;		
				    
	}
	
	
	/**
	 * Validates a L2 trie structure 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *	    => KEY @param int/string | L1 id
	 *		=> VAL @param bool/int/float/string/array/obj $val | key value
	 *
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateL2Trie($data){
	    	    
	    
	    	if($this->order < 2){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Storage class order is too low to use this validator",
				'data'=>array("min_order"=>2, "class_order"=>$this->order),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
			
		try {

			foreach( $data as $L2 => $L1s ){
				
				$check_result = self::validateKey( array(
									    'type'=>$this->cols['L2']['type'],
									    'format'=>'both',
									    'var'=>$L2
				));				

				if( $check_result !== true ){

					return array(
						$this->cols['L2']['db_col']=>$L2
					);		    
				}

				foreach( $L1s as $L1 => $L1_val ){
					
					$check_result = self::validateKey( array(
										    'type'=>$this->cols['L1']['type'],
										    'format'=>'scalar',
										    'var'=>$L1
					));					

					if( $check_result !== true ){

						    return array(
							$this->cols['L2']['db_col']=>$L2,
							$this->cols['L1']['db_col']=>$L1					    
						    );		    
					}

				}
				unset($L1, $L1_val);
			}
			unset($L2, $L1s);
						
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->cols),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
		
				    
	}
	
	
	
	/**
	 * Validates a L3 trie structure 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	=> ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *	    => ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		=> KEY @param int/string | L1 id
	 *		    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateL3Trie($data){
	    	    
	    	
	    	if($this->order < 3){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Storage class order is too low to use this validator",
				'data'=>array("min_order"=>3, "class_order"=>$this->order),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		
		try {
		    
			foreach( $data as $L3 => $L2s ){

			    
				$check_result = self::validateKey( array(
									    'type'=>$this->cols['L3']['type'],
									    'format'=>'both',
									    'var'=>$L3
				));				

				if( $check_result !== true ){

					return array(
						$this->cols['L3']['db_col']=>$L3,						    
					);		    
				}			    

				foreach( $L2s as $L2 => $L1s ){

				    
					$check_result = self::validateKey( array(
										    'type'=>$this->cols['L2']['type'],
										    'format'=>'both',
										    'var'=>$L2
					));				

					if( $check_result !== true ){

						return array(
							$this->cols['L3']['db_col']=>$L3,						    
							$this->cols['L2']['db_col']=>$L2
						);		    
					}

					foreach( $L1s as $L1 => $L1_val ){

					    
						$check_result = self::validateKey( array(
											    'type'=>$this->cols['L1']['type'],
											    'format'=>'scalar',
											    'var'=>$L1
						));					

						if( $check_result !== true ){

							    return array(
								$this->cols['L3']['db_col']=>$L3,								
								$this->cols['L2']['db_col']=>$L2,
								$this->cols['L1']['db_col']=>$L1					    
							    );		    
						}

					}
					unset($L1, $L1_val);
				}
				unset($L2, $L1s);
			}
			unset($L3, $L2s);
			
									
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->cols),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
		
				    
	}
	
	
	/**
	 * Validates a L4 trie structure 
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | array $L4s | array of L4's in the form "L4_id"=>"L3s"	 
	 *	=> ARR @param array $L3s | array of L3's in the form "L3_id"=>"L2s"
	 *	    => ARR @param array $L2s | array of L2's in the form "L2_id"=>"L1s"
	 *		=> ARR @param array $L1s | array of L1's in the form "L1_id"=>"L1_value"
	 *		    => KEY @param int/string | L1 id
	 *			=> VAL @param bool/int/float/string/array/obj $val | key value
	 *
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateL4Trie($data){
	    	    
	    	
	    	if($this->order < 4){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Storage class order is too low to use this validator",
				'data'=>array("min_order"=>4, "class_order"=>$this->order),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}
		
		try {
		    
			foreach( $data as $L4 => $L3s ){

			    
				$check_result = self::validateKey( array(
									    'type'=>$this->cols['L4']['type'],
									    'format'=>'both',
									    'var'=>$L4
				));				

				if( $check_result !== true ){

					return array(
						$this->cols['L4']['db_col']=>$L4						    
					);		    
				}			    

				foreach( $L3s as $L3 => $L2s ){

				    
					$check_result = self::validateKey( array(
										    'type'=>$this->cols['L3']['type'],
										    'format'=>'both',
										    'var'=>$L3
					));				

					if( $check_result !== true ){

						return array(
							$this->cols['L4']['db_col']=>$L4,						    
							$this->cols['L3']['db_col']=>$L3						    
						);		    
					}			    

					foreach( $L2s as $L2 => $L1s ){

					    
						$check_result = self::validateKey( array(
											    'type'=>$this->cols['L2']['type'],
											    'format'=>'both',
											    'var'=>$L2
						));				

						if( $check_result !== true ){

							return array(
								$this->cols['L4']['db_col']=>$L4,							    
								$this->cols['L3']['db_col']=>$L3,						    
								$this->cols['L2']['db_col']=>$L2
							);		    
						}

						foreach( $L1s as $L1 => $L1_val ){

						    
							$check_result = self::validateKey( array(
												    'type'=>$this->cols['L1']['type'],
												    'format'=>'scalar',
												    'var'=>$L1
							));					

							if( $check_result !== true ){

								    return array(
									$this->cols['L4']['db_col']=>$L4,									
									$this->cols['L3']['db_col']=>$L3,								
									$this->cols['L2']['db_col']=>$L2,
									$this->cols['L1']['db_col']=>$L1					    
								    );		    
							}

						}
						unset($L1, $L1_val);
					}
					unset($L2, $L1s);
				}
				unset($L3, $L2s);
			}
			unset($L4, $L3s);			
									
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->cols),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return true;
		
				    
	}
	
	
	/**
	 * Validates a L5 trie structure 
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
	 *
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateL5Trie($data){
	    
	    
	    	if($this->order < 5){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Storage class order is too low to use this validator",
				'data'=>array("min_order"=>5, "class_order"=>$this->order),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}	    	    
	    		
		try {
		    
			foreach( $data as $L5 => $L4s){
			    
				
				$check_result = self::validateKey( array(
									    'type'=>$this->cols['L5']['type'],
									    'format'=>'both',
									    'var'=>$L5
				));				

				if( $check_result !== true ){

					return array(
						$this->cols['L5']['db_col']=>$L5						    
					);		    
				}				

				foreach( $L4s as $L4 => $L3s ){


					$check_result = self::validateKey( array(
										    'type'=>$this->cols['L4']['type'],
										    'format'=>'both',
										    'var'=>$L4
					));				

					if( $check_result !== true ){

						return array(
							$this->cols['L5']['db_col']=>$L5,						    
							$this->cols['L4']['db_col']=>$L4						    
						);		    
					}			    

					foreach( $L3s as $L3 => $L2s ){


						$check_result = self::validateKey( array(
											    'type'=>$this->cols['L3']['type'],
											    'format'=>'both',
											    'var'=>$L3
						));				

						if( $check_result !== true ){

							return array(
								$this->cols['L5']['db_col']=>$L5,							    
								$this->cols['L4']['db_col']=>$L4,						    
								$this->cols['L3']['db_col']=>$L3						    
							);		    
						}			    

						foreach( $L2s as $L2 => $L1s ){


							$check_result = self::validateKey( array(
												    'type'=>$this->cols['L2']['type'],
												    'format'=>'both',
												    'var'=>$L2
							));				

							if( $check_result !== true ){

								return array(
									$this->cols['L5']['db_col']=>$L5,								    
									$this->cols['L4']['db_col']=>$L4,							    
									$this->cols['L3']['db_col']=>$L3,						    
									$this->cols['L2']['db_col']=>$L2
								);		    
							}

							foreach( $L1s as $L1 => $L1_val ){


								$check_result = self::validateKey( array(
													    'type'=>$this->cols['L1']['type'],
													    'format'=>'scalar',
													    'var'=>$L1
								));					

								if( $check_result !== true ){

									    return array(
										$this->cols['L5']['db_col']=>$L5,										
										$this->cols['L4']['db_col']=>$L4,									
										$this->cols['L3']['db_col']=>$L3,								
										$this->cols['L2']['db_col']=>$L2,
										$this->cols['L1']['db_col']=>$L1					    
									    );		    
								}

							}
							unset($L1, $L1_val);
						}
						unset($L2, $L1s);
					}
					unset($L3, $L2s);
				}
				unset($L4, $L3s);
			}
			unset($L5, $L4s);
						
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->cols),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		return true;		
						    
	}

	
	/**
	 * Validates a L5 trie structure 
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
	 *	=> VAL @param int $order | Order of trie
	 *	=> VAL @param string $mode | Operation mode 'control' | 'data'
	 *	=> VAL @param bool $allow_wildcard | Allow the wildcard character to be used	 
	 * 
	 * @return bool/array | Exception on failure, (bool)true on success, (array)data_array on failure.
	 */

	public function validateTrie($data, $ctrl=null){
	    
	    
		$ctrl_default = array(
			'order'=>$this->order,
			'mode'=>'control',
			'allow_wildcard'=>false,
			'clip_order'=>false		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		
	    	if($ctrl['order'] > $this->order){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Specified order is too high for this storage class",
				'data'=>array("order"=>$ctrl['order'], "class_order"=>$this->order),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}	
		
	    	if( ($ctrl['mode'] == 'data') && !is_int($ctrl['clip_order']) ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"The 'clip_order' parameter must be set when operating in 'data' mode",
				'data'=>array('ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}		
	    		
		try {
		    
			if( $ctrl['order'] > 0 ){
			    
			    
				if( is_array($data) && !empty($data) ){
						
				    
					foreach( $data as $parent_id => $children ){


						$check_result = self::validateKey(array(
											'type'=>$this->cols['L' . $ctrl['order']]['type'],
											'format'=>'both',
											'var'=>$parent_id
						));				

						if( $check_result !== true ){

							return array(	'numeric'=>1,
									'message'=>$check_result,
									'key'=>'L' . $ctrl['order'], 
									'val'=>$parent_id
							);			    
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

								$trace = array_merge($child_result['trace'], array( 'L' . $ctrl['order'] => $parent_id) );
							}
							else {

							    $trace = array( 'L' . $ctrl['order'] => $parent_id);
							}

							return array(	
									'numeric'=>2,
									'message'=>$child_result['message'],
									'key'=>'L' . $ctrl['order'], 
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
									'key'=>'L' . $ctrl['order'], 
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
										'key'=>'L' . $ctrl['order'], 
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
									'key'=>'L' . $ctrl['order'], 
									'val'=>$parent_id,
							);
						}

					}				    				    
				    
					
				} // ENDOF: if( is_array($data) && !empty($data) )
			
			}
			else {	
				// We're at L0 in a walk
				return true;
			}
			
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in validator",
				'data'=>array("columns"=>$this->cols),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		
			
		}
		
		if( empty($args['format'])  
		    || (($args['format'] != 'scalar') && ($args['format'] != 'array') && ($args['format'] != 'both')) 
		){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Format must be either 'scalar', 'array', or 'both'",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
			    
				case "both" : {
				    				    
					if( is_array($args['var']) ){
					    
						foreach( $args['var'] as $key => $val){


							if( !is_int($val) || empty($val) ){

								$message_str =  "Each key in the array must be an int ";	
								$message_str .= "(not just a string that converts to an int)";	
						
								$result = array(
									'numeric'=>5,									    
									'message'=>$message_str,
									'var'=>$args['var'],
									'faulting_key'=>$key							    
								);

								break;
							}					    
						}
						unset($key, $val);					    
					}
					elseif( !is_int($args['var']) || empty($args['var']) ){
					    
						$message_str =  "Must be a scalar int, or an array where each key is ";	
						$message_str .= "an int (not just a string that converts to an int)";	
								
						$result = array(	
						    	'numeric'=>6,	
							'message'=>$message_str,
							'var'=>$args['var']							    
						);	
						
					}					
					
				    
				} break;				
				
			
			}
									
			
		}		
		elseif( $args['type'] == 'string' ){
		    		    
		    
			switch($args['format']){
								
				case "scalar" : {

					if( !is_string($args['var']) || empty($args['var']) ){
					    
						$result = array(
						    	'numeric'=>7,	
							'message'=>"Must be a non-null string.",
							'var'=>$args['var']
						);					    					    
					}
					elseif( is_numeric($args['var']) ){
					    
						$message_str =  "Cannot be a string that PHP will auto-convert to an int when ";	
						$message_str .= "used as an array key. For example, (string)'7'. ";
						
						$result = array(
						    	'numeric'=>8,	
							'message'=>$message_str,
							'var'=>$args['var']
						);					    					    
					}					
					

				} break;

				case "array" : {
				    
					if( !is_array($args['var']) ){
					    
						$result = array(
						    	'numeric'=>9,	
							'message'=>"Must be an array, where each key is a string.",
							'var'=>$args['var']						    
						);
						
					}
					else {

						foreach( $args['var'] as $key => $val){


							if( !is_string($val) || empty($val) ){

								$result = array(
								    	'numeric'=>10,	
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
									'numeric'=>11,	
									'message'=>$message_str,
									'var'=>$args['var'],
									'faulting_key'=>$key								    
								);					    					    
							}
						}	
						unset($key, $val);
					}				    				    
				    
				} break;
			    
				case "both" : {
				    				    
					if( is_array($args['var']) ){
					    
						foreach( $args['var'] as $key => $val){


							if( !is_string($val) || empty($val) ){

								$result = array(
									'numeric'=>12,	
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
									'numeric'=>13,	
									'message'=>$message_str,
									'var'=>$args['var'],
									'faulting_key'=>$key								    
								);					    					    
							}
						}
						unset($key, $val);
						
					}
					elseif( !is_string($args['var']) || empty($args['var']) ){
					    
						$result = array(	
						    	'numeric'=>14,	
							'message'=>"Must be a non-null string, or an array where each key is a string.",
							'var'=>$args['var']							    
						);	
						
					}
					elseif( is_numeric($args['var']) ){

						$message_str =  "Cannot be a string that PHP will auto-convert to an int when ";	
						$message_str .= "used as an array key. For example, (string)'7'. ";

						$result = array(
							'numeric'=>15,	
							'message'=>$message_str,
							'var'=>$args['var']							    
						);					    					    
					}					
					
				    
				} break;
								
			
			}
			
		}	
		
		return $result;	
			    
	}	
	
	
    
} // End of class FOX_dataStore_validator


?>