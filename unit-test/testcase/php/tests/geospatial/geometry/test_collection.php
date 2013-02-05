<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - GEOSPATIAL GEOMETRY - COLLECTION
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


class geometry_collection extends RAZ_testCase {
    
 	
	function setUp() {

		parent::setUp();			
	}
	
	
	// Abtract: Standard
	// -----------------
	
	function test_area(){}
	function test_boundary(){}
	function test_centroid(){}
	function test_length(){}
	function test_y(){}
	function test_x(){}
	function test_numGeometries(){}
	function test_geometryN(){}
	function test_startPoint(){}
	function test_endPoint(){}
	function test_isRing(){}
	function test_isClosed(){}
	function test_numPoints(){}
	function test_pointN(){}
	function test_exteriorRing(){}
	function test_numInteriorRings(){}
	function test_interiorRingN(){}
	function test_dimension(){}
	function test_equals(){}
	function test_isEmpty(){}
	function test_isSimple(){}

	// Abtract: Non-Standard
	// ---------------------
	
	function getBBox(){}
	function asArray(){}
	function getPoints(){}
	function explode(){}
	function greatCircleLength(){} 
	function haversineLength(){}

	// Public: Standard -- Common to all geometries
	// --------------------------------------------
	
	function test_SRID(){}
	function test_setSRID(){}
	function test_envelope(){}
	function test_geometryType(){}
	
	// Public: Non-Standard -- Common to all geometries
	// ------------------------------------------------
	
	function test_out(){}

	// Public: Aliases
	// ---------------
	
	function test_getCentroid(){}
	function test_getArea(){}
	function test_getX(){}
	function test_getY(){}
	function test_getGeos(){}
	function test_getGeomType(){}
	function test_getSRID(){}
	function test_asText(){}
	function test_asBinary(){}
	
	// Public: GEOS Only Functions
	// ---------------------------
	
	function test_geos(){}
	function test_setGeos(){}
	function test_pointOnSurface(){}	
	function test_equalsExact(){}
	function test_relate(){}
	function test_checkValidity(){}
	function test_buffer(){}
	function test_intersection(){}
	function test_convexHull(){}
	function test_difference(){}
	function test_symDifference(){}
	function test_union(){}
	function test_simplify(){}
	function test_disjoint(){}
	function test_touches(){}
	function test_intersects(){}
	function test_crosses(){}
	function test_within(){}
	function test_contains(){}
	function test_overlaps(){}
	function test_covers(){}
	function test_coveredBy(){}
	function test_distance(){}
	function test_hausdorffDistance(){}
	function test_project(){}
	
	// Public - Placeholders
	// ---------------------
	
	function test_hasZ(){}
	function test_is3D(){}
	function test_isMeasured(){}
	function test_coordinateDimension(){}
	function test_z(){}
	function test_m(){}
	
	
	
	function tearDown() {

		parent::tearDown();
	}	
	
}

?>