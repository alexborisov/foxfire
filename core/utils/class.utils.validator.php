<?php

/**
 * FOXFIRE VALIDATOR HELPER CLASS
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

class FOX_validator{
    
	// ^F not sure how in-depth the class has to be.
	//  Should i be testing for bool, int, small int, date time and so on
	// also should i add remove if not valid 
	const BOOL_STRICT = 1;
	const INT_STRICT = 2; // 32 bit interger
	const INT_REMOVE = 2; // don't throw exception just remove variable
	const FLOAT_STRICT = 3;
	const STRING_STRICT = 4;

	const INT_CAST = 5;    
	const STRING_CAST = 6;
	
	
	public function __construct(){}
	
	/**
	 * Validates an array of values
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $args | control args
	 *	=> ARR @param int '' | Individual row array
	 *		=> VAL @param string $name | name of the variable
	 *		=> VAL @param string $format | acceptable formats ("scalar" | "array" | "both")
	 *		=> VAL @param string $var | variable to test
	 *		=> VAL @param int $type | variable type using constants above.
	 *		=> VAL @param array $column | struct column 
	 * 
	 * @return bool/array | Exception on failure, (bool)true on pass, data array on fail.
	 */	
	
	public function validate($args) {
	    
		// Check array is not empty 
		if( empty($args)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Args must be set.",
				'data'=>$arg,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		    
		}
	    
		// if array is associate wrap in sequential
		if( $this->isAssoc($args)){
		    
			$args= array($args);
		}
		
		
		foreach($args as $arg){

			// Check name has been set
			if( empty($arg['name']) || !is_string($arg['name']) ){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Name must be a 'string'",
					'data'=>array('name'=>$arg['name']),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					
			}			
			
			// Check format has been set
			if( empty($arg['format'])  || in_array($arg['format'], array("scalar", "array") ) ){
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Format must be either 'scalar' or 'array'",
					'data'=>$args,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					
			}		    
		    
		    
		    
			// Check format of variable
			if ($arg['format'] == 'scalar' && is_array($arg['var'])){
			    
				throw new FOX_exception( array(
				    'numeric'=>1,
				    'text'=>"array parameter passed when scalar required exception",
				    'data'=>array($$arg['name']=>$arg['var']),
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
				));
			    
			} elseif( !is_array($arg['var'])){
			    
				throw new FOX_exception( array(
				    'numeric'=>1,
				    'text'=>"scalar parameter passed when array required exception",
				    'data'=>array($$arg['name']=>$arg['var']),
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
				));			    
			    
			}

			// If type not set generate it using column
			if( !isset($arg['type']) && isset($arg['column'])){
			    
				$arg['type'] = $this->processCol($arg['column']);
			}
			
			// Check type has been set
			if( empty($arg['type']) || is_int($arg['type']) ){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Type must be an int",
					'data'=>array('type'=>$arg['type']),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));					
			}			
			// ^F not sure if should test for null
			// could be used if can be null and if int must be small int
//			// Test if variable is null and is allowed to be null
//			if( is_null($arg['var']) && in_array($arg['type'], array(INT_STRICT, STRING_STRICT) ) ){
//			    
//				throw new FOX_exception( array(
//				    'numeric'=>1,
//				    'text'=>"scalar parameter passed when array required exception",
//				    'data'=>array($$arg['name']=>$arg['var']),
//				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
//				    'child'=>null
//				));			    
//			}
			
			switch($arg['type']){
			    
				case FOX_validator::INT_STRICT:
					if( $arg['format']== "scalar"){

						$arg['var'] = array($arg['var']);
					}
					
					foreach($arg['var'] as $var){

						if( !is_int($var)){
							throw new FOX_exception( array(
								'numeric'=>1,
								'text'=>"var must be an int",
								'data'=>array('var'=>$arg['var']),
								'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
								'child'=>null
							));						
						}			    
					}
			    
			    
			}

		}
	    
	    
	}
	
	
	/**
	 * Generate type using struct column
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $col | struct column
	 * 
	 *	=> VAL @param string $php 
	 *	=> VAL @param int $width 
	 *	=> VAL @param string $flags 
	 * 
	 * @return bool/array | Exception on failure, (bool)true on pass, data array on fail.
	 */	
	
	public function processCol($col) {
	    
	    
		switch($col['php']){
		
			case "int":
				
				if( strpos($col['flags'], "NOT NULL")){
					return FOX_validator::INT_STRICT;
				}
				return FOX_validator::INT_CAST;
			case "string":
			    
				if( strpos($col['flags'], "NOT NULL")){
					return FOX_validator::STRING_STRICT;
				}
				return FOX_validator::STRING_CAST;			    
			    
			    break;
			default:
				throw new FOX_exception( array(
				    'numeric'=>1,
				    'text'=>"invalid php column entry in struct exception",
				    'data'=>array($$arg['name']=>$arg['var']),
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
				));
			    break;
			    
		}
	    
	}	
    
	function isAssoc($arr){
	    
		return array_keys($arr) !== range(0, count($arr) - 1);
		
	}    
}
?>