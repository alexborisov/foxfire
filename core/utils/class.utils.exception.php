<?php

/**
 * FOXFIRE EXCEPTION OBJECT
 * Generated by FoxFire functions when an exception is thrown
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Util
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_exception extends Exception {

    
	var $data;			    // Error data


	// ============================================================================================================ //

	
	public function  __construct($data) {
	    
		$this->data = $data;	    
	}
	
	public function dump($depth=null){
	    	    
	    
		if($depth == 1){

			FOX_debug::dump($this->data);
		}
		else {

			FOX_debug::dump( FOX_debug::formatError_print($this->data, $depth));
		}	
	    
	}
	
	public function dumpString($args=null){
	    	    	    
	    
		if(is_array($args)){
		
			return self::dumpString_iterator($args['depth'], $this, $args);
		}
		else {
		    
			return self::dumpString_iterator($args, $this);
		}
				    
	}
	
	
	public function dumpString_iterator($depth, $obj, $ctrl=null){
	    
		$pos_1 = strrpos($obj->data['file'], "/");	// Linux
		$pos_2 = strrpos($obj->data['file'], "\\");	// Windows
		
		$offset = max($pos_1, $pos_2);
		$length = strlen($obj->data['file']);
		
		$file = substr($obj->data['file'], $offset + 1, ($length - $offset));
	    
		$result .= "\nCODE: " . $obj->data['numeric'] ."\n";
		$result .= "TEXT: " . $obj->data['text'] ."\n";
		$result .= "FILE: " . $file ."\n";
		$result .= "LINE: " . $obj->data['line'] ."\n";
		
		if( ($ctrl['data'] == true) ){
		    
			$result .= "DATA: " . FOX_debug::dumpToString($obj->data['data']);
		}
		    
		if( is_object($obj->data['child']) && ($depth > 0) ){
		    
			$result .= self::dumpString_iterator($depth-1, $obj->data['child'], $ctrl);			
		}

		return $result;
						    
	}	


} // End of class FOX_exception

?>