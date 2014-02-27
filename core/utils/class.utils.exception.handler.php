<?php

/**
 * FOXFIRE EXCEPTION HANDLER
 * Global singleton that catches and logs all uncaught exceptions
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

class FOX_exceptionHandler {


	var $buffer;			    // Error data


	// ============================================================================================================ //


	public function  __construct() {

		$this->buffer = array();
	}


	public function add($data){

		$this->buffer[] = $data;
	}


} // End of class FOX_exceptionHandler

?>