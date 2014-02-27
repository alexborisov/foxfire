<?php

/**
 * FOXFIRE DEBUGGING TOOLS CLASS
 * Simplifies debugging by helping output data to the console, web browser, and files
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Debug
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_debug {


	/**
         * Dumps the contents of an array of mixed nested arrays and other data types to a
	 * string, for printing within the context of a larger error message.
         *
         * @version 1.0
         * @since 1.0
	 *
         * @param bool/int/float/string/array/object $data | variable or object to dump
         * @return string containing contents
         */

	public static function dumpToString($data){

		ob_start();
		print_r($data);

		$output = ob_get_clean();

		return $output;
	}


	/**
         * Creates a file in the plugin's root directory, and logs the contents of $data to
         * it. If the file already exists, its contents will be overwritten.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param bool/int/float/string/array/object $data | variable or object to dump
         * @return text file containing contents of object
         */

	public static function printToFile($data){

		if( !is_array($data) && !is_object($data) ){

			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $data\n", LOCK_EX);
		}
		else {

			ob_start();
			print_r($data);
			$output = ob_get_clean();

			// Because we could be printing huge blocks of data, we add separator lines between them

			$separator = "\n==============================================================";

			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $separator\n", LOCK_EX);
			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $output\n", LOCK_EX);
			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $separator\n", LOCK_EX);
		}

	}


	/**
         * Creates a file in the plugin's root directory, and logs the contents of $data to
         * it. If the file already exists, the contents of $data will be appended to the file.
         *
         * @version 1.0
         * @since 1.0
	 *
         * @param bool/int/float/string/array/object $data | variable or object to dump
         * @return text file containing contents of object
         */

	public static function addToFile($data){

		if( !is_array($data) && !is_object($data) ){

			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $data\n", FILE_APPEND);
		}
		else {

			ob_start();
			print_r($data);
			$output = ob_get_clean();

			// Because we could be printing huge blocks of data, we add separator lines between them

			$separator = "\n==============================================================";

			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $separator\n", FILE_APPEND);
			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $output", FILE_APPEND);
			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), date("Y-m-d H:i:s") . " : $separator\n", FILE_APPEND);
		}

	}

	/**
         * Same as addToFile, without the date added to each line
         *
         * @version 1.0
         * @since 1.0
	 *
         * @param bool/int/float/string/array/object $data | variable or object to dump
         * @return text file containing contents of object
         */

	public static function addToFilenoDate($data){

		if( !is_array($data) && !is_object($data) ){

			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), " $data\n", FILE_APPEND);
		}
		else {

			ob_start();
			print_r($data);
			$output = ob_get_clean();

			// Because we could be printing huge blocks of data, we add separator lines between them

			$separator = "\n==============================================================";

			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), " $separator\n", FILE_APPEND);
			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), " $output", FILE_APPEND);
			@file_put_contents((dirname(__FILE__)."/bp_media_log.txt"), " $separator\n", FILE_APPEND);
		}

	}



	/**
	 * Dumps an entire object or array to a html based page in human-readable format.
	 *
         * @version 1.0
         * @since 1.0
	 * @author http://ca2.php.net/manual/en/function.var-dump.php#92594
	 * @param array/object &$var | Variable to be dumped
	 * @param string $info | Text to add to dumped variable html block, when dumping multiple variables.
	 */

	public static function dump(&$var, $info=FALSE){


            // If the function is being called by code running at the command line, do
            // a simple var_dump()
            // ========================================================================
            if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])){

                    var_dump($var);

            }
            // Otherwise, wrap the output in HTML so it prints properly in a browser
            // ========================================================================
            else {
                    $scope = false;
                    $prefix = 'unique';
                    $suffix = 'value';

                    if($scope){
                            $vals = $scope;
                    }
                    else{
                            $vals = $GLOBALS;
                    }

                    $old = $var;
                    $new = $prefix.rand().$suffix;
                    $var = $new;

                    $vname = FALSE;

                    foreach($vals as $key => $val){

                            if($val === $new){
                                    $vname = $key;
                            }
                    }

                    $var = $old;

                    $pre =  "<pre style='margin: 0px 0px 10px 0px; display: block; background: white; color: black; font-family:";
                    $pre .= " Verdana; border: 1px solid #cccccc; padding: 5px; font-size: 10px; line-height: 13px; white-space:nowrap;'>";

                    if($info){

                            $pre .= "<b style='color: red;'>$info:</b><br>";
                    }

                    echo($pre);

                    self::do_dump($var, '$'.$vname);

                    echo "</pre>";

            }

	}


        /**
         * Recursive iterator function used by FOX_debug::dump()
         *
         * @version 1.0
         * @since 1.0
         * @author http://ca2.php.net/manual/en/function.var-dump.php#92594
         */

        public static function do_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL) {

                $do_dump_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
                $reference = $reference.$var_name;
                $keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';

                if( is_array($var) && isset($var[$keyvar]) ){

                    $real_var = &$var[$keyvar];
                    $real_name = &$var[$keyname];
                    $type = ucfirst(gettype($real_var));

                    echo "$indent$var_name <span style='color:#a2a2a2'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";

                }
                else {

                        $var = array($keyvar => $var, $keyname => $reference);
                        $avar = &$var[$keyvar];

                        $type = ucfirst(gettype($avar));

                        if($type == "String") $type_color = "<span style='color:green'>";
                        elseif($type == "Integer") $type_color = "<span style='color:red'>";
                        elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
                        elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
                        elseif($type == "NULL") $type_color = "<span style='color:black'>";

                        if( is_array($avar) ){

                            $count = count($avar);
                            echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br>$indent(<br>";
                            $keys = array_keys($avar);
                            foreach($keys as $name)
                            {
                                $value = &$avar[$name];
                                self::do_dump($value, "['$name']", $indent.$do_dump_indent, $reference);
                            }
                            echo "$indent)<br>";
                        }
                        elseif(is_object($avar)) {

                            echo "$indent$var_name <span style='color:#a2a2a2'>$type</span><br>$indent(<br>";
                            foreach($avar as $name=>$value) self::do_dump($value, "$name", $indent.$do_dump_indent, $reference);
                            echo "$indent)<br>";
                        }
                        elseif(is_int($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
                        elseif(is_string($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color\"$avar\"</span><br>";
                        elseif(is_float($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
                        elseif(is_bool($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
                        elseif(is_null($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
                        else echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $avar<br>";

                        $var = $var[$keyvar];
                }
        }


	/**
	 * Calculates the difference between two objects or arrays and renders them to a html
	 * based page in human-readable format. Currently only works reliably on arrays.
	 *
         * @version 1.0
         * @since 1.0
	 * @author http://ca2.php.net/manual/en/function.var-dump.php#92594
	 *
	 * @param array/object &$from | From object
	 * @param array/object &$to | To object
	 */

	public static function diff(&$from_ref, &$to_ref, $granularity=2){


		$from = self::string_dump($from_ref, '$');
		$to = self::string_dump($to_ref, '$');

		$pre =  "<pre style='margin: 0px 0px 10px 0px; display: block; background: white; color: black; font-family:";
		$pre .= " Verdana; border: 1px solid #cccccc; padding: 5px; font-size: 10px; line-height: 13px; white-space:pre;'>";

		echo $pre;

		$start_time = gettimeofday(true);

		$granularityStacks = array(
					    FOX_fineDiff::$paragraphGranularity,
					    FOX_fineDiff::$sentenceGranularity,
					    FOX_fineDiff::$wordGranularity,
					    FOX_fineDiff::$characterGranularity
		);

		$diff = new FOX_fineDiff($from, $to, $granularityStacks[$granularity]);
		$rendered_diff = $diff->renderDiffToHTML();

		?>
		    <div class="panecontainer" style="white-space:nowrap;">
			    <div id="htmldiff">
				    <div class="pane" style="white-space:nowrap;"><?php echo $rendered_diff; ?></div>
			    </div>
		    </div>

		<?php

	}


	/**
         * Recursive iterator function used by FOX_debug::diff()
         *
         * @version 1.0
         * @since 1.0
         * @author http://ca2.php.net/manual/en/function.var-dump.php#92594
	 *
	 * @param array/object &$from | From object
	 * @param reference &$to | To object
	 * @param string $result | String to echo in browser
         */

	public static function string_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL) {

		$result = "";

		$do_dump_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
		$reference = $reference . $var_name;
		$keyvar = 'the_do_dump_recursion_protection_scheme';
		$keyname = 'referenced_object_name';

		if( is_array($var) && isset($var[$keyvar]) ){

		    $real_var = &$var[$keyvar];
		    $real_name = &$var[$keyname];
		    $type = ucfirst(gettype($real_var));

		    $result .= "$indent$var_name <span style='color:#a2a2a2'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";

		}
		else {

			$var = array($keyvar => $var, $keyname => $reference);
			$avar = &$var[$keyvar];

			$type = ucfirst(gettype($avar));

			if($type == "String") $type_color = "<span style='color:green'>";
			elseif($type == "Integer") $type_color = "<span style='color:red'>";
			elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
			elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
			elseif($type == "NULL") $type_color = "<span style='color:black'>";

			if( is_array($avar) ){

			    $count = count($avar);
			    $result .=  "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br>$indent(<br>";
			    $keys = array_keys($avar);
			    foreach($keys as $name)
			    {
				$value = &$avar[$name];
				$result .= self::string_dump($value, "['$name']", $indent.$do_dump_indent, $reference);
			    }
			    $result .= "$indent)<br>";
			}
			elseif(is_object($avar)) {

			    $result .= "$indent$var_name <span style='color:#a2a2a2'>$type</span><br>$indent(<br>";
			    foreach($avar as $name=>$value) self::string_dump($value, "$name", $indent.$do_dump_indent, $reference);
			    $result .= "$indent)<br>";
			}
			elseif(is_int($avar)) $result .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
			elseif(is_string($avar)) $result .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color\"$avar\"</span><br>";
			elseif(is_float($avar)) $result .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
			elseif(is_bool($avar)) $result .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
			elseif(is_null($avar)) $result .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
			else $result .= "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $avar<br>";

			$var = $var[$keyvar];
		}

		return $result;
	}


	/**
	 * Formats a FoxFire soft-error object for printing at the command prompt
	 *
         * @version 1.0
         * @since 1.0
	 * @param array $error | FoxFire error array
	 * @return string $result | Error array formatted for printing
	 */

	public static function formatError_print($error, $depth=null) {

		if($depth === 0){

			return $error;
		}
		elseif( $error !== null){

			$depth--;
		}

		$result = '';

		if($error === false){

			return "FOX_debug:: Passed FALSE";
		}
		elseif($error === null){

			return "FOX_debug:: Passed NULL";
		}
		elseif( !is_array($error) ){

			return $error;
		}
		else {

			$result .= "\n\n ========================== \n";

			foreach( $error as $key => $val ){

				if( $key != "child"){

					if( is_array($val) || is_object($val)){

						$result .= "\n[" . $key . "] => " . self::dumpToString($val);
					}
					else {
						$result .= "\n[" . $key . "] => " . $val;
					}
				}
			}
			unset($key, $val);

			if( $error['child'] ){

				$result .= self::formatError_print($error['child'], $depth);
			}
			else {

				$result .= "\n\n ========================== \n";
			}

			return $result;
		}

	}

	/**
	 * Dumps the output of PHP_INFO minus the CSS.
	 *
         * @version 1.0
         * @since 1.0
	 * @return string $info | PHP_INFO
	 */

	public static function php_info_dump(){

		ob_start();
		phpinfo(INFO_GENERAL);

		$info = ob_get_contents();
		ob_end_clean();

		$info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);

		echo $info;

	}



} // End of class FOX_debug

?>