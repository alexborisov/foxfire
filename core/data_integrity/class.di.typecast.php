<?php

/**
 * FOXFIRE DATA TYPE CONVERSION CLASS CLASS
 * Simplifies interchange between PHP and SQL data types across 32-bit and 64-bit platforms.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

class FOX_cast {


	/**
         * Converts an SQL data type to a PHP data type, returning the converted value.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param bool/int/float/string &$value | reference to input value
         * @param string $in_type | name of SQL data type $value is formatted as
         * @param string $out_type | name of PHP data type to convert $value to
	 *
         * @return bool/int/float/string/array/object | converted output value
         */

	public function SQLToPHP($value, $in_type, $out_type){


		switch($out_type){

			case "bool" : {

				return (bool)$value;

			} break;

			case "int" : {

				if($in_type == "date"){

					$date = explode('-', $value);

					// These values *must* be manually cast as ints, because
					// all values returned by the db are cast as strings

					$year = (int)$date[0];
					$month = (int)$date[1];
					$day = (int)$date[2];

					// WARNING: do not use mktime() - it "automatically compensates for daylight savings time" to 
					// where the server is located, shifting the timestamp by 3600 seconds. All of our dates are
					// stored referenced to GMT.
					
					return gmmktime(0, 0, 0, $month, $day, $year);

				}
				elseif($in_type == "datetime"){

					$full = explode(' ', $value);
					$date = explode('-', $full[0]);
					$time = explode(':', $full[1]);

					// These values *must* be manually cast as ints, because
					// all values returned by the db are cast as strings

					$year = (int)$date[0];
					$month = (int)$date[1];
					$day = (int)$date[2];

					$hour = (int)$time[0];
					$minute = (int)$time[1];
					$second = (int)$time[2];

					return gmmktime($hour, $minute, $second, $month, $day, $year);

				}
				else {
					return (int)$value;
				}

			} break;

			case "float" : {

				return (float)$value;

			} break;

			case "string" : {

				// If you try to cast NULL to (string) it becomes "0"
				if($value !== null){
				    
					return (string)$value;
				}
				else {
					return "";
				}
				
			} break;

			case "serialize" : {

				// If you try to cast NULL to (string) it becomes "0"
				if($value !== null){

					return unserialize($value);
				}
				else {
					return null;
				}				

			} break;

			case "array" : {

				if( $value === null ){	
				    
					return null;
				}
				elseif($in_type == "point"){
				    
					// Typical input: POINT(23.1179 15.223)

					$space_offset = stripos($value, " ");

					$lat_start = 6;
					$lat_len = $space_offset - $lat_start;

					$lon_start = $space_offset + 1;
					$lon_len = strlen($value) - ($space_offset + 1);

					$result = array(
							'lat'=> (float)substr($value, $lat_start, $lat_len),
							'lon'=> (float)substr($value, $lon_start, $lon_len),
					);

					return $result;
				    
				}
				elseif($in_type == "polygon"){			

					return "FAIL";
				}
				else {
					return unserialize($value);
				}

			} break;

			case "object" : {

				return unserialize($value);

			} break;

			default : {

				$class_name = get_class($this);
			
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Called with missing or invalid out_type",
					'data'=>array("class_name"=>$class_name, "value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}


		} // ENDOF: switch($out_type)

	}


	/**
         * Converts a PHP data type to an SQL data type, returning the converted value.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param bool/int/float/string $value | input value
         * @param string $in_type | name of PHP data type $value is formatted as
         * @param string $out_type | name of SQL data type to convert $value to
	 *
         * @return bool/int/float/string | converted output value
         */

	public function PHPToSQL($value, $in_type, $out_type){


		switch($out_type){

			case "tinyint" : {  // 8 bit integer			    
				return (int)$value;
			} break;

			case "smallint" : { // 16 bit integer
				return (int)$value;
			} break;

			case "mediumint" : { // 24 bit integer
				return (int)$value;
			} break;

			case "int" : { // 32 bit integer
				return (int)$value;
			} break;

			case "bigint" : { // 64 bit integer

				// Since 32-bit systems can't print this properly, we cast it as a string
				return (string)$value;
			} break;

			case "float" : { // 32 bit float
				return (float)$value;
			} break;

			case "double" : { // 64 bit float
				return (float)$value;
			} break;

			case "char" : {	// Fixed-length string of up 65,535 8-bit chars. Note UTF8 uses 24 bits per char
					// so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
				    
					return serialize($value);
				}
				else{
					return (string)$value;
				}

			} break;

			case "varchar" : { // Variable-length string of up 65,535 8-bit chars. Note UTF8 uses 24 bits per char
					   // so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
				    
					return serialize($value);
				}
				else{
					return (string)$value;
				}

			} break;

			case "text" : {	    // Variable-length string of up 65,535 8-bit chars. Note UTF8 uses 24 bits per char
					    // so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
				    
					return serialize($value);
				}
				else{
					return (string)$value;
				}

			} break;

			case "mediumtext" : {	// Variable-length string of up 16,777,215 8-bit chars. Note UTF8 uses 24 bits per char
						// so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
				    
					return serialize($value);
				}
				else{
					return (string)$value;
				}

			} break;

			case "longtext" : {	// Variable-length string of up 4,294,967,295 8-bit chars. Note UTF8 uses 24 bits per char
						// so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
				    
					return serialize($value);
				}
				else{
					return (string)$value;
				}

			} break;

			case "date" : {		// Date, formatted as "9999-12-31"

				if( $in_type == "int" ){
				    
					return gmdate( "Y-m-d", (int)$value);
				}
				else{
					return $value;
				}

			} break;

			case "datetime" : {	// Date and time, formatted as "9999-12-31 23:59:59"

				if( $in_type == "int" ){
				    
					return gmdate( "Y-m-d H:i:s", (int)$value);
				}
				else{				    				    
					return $value;
				}

			} break;
			
			case "point" : {	// GIS Point, formatted as array('lat'=>X, 'lon'=>Y)			    
					
				if( $in_type != "array" ) {
				    
					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"GIS data points must use 'array' as input type",
						'data'=>array("value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}					

				if( !empty($value) ){
				    
					if( !FOX_sUtil::keyExists('lat', $value) || !FOX_sUtil::keyExists('lon', $value)){

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Called with malformed input array",
							'data'=>array("value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));										
					}

					$result = "POINTFROMTEXT('POINT(" . $value['lat'] . " " . $value['lon'] . ")')";	
				}
				else {
					$result = 'NULL';				    
				}				
				
				return $result;
			

			} break;
			
			case "polygon" : {	// GIS Polygon, formatted as array( 0=>array('lat'=>X, 'lon'=>Y), 1=>array('lat'=>X, 'lon'=>Y))
			
				// NOTE: as per the OpenGIS standard for WKT polygon representation, polygons must be "closed" by defining 
				// a last point with the exact same lat/lon as the first point. If you fail to do this, the polygon will be
				// automatically set to NULL by the database because it isn't valid.
			    
				if( $in_type != "array" ) {
				    
					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Called with non-array input type",
						'data'=>array("value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));
				}

				$result = "PolygonFromText('POLYGON((";
				
				$points_left = count($value) - 1;
				
				if($points_left < 3){
				    
					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"A polygon definition must contain at least four points. Three to define the plane and a 4th to close it.",
						'data'=>array("value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>null
					));														    
				}
				
				foreach( $value as $point ){
				    
					if( !FOX_sUtil::keyExists('lat', $point) || !FOX_sUtil::keyExists('lon', $point)){

						throw new FOX_exception( array(
							'numeric'=>5,
							'text'=>"Called with malformed input array",
							'data'=>array("value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
							'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							'child'=>null
						));										
					}

					$result .= $value['lat'] . " " . $value['lon'];	
					
					if($points_left){
					    
						$result .= ", ";
						$points_left --;
					}
				}
				unset($point);
				
				$result .= "))')";
				
				return $result;
			

			} break;				

			default : {
			
				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Called with missing or invalid out_type",
					'data'=>array("value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}


		} // END switch($out_type)


	}



	/**
         * Converts the data contained in a query result to PHP data types
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $format | query result format. "var"|"col"|"row_object"
         * @param int/float/string/array &$data | reference to data returned by the query
	 * @param array $types | PHP data type for each column
	 *	=> ARR @param string '' | column name
	 *	    => VAL @param string $type | PHP data type to format column value as
	 *	    => VAL @param string $type | PHP data type to format column value as
	 *
         * @return bool/int/float/string/array/object | converted output value
         */

	public function queryResult($format, $data, $types){

		// Handle null queries
		if(!$data){
			return;
		}

		switch($format){

			case "var" : {

				// Get the data types for the first key in the array
				$temp = array_keys($types);
				$key = $temp[0];
				$in_type = $types[$key]["sql"];
				$out_type = $types[$key]["php"];

				return self::SQLToPHP($data, $in_type, $out_type);

			} break;

			case "col" : {

				// Get the data types for the first key in the array
				$temp = array_keys($types);
				$key = $temp[0];
				$in_type = $types[$key]["sql"];
				$out_type = $types[$key]["php"];

				$result = array();
				
				// Cast each value in the array
				foreach($data as $val){

					 $result[] = self::SQLToPHP($val, $in_type, $out_type);
				}
				unset($val);
				
				return $result;

			} break;

			case "row_object" : {

				$result = new stdClass();
				
				foreach($types as $var => $cast){

					$in_type = $cast["sql"];
					$out_type = $cast["php"];
					
					if( ($in_type == 'point') || ($in_type == 'polygon') ){
					    
						// GIS data types come back from the db with the wrapped
						// column name as the key. Example: "AsText(colName)"
					    
						$wrapped_var = "AsText(".$var.")";
						$result->{$var} = self::SQLToPHP($data->{$wrapped_var}, $in_type, $out_type);
					}
					else {
						$result->{$var} = self::SQLToPHP($data->{$var}, $in_type, $out_type);
					}
				}
				
				return $result;


			} break;

			case "array_object" : {

				$result = array();
			    
				foreach($data as $index => $fake_var){

					$result[$index] = new stdClass();
				    
					foreach($types as $var => $cast){

						$in_type = $cast["sql"];
						$out_type = $cast["php"];
						
						if( ($in_type == 'point') || ($in_type == 'polygon') ){
							
							// GIS data types come back from the db with the wrapped
							// column name as the key. Example: "AsText(colName)"

							$wrapped_var = "AsText(".$var.")";
							$result[$index]->{$var} = self::SQLToPHP($data[$index]->{$wrapped_var}, $in_type, $out_type);													
						}
						else {							
							$result[$index]->{$var} = self::SQLToPHP($data[$index]->{$var}, $in_type, $out_type);
						}											
					}
					unset($var, $cast);
				}
				unset($index, $fake_var);
				
				return $result;				

			} break;
			
			default : {
			    
				$class_name = get_class($this);
			
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Called with missing or invalid format",
					'data'=>array("class_name"=>$class_name, "format"=>$format),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			    
			    
			}


		} 

	}
	
	
} // End of class FOX_cast

?>