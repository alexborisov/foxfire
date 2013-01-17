<?php

/**
 * FOXFIRE USER CLASS
 * This singleton class manages all user data operations
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage RBAC
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

final class FOX_user {


	var $data_class;		    // User datastore class instance
	var $keyring_class;		    // User keyring class instance
	var $bp_class;			    // BuddyPress abstraction class instance

	var $loggedin_user;		    // Logged-in user data object
	var $displayed_user;		    // Displayed user data object


	// ============================================================================================================ //


	function FOX_user($args=null) {

		$this->__construct($args);
	}

	function __construct($args=null) {

		// Handle dependency-injection for unit tests
		if($args){

			$this->data_class = &$args['data_class'];
			$this->keyring_class = &$args['keyring_class'];
			$this->bp_class = &$args['bp_class'];
		}
		else {
			global $fox;
			$this->data_class = new FOX_uData();
			$this->keyring_class = new FOX_uKeyRing();
			$this->bp_class = new FOX_bp();
		}

		$this->loggedin_user = new stdClass();
		$this->displayed_user = new stdClass();
		
		$this->loggedin_user->id = $this->bp_class->getLoggedInUserID();
		$this->displayed_user->id = $this->bp_class->getDisplayedUserID();

	}



} // End of class FOX_user

?>