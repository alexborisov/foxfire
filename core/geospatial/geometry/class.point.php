<?php

/**
 * FOXFIRE POINT CLASS
 * Represents a single point
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

class FOX_point extends FOX_geometry {
    
    
	public $coords = array(2);
	
	protected $geom_type = 'Point';
	protected $dimention = 2;

	// ============================================================================================================ //
	
	
	/**
	* Constructor
	*
	* @param numeric $x The x coordinate (or longitude)
	* @param numeric $y The y coordinate (or latitude)
	* @param numeric $z The z coordinate (or altitude) - optional
	*/
	public function __construct($x, $y, $z=null){
	    
		// Basic validation on x and y
		if( !is_numeric($x) || !is_numeric($y) || ( ($z !== null)  && !is_numeric($z) ) ){		   
			
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid parameter. X, Y, and Z must be numeric.",
				'data'=>array('x'=>$x, 'y'=>$y, 'z'=>$z),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			
		}

		// Check to see if this is a 3D point
		if($z !== null){		    
			
			$this->dimention = 3;
		}

		// Convert to floatval in case they are passed in as a string or integer etc.
		$x = floatval($x);
		$y = floatval($y);
		$z = floatval($z);

		// Add poitional elements
		if($this->dimention == 2){
		    
			$this->coords = array($x, $y);
		}
		
		if($this->dimention == 3){
		    
			$this->coords = array($x, $y, $z);
		}
		
	}

	/**
	* Get X (longitude) coordinate
	*
	* @return float The X coordinate
	*/
	public function x(){
	    
		return $this->coords[0];
	}

	/**
	* Returns Y (latitude) coordinate
	*
	* @return float The Y coordinate
	*/
	public function y(){
	    
		return $this->coords[1];
	}

	/**
	* Returns Z (altitude) coordinate
	*
	* @return float The Z coordinate or null is not a 3D point
	*/
	public function z(){
	    
		if($this->dimention == 3){
		    
			return $this->coords[2];
		}
		else {
			return null;
		}
		
	}

	// A point's centroid is itself
	public function centroid(){
	    
		return $this;
	}

	public function getBBox(){
	    
		return array(
		    'maxy' => $this->getY(),
		    'miny' => $this->getY(),
		    'maxx' => $this->getX(),
		    'minx' => $this->getX(),
		);
	}

	public function asArray($assoc = false){
	    
		return $this->coords;
	}

	public function area(){
	    
		return 0;
	}

	public function length(){
	    
		return 0;
	}

	public function greatCircleLength(){
	    
		return 0;
	}

	public function haversineLength(){
	    
		return 0;
	}

	// The boundary of a point is itself
	public function boundary(){
	    
		return $this;
	}

	public function dimension(){
	    
		return 0;
	}

	public function isEmpty(){
	    
		return false;
	}

	public function numPoints(){
	    
		return 1;
	}

	public function getPoints(){
	    
		return array($this);
	}

	public function equals($geometry){
	    
		return ($this->x() == $geometry->x() && $this->y() == $geometry->y());
	}

	public function isSimple(){
	    
		return true;
	}

	// Not valid for this geometry type
	
	public function numGeometries()    { return null; }
	public function geometryN($n)      { return null; }
	public function startPoint()       { return null; }
	public function endPoint()         { return null; }
	public function isRing()           { return null; }
	public function isClosed()         { return null; }
	public function pointN($n)         { return null; }
	public function exteriorRing()     { return null; }
	public function numInteriorRings(){ return null; }
	public function interiorRingN($n)  { return null; }
	public function pointOnSurface()   { return null; }
	public function explode()          { return null; }
	
	
}

?>