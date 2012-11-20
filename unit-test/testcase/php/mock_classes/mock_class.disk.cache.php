<?php

/**
 * FOXFIRE UNIT TESTS - MOCK DISK CACHE CLASS
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


class mock_dCache {


	public function mock_dCache() {}


	public function deleteAll($owner_id){

		return true;
	}

	public function deleteItem($owner_id, $master, &$error=null){

		return true;
	}


} // End class mock_dCache


?>