<?php

/**
 * RADIENT PAGE MODULE BASE CLASS
 * Provides common functions used in page modules
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
 * @subpackage Page Modules
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

abstract class RAD_pageModule_base {


	/**
	 * Returns the unique ID for the page module as assigned by the plugin core.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return int | Exception on failure. Page module ID on success.
	 */
	public function getID(){

		global $rad;

		$slug = $this->getSlug();

		try{
			$module_data = $rad->pageModules->getBySlug($slug);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				    'numeric'=>1,
				    'text'=>"Error fetching module data",
				    'data'=> array('slug' => $slug),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
			));
		}

		if( !is_int($module_data["module_id"]) || ($module_data["module_id"] < 1) ){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"Module singleton returned invalid module_id",
				    'data'=>array('slug'=>$slug, 'module_id'=>$module_data["module_id"]),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
			));
		}

		return $module_data["module_id"];

	}

} // End of abstract class_RAD_pageModule_base

?>