<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - EXTENDED WELL KNOWN TEXT
 * Reads and writes data to Extended Well Known Text format
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

class FOX_ewkt extends FOX_wkt {
  
	/**
	* Serialize geometries into an EWKT string.
	*
	* @param Geometry $geometry
	*
	* @return string The Extended-WKT string representation of the input geometries
	*/
	public function write(FOX_geometry $geometry) {
	    
		$srid = $geometry->SRID();
		$wkt = '';
		
		if($srid){
		    
			$wkt = 'SRID=' . $srid . ';';
			$wkt .= $geometry->out('wkt');
			
			return $wkt;
		}
		else {
			return $geometry->out('wkt');
		}
		
	}
	
}

?>