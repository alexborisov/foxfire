<?php

/**
 * FOXFIRE TEST DATA LOADER FUNCTIONS
 * These functions add and clear users and media items from the FoxFire install and the host BuddyPress
 * and WordPress installations. They are used to reset the installation to a known state for UI testing.
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

class FOX_testData {


	private function  __construct() {}


	/**
	 * Clears ALL users from the site, except the site admin
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function clearUsers() {

	}


	/**
	 * Clears ALL uploaded content from the site, except items owned by the site admin
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function clearItems() {

	}


	/**
	 * Loads user data into the system from a manifest file
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $path | Path to manifest file
	 */

	public function loadUsers($path) {

	}


	/**
	 * Loads media items into the system from a manifest file
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $path | Path to manifest file
	 */

	public function loadItems($path) {

	}








} // End of class FOX_testData

?>