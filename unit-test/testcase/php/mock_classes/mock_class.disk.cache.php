<?php

/**
 * BP-MEDIA UNIT TESTS - MOCK DISK CACHE CLASS
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
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