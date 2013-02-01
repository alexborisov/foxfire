<?php

/**
 * FOXFIRE LINESTRING CLASS
 * A collection of Points representing a line. A line can have more than one segment.
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

class FOX_multiPoint extends FOX_collection {
    
    
	protected $geom_type = 'MultiPoint';
	
	
	// ============================================================================================================ //
	

	public function numPoints(){
	    
		return $this->numGeometries();
	}

	public function isSimple(){
	    
		return true;
	}

	// Not valid for this geometry type
	// --------------------------------
	public function explode(){ 
	    
		return null; 		
	}
  
}

?>