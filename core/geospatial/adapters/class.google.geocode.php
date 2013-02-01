<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - GOOGLE
 * Reads and writes data to Google's geocoding API
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

class FOX_googleGeocode extends FOX_geoAdapter {

	/**
	* Read an address string or array geometry objects
	*
	* @param string - Address to geocode
	* @param string - Type of Geometry to return. Can either be 'points' or 'bounds' (polygon)
	* @param Geometry|bounds-array - Limit the search area to within this region. For example
	*                                by default geocoding "Cairo" will return the location of Cairo Egypt.
	*                                If you pass a polygon of illinois, it will return Cairo IL.
	* @param return_multiple - Return all results in a multipoint or multipolygon
	* @return Geometry|GeometryCollection
	*/
	public function read($address, $return_type='point', $bounds=false, $return_multiple=false){
	    
	    
		if( is_array($address) ){
		    
			$address = join(',', $address);
		}

		if( gettype($bounds) == 'object' ){
		    
			$bounds = $bounds->getBBox();
		}		
		elseif( gettype($bounds) == 'array' ){
		    
			$bounds_string = '&bounds='.$bounds['miny'].','.$bounds['minx'].'|'.$bounds['maxy'].','.$bounds['maxx'];
		}
		else {
			$bounds_string = '';
		}

		$url = "http://maps.googleapis.com/maps/api/geocode/json";
		$url .= '?address='. urlencode($address);
		$url .= $bounds_string;
		$url .= '&sensor=false';
		
		$this->result = json_decode(@file_get_contents($url));

		if($this->result->status == 'OK'){
		    
			if($return_multiple == false){

				if($return_type == 'point'){

					return $this->getPoint();
				}

				if( ($return_type == 'bounds') || ($return_type == 'polygon') ){

					return $this->getPolygon();
				}
			}

			if($return_multiple == true){

				if($return_type == 'point'){

					$points = array();

					foreach( $this->result->results as $delta => $item ){

						$points[] = $this->getPoint($delta);
					}

					return new FOX_multiPoint($points);
				}

				if( ($return_type == 'bounds') || ($return_type == 'polygon') ){

					$polygons = array();

					foreach( $this->result->results as $delta => $item ){

						$polygons[] = $this->getPolygon($delta);
					}

					return new FOX_multiPolygon($polygons);

				}
			}
		    
		}
		else {		    
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in Google forward-geocoder API",
				'data'=>array(	'address'=>$address, 'return_type'=>$return_type,
						'bounds'=>$bounds, 'return_multiple'=>$return_multiple, 
						'result'=>$this->result),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));					
		}
		
		
	}

	/**
	* Serialize geometries into a WKT string.
	*
	* @param Geometry $geometry
	* @param string $return_type Should be either 'string' or 'array'
	*
	* @return string Does a reverse geocode of the geometry
	*/
	public function write(FOX_geometry $geometry, $return_type='string'){
	    
	    
		$centroid = $geometry->getCentroid();
		
		$lat = $centroid->getY();
		$lon = $centroid->getX();

		$url = "http://maps.googleapis.com/maps/api/geocode/json";
		$url .= '?latlng='.$lat.','.$lon;
		$url .= '&sensor=false';
		
		$this->result = json_decode(@file_get_contents($url));

		if($this->result->status == 'OK'){
		    
			if($return_type == 'string'){
			    
				return $this->result->results[0]->formatted_address;
			}			
			elseif($return_type == 'array'){
			    
				return $this->result->results[0]->address_components;
			}
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in Google reverse-geocoder API",
				'data'=>array(	'geometry'=>$geometry, 'return_type'=>$return_type,
						'result'=>$this->result),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		
	}

	public function getPoint($delta=0){
	    
		$lat = $this->result->results[$delta]->geometry->location->lat;
		$lon = $this->result->results[$delta]->geometry->location->lng;
		
		return new FOX_point($lon, $lat);
		
	}

	
	public function getPolygon($delta = 0){
	    
		$points = array (
				    $this->getTopLeft($delta),
				    $this->getTopRight($delta),
				    $this->getBottomRight($delta),
				    $this->getBottomLeft($delta),
				    $this->getTopLeft($delta),
		);
		
		$outer_ring = new FOX_lineString($points);
		
		return new FOX_polygon(array($outer_ring));
		
		
	}

	
	public function getTopLeft($delta = 0){
	    
		$lat = $this->result->results[$delta]->geometry->bounds->northeast->lat;
		$lon = $this->result->results[$delta]->geometry->bounds->southwest->lng;
		
		return new FOX_point($lon, $lat);
		
	}

	public function getTopRight($delta = 0){
	    
		$lat = $this->result->results[$delta]->geometry->bounds->northeast->lat;
		$lon = $this->result->results[$delta]->geometry->bounds->northeast->lng;
		
		return new FOX_point($lon, $lat);
		
	}

	public function getBottomLeft($delta = 0){
	    
		$lat = $this->result->results[$delta]->geometry->bounds->southwest->lat;
		$lon = $this->result->results[$delta]->geometry->bounds->southwest->lng;
		
		return new FOX_point($lon, $lat);
		    
	}

	public function getBottomRight($delta = 0){
	    
		$lat = $this->result->results[$delta]->geometry->bounds->southwest->lat;
		$lon = $this->result->results[$delta]->geometry->bounds->northeast->lng;
		
		return new FOX_point($lon, $lat);
		
	}
	
}

?>