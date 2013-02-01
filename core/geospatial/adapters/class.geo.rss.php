<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - GEORSS
 * Reads and writes data stored in GeoRSS format.
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

class FOX_geoRSS extends FOX_geoAdapter {
    
    
	private $namespace = false;
	private $nss = ''; // Name-space string. eg 'georss:'
	
	// ============================================================================================================ //
	

	/**
	* Read GeoRSS string into geometry objects
	*
	* @param string $georss - an XML feed containing geoRSS
	*
	* @return Geometry|GeometryCollection
	*/
	public function read($gpx){
	    
		return $this->geomFromText($gpx);
		
	}

	/**
	* Serialize geometries into a GeoRSS string.
	*
	* @param Geometry $geometry
	*
	* @return string The georss string representation of the input geometries
	*/
	public function write(FOX_geometry $geometry, $namespace = false){
	    
		if($namespace){
		    $this->namespace = $namespace;
		    $this->nss = $namespace.':';    
		}
		
		return $this->geometryToGeoRSS($geometry);
		
	}

	public function geomFromText($text){
	    
		// Change to lower-case, strip all CDATA, and de-namespace
		$text = strtolower($text);
		$text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s','',$text);

		// Load into DOMDOcument
		$xmlobj = new DOMDocument();
		@$xmlobj->loadXML($text);
		
		if($xmlobj === false){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid GeoRSS text string",
				'data'=>$text,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    
		}

		$this->xmlobj = $xmlobj;
		
		try {
			$geom = $this->geomFromXML();
		} 
		catch(FOX_exception $child){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::geomFromXML()",
				'data'=>$text,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));			
		} 

		return $geom;
		
	}

	protected function geomFromXML(){
	    
		$geometries = array();
		
		$geometries = array_merge($geometries, $this->parsePoints());
		$geometries = array_merge($geometries, $this->parseLines());
		$geometries = array_merge($geometries, $this->parsePolygons());
		$geometries = array_merge($geometries, $this->parseBoxes());
		$geometries = array_merge($geometries, $this->parseCircles());

		if(empty($geometries)){		   
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid or empty GeoRSS data",
				'data'=>$geometries,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    
		}

		return geoPHP::geometryReduce($geometries); 
		
	}

	protected function getPointsFromCoords($string){
	    
		$coords = array();
		$latlon = explode(' ',$string);
		
		foreach($latlon as $key => $item){
		    
			// It's a latitude
			if(!($key % 2)){
				
				$lat = $item;
			}
			// It's a longitude
			else {				
				$lon = $item;
				$coords[] = new FOX_point($lon, $lat);
			}
		}
		
		return $coords;
		
	}

	protected function parsePoints(){
	    
		$points = array();
		$pt_elements = $this->xmlobj->getElementsByTagName('point');
		
		foreach($pt_elements as $pt){
		    
			$point_array = $this->getPointsFromCoords(trim($pt->firstChild->nodeValue));
			$points[] = $point_array[0];
		}
		
		return $points;
		
	}

	protected function parseLines(){
	    
		$lines = array();
		$line_elements = $this->xmlobj->getElementsByTagName('line');
		
		foreach ($line_elements as $line){
		    
			$components = $this->getPointsFromCoords(trim($line->firstChild->nodeValue));
			$lines[] = new LineString($components);
		}
		
		return $lines;
		
	}

	protected function parsePolygons(){
	    
		$polygons = array();
		$poly_elements = $this->xmlobj->getElementsByTagName('polygon');
		
		foreach( $poly_elements as $poly ){
		    
		    if($poly->hasChildNodes()){
			
			    $points = $this->getPointsFromCoords(trim($poly->firstChild->nodeValue));
			    $exterior_ring = new FOX_lineString($points);
			    $polygons[] = new FOX_polygon(array($exterior_ring));
		    }
		    else {
			    // It's an EMPTY polygon
			    $polygons[] = new FOX_polygon(); 
		    }
		}
		
		return $polygons;
		
	}

	// Boxes are rendered into polygons
	protected function parseBoxes(){
	    
		$polygons = array();
		$box_elements = $this->xmlobj->getElementsByTagName('box');
		
		foreach ($box_elements as $box){
		    
			$parts = explode(' ',trim($box->firstChild->nodeValue));

			$components = array(
					    new FOX_point($parts[3], $parts[2]),
					    new FOX_point($parts[3], $parts[0]),
					    new FOX_point($parts[1], $parts[0]),
					    new FOX_point($parts[1], $parts[2]),
					    new FOX_point($parts[3], $parts[2]),
			);

			$exterior_ring = new FOX_lineString($components);
			$polygons[] = new FOX_polygon(array($exterior_ring));		    
		}
		
		return $polygons;
	
	}

	// Circles are rendered into points
	// @@TODO: Add good support once we have circular-string geometry support
	protected function parseCircles(){
	    
		$points = array();
		$circle_elements = $this->xmlobj->getElementsByTagName('circle');
		
		foreach( $circle_elements as $circle ){
		    
			$parts = explode(' ',trim($circle->firstChild->nodeValue));
			$points[] = new FOX_point($parts[1], $parts[0]);
		}
		
		return $points;
		
	}

	protected function geometryToGeoRSS($geom){
	    
	    
		$type = strtolower($geom->getGeomType());
		
		switch($type){

			case "point" : {

				return $this->pointToGeoRSS($geom);

			} break;
		    
			case "linestring" : {

				return $this->linestringToGeoRSS($geom);

			} break;
		    
			case "polygon" : {

				return $this->polygonToGeoRSS($geom);

			} break;
		    
			case 'multipoint':
			case 'multilinestring':
			case 'multipolygon':
			case 'geometrycollection': {

				return $this->collectionToGeoRSS($geom);

			} break;
		    
			default :   {
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Unknown geometry type",
					'data'=>$geom,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			}
		    
		}		
		
	}
	
	

	private function pointToGeoRSS($geom){
	    
		return '<'.$this->nss.'point>'.$geom->getY().' '.$geom->getX().'</'.$this->nss.'point>';
	}


	private function linestringToGeoRSS($geom){
	    
		$output = '<'.$this->nss.'line>';
		
		foreach( $geom->getComponents() as $k => $point ){
		    
			$output .= $point->getY().' '.$point->getX();
			
			if( $k < ($geom->numGeometries() - 1) ){
			    
				$output .= ' ';
			}
		}
		
		$output .= '</'.$this->nss.'line>';
		
		return $output;
		
	}

	private function polygonToGeoRSS($geom){
	    
		$output = '<'.$this->nss.'polygon>';
		$exterior_ring = $geom->exteriorRing();
		
		foreach( $exterior_ring->getComponents() as $k => $point ){
		    
			$output .= $point->getY().' '.$point->getX();
			
			if( $k < ($exterior_ring->numGeometries() - 1) ){
				$output .= ' ';
			}
		}
		
		$output .= '</'.$this->nss.'polygon>';
		
		return $output;
		
	}

	public function collectionToGeoRSS($geom){
	    
		$georss = '<'.$this->nss.'where>';
		$components = $geom->getComponents();
		
		foreach( $components as $component ){
		    
			$georss .= $this->geometryToGeoRSS($component);
		}

		$georss .= '</'.$this->nss.'where>';

		return $georss;
		
	}

}

?>