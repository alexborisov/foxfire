<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - WELL KNOWN BINARY
 * Reads and writes data to Well Known Binary format
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

class FOX_wkb extends FOX_geoAdapter {

    
	private $dimension = 2;
	private $z = false;
	private $m = false;

	// ============================================================================================================ //
	
	
	/**
	* Read WKB into geometry objects
	*
	* @param string $wkb
	*   Well-known-binary string
	* @param bool $is_hex_string
	*   If this is a hexedecimal string that is in need of packing
	*
	* @return Geometry
	*/
	public function read($wkb, $is_hex_string=false){
	    
		if($is_hex_string){
			$wkb = pack('H*',$wkb);
		}

		if(empty($wkb)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"'Cannot read empty WKB geometry",
				'data'=>array('wkb'=>$wkb, 'is_hex_string'=>$is_hex_string),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    
		}

		$mem = fopen('php://memory', 'r+');
		
		fwrite($mem, $wkb);
		fseek($mem, 0);

		$geometry = $this->getGeometry($mem);
		fclose($mem);
		
		return $geometry;
		
	}

	public function getGeometry(&$mem){
	    
		$base_info = unpack("corder/ctype/cz/cm/cs", fread($mem, 5));
		
		if($base_info['order'] !== 1){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Only NDR (little endian) SKB format is currently supported",
				'data'=>array('mem'=>$mem),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    
		}

		if($base_info['z']){
		    
			$this->dimension++;
			$this->z = true;
		}
		
		if($base_info['m']){
		    
			$this->dimension++;
			$this->m = true;
		}

		// If there is SRID information, ignore it - use EWKB Adapter to get SRID support
		if($base_info['s']){
		    
			fread($mem, 4);
		}
		
		switch($base_info['type']){

			case 1 : {
				return $this->getPoint($mem);
			} break;
		    
			case 2 : {
				return $this->getLinstring($mem);
			} break;
		    
			case 3 : {
				return $this->getPolygon($mem);
			} break;
		    
			case 4 : {
				return $this->getMulti($mem,'point');
			} break;
		    
			case 5 : {
				return $this->getMulti($mem,'line');
			} break;		    
		    
			case 6 : {
				return $this->getMulti($mem,'polygon');
			} break;
		    
			case 7 : {
				return $this->getMulti($mem,'geometry');
			} break;		    
		    
			default :   {
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Unknown geometry type",
					'data'=>$base_info,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}
		    
		}
		
		
	}

	public function getPoint(&$mem){
	    
		$point_coords = unpack("d*", fread($mem,$this->dimension*8));
		
		return new FOX_point($point_coords[1],$point_coords[2]);
	}

	public function getLinstring(&$mem){
	    
		// Get the number of points expected in this string out of the first 4 bytes
		$line_length = unpack('L',fread($mem,4));

		// Return an empty linestring if there is no line-length
		if(!$line_length[1]){
		    
			return new FOX_lineString();
		}

		// Read the nubmer of points x2 (each point is two coords) into decimal-floats
		$line_coords = unpack('d*', fread($mem,$line_length[1]*$this->dimension*8));

		// We have our coords, build up the linestring
		$components = array();
		$i = 1;
		$num_coords = count($line_coords);
		
		while($i <= $num_coords){
		    
			$components[] = new FOX_point($line_coords[$i],$line_coords[$i+1]);
			$i += 2;
		}
		
		return new FOX_lineString($components);
		
	}

	
	public function getPolygon(&$mem){
	    
		// Get the number of linestring expected in this poly out of the first 4 bytes
		$poly_length = unpack('L',fread($mem,4));

		$components = array();
		$i = 1;
		
		while($i <= $poly_length[1]){
		    
			$components[] = $this->getLinstring($mem);
			$i++;
		}
		
		return new FOX_polygon($components);
		
	}

	
	public function getMulti(&$mem, $type){
	    
		// Get the number of items expected in this multi out of the first 4 bytes
		$multi_length = unpack('L',fread($mem,4));

		$components = array();
		$i = 1;
		
		while($i <= $multi_length[1]){
		    
			$components[] = $this->getGeometry($mem);
			$i++;
		}		
		
		$type = strtolower($geometry->geometryType());
		
		switch($type){

			case "point" : {
				return new FOX_multiPoint($components);
			} break;
		    
			case "line" : {
				return new FOX_multiLineString($components);
			} break;
		    
			case 'polygon': {
				return new FOX_multiPolygon($components);
			} break;
		    
			case 'geometry': {
				return new FOX_geometryCollection($components);
			} break;
		    
			default :   {
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Unknown geometry type",
					'data'=>array('multi_length'=>$multi_length, 'type'=>$type),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}
		    
		}		
		
		
	}

	
	/**
	* Serialize geometries into WKB string.
	*
	* @param Geometry $geometry
	*
	* @return string The WKB string representation of the input geometries
	*/
	public function write(FOX_geometry $geometry, $write_as_hex=false){
	    
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

	public function writePoint($point){
	    
		// Set the coords
		$wkb = pack('dd',$point->x(), $point->y());

		return $wkb;
	}

	public function writeLineString($line){
	    
		// Set the number of points in this line
		$wkb = pack('L',$line->numPoints());

		// Set the coords
		foreach( $line->getComponents() as $point ){
		    
			$wkb .= pack('dd',$point->x(), $point->y());
		}

		return $wkb;
	}

	public function writePolygon($poly){
	    
		// Set the number of lines in this poly
		$wkb = pack('L',$poly->numGeometries());

		// Write the lines
		foreach( $poly->getComponents() as $line ){
		    
			$wkb .= $this->writeLineString($line);
		}

		return $wkb;
	}

	public function writeMulti($geometry){
	    
		// Set the number of components
		$wkb = pack('L',$geometry->numGeometries());

		// Write the components
		foreach( $geometry->getComponents() as $component ){
		    
			$wkb .= $this->write($component);
		}

		return $wkb;
	}

}

?>