<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - WELL KNOWN TEXT
 * Reads and writes data to Well Known Text format
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

class FOX_wkt extends FOX_geoAdapter {
  
	/**
	* Read WKT string into geometry objects
	*
	* @param string $WKT A WKT string
	*
	* @return Geometry
	*/
	public function read($wkt){
	    
		$wkt = trim($wkt);

		// If it contains a ';', then it contains additional SRID data
		if( strpos($wkt,';') ){
		    
			$parts = explode(';', $wkt);
			$wkt = $parts[1];
			$eparts = explode('=',$parts[0]);
			$srid = $eparts[1];
		}
		else {
			$srid = null;
		}

		// If geos is installed, then we take a shortcut and let it parse the WKT
		if(FOX_geo::geosInstalled()){
		    
			$reader = new GEOSWKTReader();
			
			if($srid){
			    
				$geom = FOX_geo::geosToGeometry($reader->read($wkt));
				$geom->setSRID($srid);
				
				return $geom;
			}
			else { 
				return FOX_geo::geosToGeometry($reader->read($wkt));
			}
		}
		
		$wkt = str_replace(', ', ',', $wkt);

		// For each geometry type, check to see if we have a match at the
		// beggining of the string. If we do, then parse using that type
		
		foreach (FOX_geo::geometryList() as $geom_type){
		    
			$wkt_geom = strtoupper($geom_type);
			
			if( strtoupper(substr($wkt, 0, strlen($wkt_geom))) == $wkt_geom ){
			    
				$data_string = $this->getDataString($wkt, $wkt_geom);
				$method = 'parse'.$geom_type;

				if($srid){
				    
					$geom = $this->$method($data_string);
					$geom->setSRID($srid);
					
					return $geom;
				}
				else { 
					return $this->$method($data_string);
				}
			}
		}
		
	}

	private function parsePoint($data_string){
	    
		$data_string = $this->trimParens($data_string);
		$parts = explode(' ',$data_string);
		
		return new FOX_point($parts[0], $parts[1]);
		
	}

	private function parseLineString($data_string){
	    
	    
		$data_string = $this->trimParens($data_string);

		// If it's marked as empty, then return an empty line
		if($data_string == 'EMPTY'){
		    
			return new FOX_lineString();
		}

		$parts = explode(',',$data_string);
		$points = array();
		
		foreach( $parts as $part ){
		    
			$points[] = $this->parsePoint($part);
		}
		
		return new FOX_lineString($points);
		
	}

	private function parsePolygon($data_string){
	    
		$data_string = $this->trimParens($data_string);

		// If it's marked as empty, then return an empty polygon
		if($data_string == 'EMPTY'){ 
		    
			return new FOX_polygon();
		}

		$parts = explode('),(',$data_string);
		$lines = array();
		
		foreach( $parts as $part ){
		    
			if( !$this->beginsWith($part,'(') ){
			    
				$part = '(' . $part;
			}
			
			if( !$this->endsWith($part,')') ){
			    
				$part = $part . ')';
			}
			
			$lines[] = $this->parseLineString($part);
		}
		
		return new FOX_polygon($lines);
		
	}

	private function parseMultiPoint($data_string){
	    
	    
		$data_string = $this->trimParens($data_string);

		// If it's marked as empty, then return an empty MutiPoint
		if($data_string == 'EMPTY'){
		    
			return new FOX_multiPoint();
		}

		$parts = explode(',',$data_string);
		$points = array();
		
		foreach( $parts as $part ){
		    
			$points[] = $this->parsePoint($part);
		}
		
		return new FOX_multiPoint($points);
		
	}

	
	private function parseMultiLineString($data_string){
	    
		$data_string = $this->trimParens($data_string);

		// If it's marked as empty, then return an empty multi-linestring
		if($data_string == 'EMPTY'){
		    
			return new FOX_multiLineString();
		}

		$parts = explode('),(',$data_string);
		$lines = array();
		
		foreach( $parts as $part ){
		    
			// Repair the string if the explode broke it
			if(!$this->beginsWith($part,'(')){
			    
				$part = '(' . $part;
			}
			
			if(!$this->endsWith($part,')')){
			    
				$part = $part . ')';
			}
			
			$lines[] = $this->parseLineString($part);
		}
		
		return new FOX_multiLineString($lines);
		
	}

	private function parseMultiPolygon($data_string){
	    
	    
		$data_string = $this->trimParens($data_string);

		// If it's marked as empty, then return an empty multi-polygon
		if($data_string == 'EMPTY'){
		    
			return new FOX_multiPolygon();
		}

		$parts = explode(')),((',$data_string);
		$polys = array();
		
		foreach( $parts as $part ){
		    
			// Repair the string if the explode broke it
			if( !$this->beginsWith($part,'((') ){
			    
				$part = '((' . $part;
			}
			
			if( !$this->endsWith($part,'))') ){
			    
				$part = $part . '))';
			}
			
			$polys[] = $this->parsePolygon($part);
		}
		
		return new FOX_multiPolygon($polys);
		
	}

	
	private function parseGeometryCollection($data_string){
	    
	    
		$data_string = $this->trimParens($data_string);

		// If it's marked as empty, then return an empty geom-collection
		if($data_string == 'EMPTY'){
		    
			return new FOX_geometryCollection();
		}

		$geometries = array();
		$str = preg_replace('/,\s*([A-Za-z])/', '|$1', $data_string);
		$components = explode('|', trim($str));

		foreach( $components as $component ){
		    
			$geometries[] = $this->read($component);
		}
		
		return new GeometryCollection($geometries);
		
	}

	protected function getDataString($wkt, $type){
	    
		return substr($wkt, strlen($type));
	}

	/**
	* Trim the parenthesis and spaces
	*/
	protected function trimParens($str){
	    
		$str = trim($str);

		// We want to only strip off one set of parenthesis
		if($this->beginsWith($str, '(')){
		    
			return substr($str,1,-1);
		}
		else return $str;
	}

	protected function beginsWith($str, $char){
	    
		if(substr($str,0,strlen($char)) == $char) return true;
		else return false;
	}

	protected function endsWith($str, $char){
	    
	    
		if( substr($str,(0 - strlen($char))) == $char ){
		    
			return true;
		}
		else {
			return false;
		}		
	}

	/**
	* Serialize geometries into a WKT string.
	*
	* @param Geometry $geometry
	*
	* @return string The WKT string representation of the input geometries
	*/
	public function write(FOX_geometry $geometry){
	    
	    
		// If geos is installed, then we take a shortcut and let it write the WKT
		if(FOX_geo::geosInstalled()){
		    
			$writer = new GEOSWKTWriter();
			$writer->setTrim(true);
			
			return $writer->write($geometry->geos());
		}

		if($geometry->isEmpty()){
		    
			return strtoupper($geometry->geometryType()).' EMPTY';
		}
		else {
			$data = $this->extractData($geometry);
			
			if($data){
		    
				return strtoupper($geometry->geometryType()).' ('.$data.')';
			}
			else {
				return null;
			}
		}
		
	}

	/**
	* Extract geometry to a WKT string
	*
	* @param Geometry $geometry A Geometry object
	*
	* @return string
	*/
	public function extractData($geometry){
	    
		
		$type = strtolower($geometry->geometryType());
		
		switch($type){

			case "point" : {

				return $geometry->getX().' '.$geometry->getY();

			} break;
		    
			case "linestring" : {

				$parts = array();
				
				foreach( $geometry->getComponents() as $component ){
				    
					$parts[] = $this->extractData($component);
				}
				
				return implode(', ', $parts);

			} break;
		    
			case "polygon" :
			case 'multipoint':
			case 'multilinestring':
			case 'multipolygon': {

				$parts = array();
				
				foreach( $geometry->getComponents() as $component ){
				    
					$parts[] = '('.$this->extractData($component).')';
				}
				
				return implode(', ', $parts);

			} break;
		    
			case 'geometrycollection': {

				$parts = array();
				
				foreach( $geometry->getComponents() as $component ){
				    
					$parts[] = strtoupper($component->geometryType()).' ('.$this->extractData($component).')';
				}
				
				return implode(', ', $parts);

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
				
	}
	
}

?>