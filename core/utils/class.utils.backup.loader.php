<?php

/**
 * FOXFIRE BACKUP LOADER FUNCTIONS
 * These functions load content from FoxFire backup containers
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

class FOX_backupLoader {


	private function  __construct() {}


	/**
	 * Imports a backup container file into the system
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $path | Path to container file
	 * @param array $ctrl | Control parameters
	 *	=> VAL @param bool $allow_remote | True to allow container files to be loaded from a remote server
	 */

	public function importContainer($path, $ctrl=null) {

	}


	/**
	 * Unzips a container file into FoxFire's temp folder
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $path | Path to archive file
	 */

	public function unzipContainer($path) {

	}


	/**
	 * Processes a user manifest file
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $path | Path to manifest file
	 */

	public function processUserManifest($path) {

	}


	/**
	 * Processes an album manifest file
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $path | Path to manifest file
	 */

	public function processAlbumManifest($path) {

	}


	/**
	 * Imports an album
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $data | Album data
	 */

	public function importAlbum($data) {

	}


	/**
	 * Imports a media item
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $data | Media item data
	 */

	public function importItem($data) {

	}

	


} // End of class FOX_backupLoader

?>