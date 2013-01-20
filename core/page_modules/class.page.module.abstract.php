<?php

/**
 * FOXFIRE PAGE MODULE BASE CLASS
 * Provides common functions used in page modules
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Page Modules
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

abstract class FOX_pageModule_base {


	/**
	 * Returns the unique ID for the page module as assigned by the plugin core.
	 *
	 * @version 1.0
	 * @since 1.0
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

} // End of abstract class_FOX_pageModule_base

?>