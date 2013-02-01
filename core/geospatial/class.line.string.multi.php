<?php

/**
 * FOXFIRE MULTI LINESTRING CLASS
 * Provides multiple linestring object functionality
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

/**
 * MultiLineString: A collection of LineStrings
 */
class FOX_multiLineString extends FOX_collection {
    
    
	protected $geom_type = 'MultiLineString';
	
	// ============================================================================================================ //    

	// MultiLineString is closed if all it's components are closed
	
	public function isClosed() {
	    
		foreach( $this->components as $line ){
		    
			if( !$line->isClosed() ){
			    
				return false;
			}
		}
		
		return true;
	}

}

?>