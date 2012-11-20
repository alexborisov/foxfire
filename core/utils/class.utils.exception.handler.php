<?php

/**
 * BP-MEDIA EXCEPTION HANDLER
 * Global singleton that catches and logs all uncaught exceptions
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Util
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class BPM_exceptionHandler {


	var $buffer;			    // Error data


	// ============================================================================================================ //


	public function  __construct() {

		$this->buffer = array();
	}


	public function add($data){

		$this->buffer[] = $data;
	}


} // End of class BPM_exceptionHandler

?>