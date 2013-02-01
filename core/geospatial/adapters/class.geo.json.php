<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - GEOJSON
 * Reads and writes data stored in GeoJSON format. Note that it will always return a GeoJSON geometry. This
 * means that if you pass it a feature, it will return the geometry of that feature and strip everything else.
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

class FOX_geoJSON extends FOX_geoAdapter {
    
	/**
	* Given an object or a string, return a Geometry
	*
	* @param mixed $input The GeoJSON string or object
	*
	* @return object Geometry
	*/
	public function read($input){
	    
	    
		if(is_string($input)){
		    
			$input = json_decode($input);
		}
		
		if(!is_object($input)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid input parameter. Must be an object.",
				'data'=>$input,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if(!is_string($input->type)){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid input parameter 'type' specifier. Must be a string.",
				'data'=>$input,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    
		}

		// Check to see if it's a FeatureCollection
		if($input->type == 'FeatureCollection'){
		    
			$geoms = array();
			
			foreach( $input->features as $feature ){
			    
				$geoms[] = $this->read($feature);
			}
			
			return FOX_geo::geometryReduce($geoms);
		}

		// Check to see if it's a Feature
		if($input->type == 'Feature'){
		    
			return $this->read($input->geometry);
		}

		// It's a geometry - process it
		return $this->objToGeom($input);
		
	}

	private function objToGeom($obj){
	    
		$type = $obj->type;

		if($type == 'GeometryCollection'){
		    
			return $this->objToGeometryCollection($obj);
		}
		$method = 'arrayTo' . $type;
		
		return $this->$method($obj->coordinates);
		
	}

	private function arrayToPoint($array){
	    
		return new FOX_point($array[0], $array[1]);
	}

	private function arrayToLineString($array){
	    
		$points = array();
		
		foreach($array as $comp_array){
		    
			$points[] = $this->arrayToPoint($comp_array);
		}
		
		return new FOX_lineString($points);
		
	}

	private function arrayToPolygon($array){
	    
		$lines = array();
		
		foreach( $array as $comp_array ){
		    
			$lines[] = $this->arrayToLineString($comp_array);
		}
		
		return new FOX_polygon($lines);
		
	}

	private function arrayToMultiPoint($array){
	    
		$points = array();
		foreach ($array as $comp_array){
		    $points[] = $this->arrayToPoint($comp_array);
		}
		return new MultiPoint($points);
	}

	private function arrayToMultiLineString($array){
	    
		$lines = array();
		
		foreach($array as $comp_array){
		    
			$lines[] = $this->arrayToLineString($comp_array);
		}
		
		return new FOX_multiLineString($lines);
		
	}

	private function arrayToMultiPolygon($array){
	    
		$polys = array();
		
		foreach ($array as $comp_array){
		    
			$polys[] = $this->arrayToPolygon($comp_array);
		}
		
		return new FOX_multiPolygon($polys);
		
	}

	private function objToGeometryCollection($obj){
	    
		$geoms = array();
		
		if(empty($obj->geometries)){		    
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid GeoJSON. GeometryCollection contains no component geometries.",
				'data'=>$obj,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    
		}
		
		foreach( $obj->geometries as $comp_object ){
		    
			$geoms[] = $this->objToGeom($comp_object);
		}
		
		return new FOX_geometryCollection($geoms);
		
	}

	/**
	* Serializes an object into a geojson string
	*
	*
	* @param Geometry $obj The object to serialize
	*
	* @return string The GeoJSON string
	*/
	public function write(FOX_geometry $geometry, $return_array = false){
	    
		if($return_array){
		    
			return $this->getArray($geometry);
		}
		else {
			return json_encode($this->getArray($geometry));
		}
		
	}

	public function getArray($geometry){
	    
	    
		if($geometry->getGeomType() == 'GeometryCollection'){
		    
			$component_array = array();

			foreach ($geometry->components as $component){
			    
				$component_array[] = array(
				    'type' => $component->geometryType(),
				    'coordinates' => $component->asArray(),
				);
			}
			
			return array(
				    'type'=> 'GeometryCollection',
				    'geometries'=> $component_array,
			);
		}
		else {
			return array(
				    'type'=> $geometry->getGeomType(),
				    'coordinates'=> $geometry->asArray(),
			);
		}
		
	}
	
}

?>