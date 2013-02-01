<?php

/**
 * FOXFIRE GEOSPATIAL ADAPTER - GPX
 * Reads and writes data to GPX format
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

class FOX_gpx extends FOX_geoAdapter {
    
    
	private $namespace = false;
	private $nss = ''; // Name-space string. eg 'georss:'
	
	// ============================================================================================================ //
	

	/**
	* Read GPX string into geometry objects
	*
	* @param string $gpx A GPX string
	*
	* @return Geometry|GeometryCollection
	*/
	public function read($gpx){
	    
		return $this->geomFromText($gpx);
	}

	/**
	* Serialize geometries into a GPX string.
	*
	* @param Geometry $geometry
	*
	* @return string The GPX string representation of the input geometries
	*/
	public function write(FOX_geometry $geometry, $namespace = false){
	    
		if($geometry->isEmpty()){
			return null;
		}
		
		if($namespace){
			$this->namespace = $namespace;
			$this->nss = $namespace.':';    
		}
		
		return '<'.$this->nss.'gpx creator="geoPHP" version="1.0">'.$this->geometryToGPX($geometry).'</'.$this->nss.'gpx>';
		
	}

	public function geomFromText($text){
	    
		// Change to lower-case and strip all CDATA
		$text = strtolower($text);
		$text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s','',$text);

		// Load into DOMDocument
		$xmlobj = new DOMDocument();
		@$xmlobj->loadXML($text);
		
		if($xmlobj === false){
			
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid GPX data string",
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
				'text'=>"Cannot parse GPX data string",
				'data'=>$text,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));			
		} 

		return $geom;
		
	}

	public function geomFromXML(){
	    
		$geometries = array();
		
		$geometries = array_merge($geometries, $this->parseWaypoints());
		$geometries = array_merge($geometries, $this->parseTracks());
		$geometries = array_merge($geometries, $this->parseRoutes());

		if(empty($geometries)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid GPX data string",
				'data'=>$geometries,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		return FOX_geo::geometryReduce($geometries); 
		
	}

	public function childElements($xml, $nodename = ''){
	    
		$children = array();
		
		foreach($xml->childNodes as $child){
		    
			if($child->nodeName == $nodename){
			    
				$children[] = $child;
			}
		}
		
		return $children;
		
	}

	public function parseWaypoints(){
	    
		$points = array();
		$wpt_elements = $this->xmlobj->getElementsByTagName('wpt');
		
		foreach($wpt_elements as $wpt){
		    
			$lat = $wpt->attributes->getNamedItem("lat")->nodeValue;
			$lon = $wpt->attributes->getNamedItem("lon")->nodeValue;
			$points[] = new FOX_point($lon, $lat);
		}
		
		return $points;
		
	}

	public function parseTracks(){
	    
		$lines = array();
		$trk_elements = $this->xmlobj->getElementsByTagName('trk');
		
		foreach( $trk_elements as $trk ){
		    
			$components = array();
			
			foreach( $this->childElements($trk, 'trkseg') as $trkseg ){
			    
				foreach( $this->childElements($trkseg, 'trkpt') as $trkpt ){
				    
					$lat = $trkpt->attributes->getNamedItem("lat")->nodeValue;
					$lon = $trkpt->attributes->getNamedItem("lon")->nodeValue;
					
					$components[] = new FOX_point($lon, $lat);
				}
			}
			if($components){$lines[] = new FOX_lineString($components);}
		}
		
		return $lines;
		
	}

	public function parseRoutes(){
	    
		$lines = array();
		$rte_elements = $this->xmlobj->getElementsByTagName('rte');
		
		foreach($rte_elements as $rte){
		    
			$components = array();
			
			foreach( $this->childElements($rte, 'rtept') as $rtept ){
			    
				$lat = $rtept->attributes->getNamedItem("lat")->nodeValue;
				$lon = $rtept->attributes->getNamedItem("lon")->nodeValue;
				
				$components[] = new FOX_point($lon, $lat);
			}
			
			$lines[] = new FOX_lineString($components);
		}
		
		return $lines;
		
	}

	public function geometryToGPX($geom){
	    
		$type = strtolower($geom->getGeomType());	
		
		switch($type){

			case "point" : {
				return $this->pointToGPX($geom);
			} break;
		    
			case "linestring" : {
				return $this->linestringToGPX($geom);
			} break;
		    
			case "polygon" :
			case 'multipoint':
			case 'multilinestring':
			case 'multipolygon': 
			case 'geometrycollection':	{

				return $this->collectionToGPX($geom);

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

	public function pointToGPX($geom){
	    
		return '<'.$this->nss.'wpt lat="'.$geom->getY().'" lon="'.$geom->getX().'" />';
	}

	public function linestringToGPX($geom){
	    
		$gpx = '<'.$this->nss.'trk><'.$this->nss.'trkseg>';

		foreach( $geom->getComponents() as $comp ){
		    
			$gpx .= '<'.$this->nss.'trkpt lat="'.$comp->getY().'" lon="'.$comp->getX().'" />';
		}

		$gpx .= '</'.$this->nss.'trkseg></'.$this->nss.'trk>';

		return $gpx;
		
	}

	public function collectionToGPX($geom){
	    
		$gpx = '';
		$components = $geom->getComponents();
		
		foreach($components as $component){
		    
			$gpx .= $this->geometryToGPX($component);
		}

		return $gpx;
		
	}

}


?>