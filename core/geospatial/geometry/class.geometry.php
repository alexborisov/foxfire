<?php

/**
 * FOXFIRE GEOMETRY BASE CLASS
 * Provides base methods for geometry objects
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

abstract class FOX_geometry {
    
    
	private   $geos = null;
	protected $srid = null;
	protected $geom_type;
	
	// ============================================================================================================ //	

	// Abtract: Standard
	// -----------------
	
	abstract public function area();
	abstract public function boundary();
	abstract public function centroid();
	abstract public function length();
	abstract public function y();
	abstract public function x();
	abstract public function numGeometries();
	abstract public function geometryN($n);
	abstract public function startPoint();
	abstract public function endPoint();
	abstract public function isRing();            // Missing dependancy
	abstract public function isClosed();          // Missing dependancy
	abstract public function numPoints();
	abstract public function pointN($n);
	abstract public function exteriorRing();
	abstract public function numInteriorRings();
	abstract public function interiorRingN($n);
	abstract public function dimension();
	abstract public function equals($geom);
	abstract public function isEmpty();
	abstract public function isSimple();

	// Abtract: Non-Standard
	// ---------------------
	
	abstract public function getBBox();
	abstract public function asArray();
	abstract public function getPoints();
	abstract public function explode();
	abstract public function greatCircleLength(); // Meters
	abstract public function haversineLength(); // Degrees


	// Public: Standard -- Common to all geometries
	// --------------------------------------------
	
	public function SRID(){
	    
		return $this->srid;
	}

	public function setSRID($srid){
	    
		if( $this->geos() ){

			$this->geos()->setSRID($srid);
		}

		$this->srid = $srid;
	    
	}

	public function envelope(){
	    
		if( $this->isEmpty() ){

			return new FOX_polygon();
		}

		if( $this->geos() ){

			return FOX_geo::geosToGeometry($this->geos()->envelope());
		}

		$bbox = $this->getBBox();

		$points = array (
				    new FOX_point($bbox['maxx'],$bbox['miny']),
				    new FOX_point($bbox['maxx'],$bbox['maxy']),
				    new FOX_point($bbox['minx'],$bbox['maxy']),
				    new FOX_point($bbox['minx'],$bbox['miny']),
				    new FOX_point($bbox['maxx'],$bbox['miny']),
		);

		$outer_boundary = new FOX_lineString($points);

		return new FOX_polygon(array($outer_boundary));
		
	}

	public function geometryType(){
	    
		return $this->geom_type;
	}

	
	// Public: Non-Standard -- Common to all geometries
	// ------------------------------------------------

	// $this->out($format, $other_args);
	
	public function out() {
	    
		$args = func_get_args();

		$format = array_shift($args);
		$type_map = FOX_geo::getAdapterMap();
		$processor_type = $type_map[$format];
		$processor = new $processor_type();

		array_unshift($args, $this);
		$result = call_user_func_array(array($processor, 'write'), $args);

		return $result;
		
	}


	// Public: Aliases
	// ---------------
	
	public function getCentroid(){
	    
		return $this->centroid();
	}

	public function getArea(){
	    
		return $this->area();
	}

	public function getX() {
	    return $this->x();
	}

	public function getY() {
	    
		return $this->y();
	}

	public function getGeos(){
	    
		return $this->geos();
	}

	public function getGeomType(){
	    
		return $this->geometryType();
	}

	public function getSRID(){
	    
		return $this->SRID();
	}

	public function asText(){
	    
		return $this->out('wkt');
	}

	public function asBinary(){
	    
		return $this->out('wkb');
	}

	// Public: GEOS Only Functions
	// ---------------------------
	
	public function geos(){
	    
		// If it's already been set, just return it

		if( $this->geos && FOX_geo::geosInstalled() ){

			return $this->geos;
		}
		// It hasn't been set yet, generate it

		if( FOX_geo::geosInstalled() ){

			$reader = new GEOSWKBReader();
			$this->geos = $reader->readHEX($this->out('wkb',true));
		}
		else {
			$this->geos = false;
		}

		return $this->geos;
	    
	}

	public function setGeos($geos){
	    
		$this->geos = $geos;
	}

	public function pointOnSurface() {
	    
		if( $this->geos() ){

			return FOX_geo::geosToGeometry($this->geos()->pointOnSurface());
		}
	    
	}

	
	public function equalsExact(FOX_geometry $geometry) {
	    
		if( $this->geos() ){
		    
			return $this->geos()->equalsExact($geometry->geos());
		}
	    
	}

	
	public function relate(FOX_geometry $geometry, $pattern = null) {
	    
		if( $this->geos() ){
		    
			if($pattern){
			    
				return $this->geos()->relate($geometry->geos(), $pattern);
			}
			else {
				return $this->geos()->relate($geometry->geos());
			}
		}
		
	}

	public function checkValidity(){
	    
		if($this->geos()){
		    
			return $this->geos()->checkValidity();
		}
		
	}

	public function buffer($distance) {
	    
		if($this->geos()){
		    
			return FOX_geo::geosToGeometry($this->geos()->buffer($distance));
		}
		
	}

	public function intersection(FOX_geometry $geometry) {
	    
		if($this->geos()){
			return FOX_geo::geosToGeometry($this->geos()->intersection($geometry->geos()));
		}
		
	}

	public function convexHull(){
	    
		if ($this->geos()) {
			return FOX_geo::geosToGeometry($this->geos()->convexHull());
		}
	}

	public function difference(FOX_geometry $geometry) {
	    
		if ($this->geos()) {
			return FOX_geo::geosToGeometry($this->geos()->difference($geometry->geos()));
		}
	}

	public function symDifference(FOX_geometry $geometry) {
	    
		if ($this->geos()) {
			return FOX_geo::geosToGeometry($this->geos()->symDifference($geometry->geos()));
		}
	}

	// Can pass in a geometry or an array of geometries
	public function union(FOX_geometry $geometry) {
	    
		if($this->geos()){
		    
			if( is_array($geometry) ){
			    
				$geom = $this->geos();
				
				foreach( $geometry as $item ){
				    
					$geom = $geom->union($item->geos());
				}
				
				return FOX_geo::geosToGeometry($geom);
			}
			else {
				return FOX_geo::geosToGeometry($this->geos()->union($geometry->geos()));
			}
		}
		
	}

	public function simplify($tolerance, $preserveTopology=false){
	    
		if ($this->geos()) {
			return FOX_geo::geosToGeometry($this->geos()->simplify($tolerance, $preserveTopology));
		}
		
	}

	public function disjoint(FOX_geometry $geometry){
	    
		if($this->geos()){
			return $this->geos()->disjoint($geometry->geos());
		}
	}

	public function touches(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->touches($geometry->geos());
		}
	}

	public function intersects(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->intersects($geometry->geos());
		}
	}

	public function crosses(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->crosses($geometry->geos());
		}
	}

	public function within(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->within($geometry->geos());
		}
	}

	public function contains(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->contains($geometry->geos());
		}
	}

	public function overlaps(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->overlaps($geometry->geos());
		}
	}

	public function covers(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->covers($geometry->geos());
		}
	}

	public function coveredBy(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->coveredBy($geometry->geos());
		}
	}

	public function distance(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->distance($geometry->geos());
		}
	}

	public function hausdorffDistance(FOX_geometry $geometry){
	    
		if ($this->geos()) {
			return $this->geos()->hausdorffDistance($geometry->geos());
		}
	}

	public function project(FOX_geometry $point, $normalized = null){
	    
		if ($this->geos()) {
			return $this->geos()->project($point->geos(), $normalized);
		}
	}

	
	// Public - Placeholders
	// ---------------------
	
	public function hasZ(){
	    
		// FOX_geo does not support Z values at the moment
		return false;
	}

	public function is3D(){
	    
		// FOX_geo does not support 3D geometries at the moment
		return false;
	}

	public function isMeasured(){
	    
		// FOX_geo does not yet support M values
		return false;
	}

	public function coordinateDimension(){
	    
		// FOX_geo only supports 2-dimentional space
		return 2;
	}

	public function z(){
	    
		// FOX_geo only supports 2-dimentional space
		return null;
	}

	public function m(){
	    
		// FOX_geo only supports 2-dimentional space
		return null;
	}
	

}

?>