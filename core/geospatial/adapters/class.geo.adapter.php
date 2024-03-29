<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER BASE CLASS
 * Provides base geo adapter methods for reading and writing to and from Geomtry objects
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Geospatial
 * @license GPL v2.0
 * @author Originally based on the geoPHP library
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

abstract class FOX_geoAdapter {
    
	/**
	* Read input and return a Geomtry or GeometryCollection
	* 
	* @return Geometry|GeometryCollection
	*/
	abstract public function read($input);

	/**
	* Write out a Geomtry or GeometryCollection in the adapter's format
	* 
	* @return mixed
	*/
	abstract public function write(FOX_geometry $geometry);
  
}

?>