<?php

/**
 * FOXFIRE POLYGON CLASS
 * A polygon is a plane figure that is bounded by a closed path composed of a finite 
 * sequence of straight line segments
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

class FOX_polygon extends FOX_collection {
    
    
	protected $geom_type = 'Polygon';
	
	
	// ============================================================================================================ //
	

	public function area($exterior_only=false, $signed=false){
	    
	    
		if($this->isEmpty()){

			return 0;
		}

		if($this->geos() && $exterior_only == false){

			return $this->geos()->area();
		}

		$exterior_ring = $this->components[0];
		$pts = $exterior_ring->getComponents();

		$c = count($pts);
		
		if((int)$c == '0'){ 
		    
			return null;
		}
		
		$a = '0';
		
		foreach($pts as $k => $p){
		    
			$j = ($k + 1) % $c;
			$a = $a + ($p->getX() * $pts[$j]->getY()) - ($p->getY() * $pts[$j]->getX());
		}

		if($signed){ 
		    
			$area = ($a / 2);
		}
		else {
			$area = abs(($a / 2));
		}

		if($exterior_only == true){
		    
			return $area;
		}
		
		foreach ($this->components as $delta => $component){
		    
			if($delta != 0){

				$inner_poly = new FOX_polygon(array($component));
				$area -= $inner_poly->area();
			}		    
		}
		
		return $area;
		
	}

	public function centroid(){
	    
		if($this->isEmpty()) return null;

		if($this->geos()){
		    
			return FOX_geo::geosToGeometry($this->geos()->centroid());
		}

		$exterior_ring = $this->components[0];
		$pts = $exterior_ring->getComponents();

		$c = count($pts);
		
		if((int)$c == '0'){
		    
			return null;
		}
		
		$cn = array('x' => '0', 'y' => '0');
		$a = $this->area(true, true);

		// If this is a polygon with no area. Just return the first point.
		
		if($a == 0){
		    
			return $this->exteriorRing()->pointN(1);
		}

		foreach($pts as $k => $p){
		    
			$j = ($k + 1) % $c;
			$P = ($p->getX() * $pts[$j]->getY()) - ($p->getY() * $pts[$j]->getX());
			$cn['x'] = $cn['x'] + ($p->getX() + $pts[$j]->getX()) * $P;
			$cn['y'] = $cn['y'] + ($p->getY() + $pts[$j]->getY()) * $P;
		}

		$cn['x'] = $cn['x'] / ( 6 * $a);
		$cn['y'] = $cn['y'] / ( 6 * $a);

		$centroid = new FOX_point($cn['x'], $cn['y']);
		
		return $centroid;
		
	}
	

	    /**
		* Find the outermost point from the centroid
		*
		* @returns Point The outermost point
		*/
	public function outermostPoint(){
	    
		$centroid = $this->getCentroid();

		$max = array('length' => 0, 'point' => null);

		foreach($this->getPoints() as $point){
		    
			$lineString = new FOX_lineString(array($centroid, $point));

			if($lineString->length() > $max['length']){
			    
				$max['length'] = $lineString->length();
				$max['point'] = $point;
			}
		}

		return $max['point'];
		
	}

	
	public function exteriorRing(){
	    
		if($this->isEmpty()){
		    
			return new FOX_lineString();
		}
		else {
		    
			return $this->components[0];
		}
	}

	
	public function numInteriorRings(){
	    
	    
		if($this->isEmpty()){
		    
			return 0;
		}
		else {
			return $this->numGeometries()-1;
		}
		
	}

	public function interiorRingN($n){
	    
		return $this->geometryN($n+1);
	}

	
	public function dimension(){
	    
		if($this->isEmpty()){
		    
			return 0;
		}
		else {
			return 2;
		}
	}

	public function isSimple(){
	    
	    
		if($this->geos()){
		    
			return $this->geos()->isSimple();
		}

		$segments = $this->explode();

		foreach ($segments as $i => $segment){
		    
			foreach ($segments as $j => $check_segment){
			    
				if($i != $j){
				    
					if($segment->lineSegmentIntersect($check_segment)){
						return false;
					}
				}
			}
		}
		
		return true;
		
	}

	// Not valid for this geometry type
	// --------------------------------
	public function length(){ return null; }
	
  
}

?>