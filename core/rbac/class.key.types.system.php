<?php

/**
 * FOXFIRE SYSTEM KEYS
 * Maps WordPress roles to FoxFire system keys, and adds parametric and conditional keys.
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

class FOX_sysKey {


	function super_admin(){

	}

	function site_admin(){

	}

	function group_admin(){

	}

	/**
	 * Returns all keys currently stored in the db
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool/array | False on failure. Array of keys on success.
	 */

	public function getAll() {

		$result = array(
				"86"=>"Paid Users",
				"101"=>"IP-Ban",
				"7"=>"Group Admin",
				"44"=>"Group Member",
				"88"=>"Group Viewer",
				"41"=>"Everyone"
		);

		return $result;

	}




} // End of class FOX_sysKey

?>