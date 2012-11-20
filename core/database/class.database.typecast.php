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
         * Converts an SQL data type to a PHP data type. The conversion is done
	 * in-place using a reference, to help conserve memory space.
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

	public function SQLToPHP(&$value, $in_type, $out_type){


		switch($out_type){

			case "bool" : {

				$value = (bool)$value;

			} break;

			case "int" : {

				if($in_type == "date"){

					$date = explode('-', $value);

					// These values *must* be manually cast as ints, because
					// all values returned by the db are cast as strings

					$year = (int)$date[0];
					$month = (int)$date[1];
					$day = (int)$date[2];

					$value = mktime(0, 0, 0, $month, $day, $year);

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

					$value = mktime($hour, $minute, $second, $month, $day, $year);

				}
				else {

					$value = (int)$value;
				}

			} break;

			case "float" : {

				$value = (float)$value;

			} break;

			case "string" : {

				// If you try to cast NULL to (string) it becomes "0"
				if($value !== null){
					$value = (string)$value;
				}
				
			} break;

			case "serialize" : {

				// If you try to cast NULL to (string) it becomes "0"
				if($value !== null){

					$value = unserialize($value);
				}

			} break;

			case "array" : {

				$value = unserialize($value);

			} break;

			case "object" : {

				$value = unserialize($value);

			} break;

			default : {

				$class_name = get_class($this);
			
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Called with missing or invalid out_type",
					'data'=>array("class_name"=>$class_name, "value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}


		} // ENDOF: switch($out_type)


		// Return the cast result
		// ======================================
		return $value;

	}


	/**
         * Converts a PHP data type to an SQL data type
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
				$value = (int)$value;
			} break;

			case "smallint" : { // 16 bit integer
				$value = (int)$value;
			} break;

			case "mediumint" : { // 24 bit integer
				$value = (int)$value;
			} break;

			case "int" : { // 32 bit integer
				$value = (int)$value;
			} break;

			case "bigint" : { // 64 bit integer

				// TODO: on 32 bit systems, this needs to be cast as (float)
				$value = (int)$value;
			} break;

			case "float" : { // 32 bit float
				$value = (float)$value;
			} break;

			case "double" : { // 64 bit float
				$value = (float)$value;
			} break;

			case "char" : {	// Fixed-length string of up 65,535 8-bit chars. Note UTF8 uses 24 bits per char
					// so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
					$value = serialize($value);
				}
				else{
					$value = (string)$value;
				}

			} break;

			case "varchar" : { // Variable-length string of up 65,535 8-bit chars. Note UTF8 uses 24 bits per char
					   // so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
					$value = serialize($value);
				}
				else{
					$value = (string)$value;
				}

			} break;

			case "text" : {	    // Variable-length string of up 65,535 8-bit chars. Note UTF8 uses 24 bits per char
					    // so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
					$value = serialize($value);
				}
				else{
					$value = (string)$value;
				}

			} break;

			case "mediumtext" : {	// Variable-length string of up 16,777,215 8-bit chars. Note UTF8 uses 24 bits per char
						// so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
					$value = serialize($value);
				}
				else{
					$value = (string)$value;
				}

			} break;

			case "longtext" : {	// Variable-length string of up 4,294,967,295 8-bit chars. Note UTF8 uses 24 bits per char
						// so max length will be 1/3 of this length.

				if( ($in_type == "array") || ($in_type == "object") || ($in_type == "serialize") ){
					$value = serialize($value);
				}
				else{
					$value = (string)$value;
				}

			} break;

			case "date" : {		// Date, formatted as "9999-12-31"

				if( $in_type == "int" ){
					$value = gmdate( "Y-m-d", (int)$value);
				}
				else{
					$value = $value;
				}

			} break;

			case "datetime" : {	// Date and time, formatted as "9999-12-31 23:59:59"

				if( $in_type == "int" ){
					$value = gmdate( "Y-m-d H:i:s", (int)$value);
				}
				else{
					$value = $value;
				}

			} break;

			default : {

				$class_name = get_class($this);
			
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Called with missing or invalid out_type",
					'data'=>array("class_name"=>$class_name, "value"=>$value, "in_type"=>$in_type, "out_type"=>$out_type),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}


		} // END switch($out_type)


		// Return the cast result
		// ======================================
		return $value;

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

	public function queryResult($format, &$data, $types){

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

				self::SQLToPHP($data, $in_type, $out_type);

			} break;

			case "col" : {

				// Get the data types for the first key in the array
				$temp = array_keys($types);
				$key = $temp[0];
				$in_type = $types[$key]["sql"];
				$out_type = $types[$key]["php"];

				// Cast each value in the array
				foreach($data as $key => $val){

					 self::SQLToPHP($data[$key], $in_type, $out_type);
				}


			} break;

			case "row_object" : {

				foreach($types as $var => $cast){

					$in_type = $cast["sql"];
					$out_type = $cast["php"];
					self::SQLToPHP($data->{$var}, $in_type, $out_type);
				}


			} break;

			case "array_object" : {

				foreach($data as $index => $row){

					foreach($types as $var => $cast){

						$in_type = $cast["sql"];
						$out_type = $cast["php"];
						self::SQLToPHP($data[$index]->{$var}, $in_type, $out_type);
					}
				}

			} break;
			
			default : {
			    
				$class_name = get_class($this);
			
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Called with missing or invalid format",
					'data'=>array("class_name"=>$class_name, "format"=>$format),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    
			    
			}


		} 

	}
	
	
} // End of class FOX_cast

?>