<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - EXTENDED WELL KNOWN BINARY
 * Reads and writes data to Extended Well Known Binary format
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

class FOX_ewkb extends FOX_wkb {
  
    
	/**
	* Read WKB binary string into geometry objects
	*
	* @param string $wkb An Extended-WKB binary string
	*
	* @return Geometry
	*/
	public function read($wkb, $is_hex_string=false){
	    
		if($is_hex_string){
		    
			$wkb = pack('H*',$wkb);
		}

		// Open the wkb up in memory so we can examine the SRID
		$mem = fopen('php://memory', 'r+');
		
		fwrite($mem, $wkb);
		fseek($mem, 0);
		
		$base_info = unpack("corder/ctype/cz/cm/cs", fread($mem, 5));
		
		if($base_info['s']){
		    
			$srid = current(unpack("Lsrid", fread($mem, 4)));
		}
		else {
			$srid = null;
		}
		
		fclose($mem);

		// Run the wkb through the normal WKB reader to get the geometry
		$wkb_reader = new FOX_wkb();
		$geom = $wkb_reader->read($wkb);

		// If there is an SRID, add it to the geometry
		if($srid){
		    
			$geom->setSRID($srid);
		}

		return $geom;
		
	}

	/**
	* Serialize geometries into an EWKB binary string.
	*
	* @param Geometry $geometry
	*
	* @return string The Extended-WKB binary string representation of the input geometries
	*/
	public function write(FOX_geometry $geometry, $write_as_hex=false){
	    
		// NOTE: Isn't this supposed to write the SRID to the binary?
	    
		// We always write into NDR (little endian)
		$wkb = pack('c',1);		
		$type = strtolower($geometry->getGeomType());
		
		switch($type){

			case "point" : {
				$wkb .= pack('L',1);
				$wkb .= $this->writePoint($geometry);
			} break;
		    
			case "linestring" : {
				$wkb .= pack('L',2);
				$wkb .= $this->writeLineString($geometry);
			} break;
		    
			case "polygon" : {
				$wkb .= pack('L',3);
				$wkb .= $this->writePolygon($geometry);
			} break;
		    
			case "multipoint" : {
				$wkb .= pack('L',4);
				$wkb .= $this->writeMulti($geometry);
			} break;	

			case "multilinestring" : {
				$wkb .= pack('L',5);
				$wkb .= $this->writeMulti($geometry);
			} break;
		    
			case "multipolygon" : {
				$wkb .= pack('L',6);
				$wkb .= $this->writeMulti($geometry);
			} break;	

			case "geometrycollection" : {
				$wkb .= pack('L',7);
				$wkb .= $this->writeMulti($geometry);
			} break;		    
		    
			default :   {
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Unknown geometry type",
					'data'=>$geometry,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}
			
		}

		if($write_as_hex){
		    
			$unpacked = unpack('H*',$wkb);
			return $unpacked[1];
		}
		else {
			return $wkb;
		}
		
	}
	

}

?>