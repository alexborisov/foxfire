<?php

/**
 * FOXFIRE AJAX ROUTERS
 * This file contains all of FoxFire's AJAX routers. They determine which handler an AJAX request gets
 * routed to when it is passed to us by WordPress. Unlike the rest of the plugin, these functions have to
 * be defined procedurally (as opposed to being methods in a class) because we have to be able to attach
 * individual functions to the various WordPress JS hook functions.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage AJAX
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


/**
 * Processes the level rank list in an albumtype panel on the admin album types page
 *
 * @version 1.0
 * @since 1.0
 *
 * @return string | 'OK' on success. 'FAIL' on failure.
 */

 function fox_ajax_admin_albumTypesLevelSort($unit_test=false) {

	// Block non-admin users from running this function
	if( !is_super_admin() ){
		    die;
	}
	    
	$module_id = $_POST['module_id'];
	$type_id = $_POST['type_id'];
	$levels = $_POST['levels'];

	$module_id = (int)$module_id;
	$type_id = (int)$type_id;

	// Process level ranks
	// ==================================================
	$rank_array = array();

	foreach($levels as $rank => $type ) {

		// Remove the "row_" prefix
		$len = strlen($type);
		$type = substr($type, 4, ($len-1) );
		
		// Cast to correct data types for security
		$rank = (int)$rank;
		$type = (int)$type;

		// First key in array is 0, which is not a valid rank
		$rank_array[$type] = $rank + 1;

	}
	unset($rank, $type);
	
	// Update database
	// ==================================================
	$cls = new FOX_albumTypeLevel();
	
	try {
	    
		$cls->setRanks($module_id, $type_id, $rank_array);
		
		if(!$unit_test){
		    
			echo "OK";
			die();	// Required by WP in AJAX mode
		}
		else {
			return true;
		}		
	
	}
	catch (FOX_exception $fail) {
	    
		if(!$unit_test){
		    
			$fail->printAsText();
			die();	// Required by WP in AJAX mode
		}
		else {
			return false;
		}	    
	    
	}

 
 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_admin_albumTypesLevelSort', 'fox_ajax_admin_albumTypesLevelSort');


/**
 * Processes modal dialog forms on fox admin pages
 *
 * @version 1.0
 * @since 1.0
 *
 * @return string | Rendered HTML to display in form
 */

 function fox_ajax_admin_getDialog($unit_test=false) {

	// Block non-admin users from running this function
	if( !is_super_admin() ){
		    die;
	}
		
	$san = new FOX_sanitize();

	// Grab names from the $_POST array and sanitize them
	
	// Tree name
	// ============================================================================
	
	try {
		$tree_name = $san->classOrFunction($_POST['tree_name'], $ctrl=null);
	
	}
	catch (FOX_exception $fail) {
	    
		if(!$unit_test){
			echo "fox_ajax_admin_getDialog called with bad or missing tree name. Error: " . $fail->getAsText();
			die;
		}
		else {
			return false;
		}	    	    
	}
	
	// Class name
	// ============================================================================
	
	try {
		$class_name = $san->classOrFunction($_POST['class_name'], $ctrl=null);
	
	}
	catch (FOX_exception $fail) {
	    
		if(!$unit_test){
			echo "fox_ajax_admin_getDialog called with bad or missing class name. Error: " . $fail->getAsText();
			die;
		}
		else {
			return false;
		}	    	    
	}	
	
	// Method name
	// ============================================================================	

	try {
		$method_name = $san->classOrFunction($_POST['method_name'], $ctrl=null);
	
	}
	catch (FOX_exception $fail) {
	    
		if(!$unit_test){
			echo "fox_ajax_admin_getDialog called with bad or missing method name. Error: " . $fail->getAsText();
			die;
		}
		else {
			return false;
		}	    	    
	}


	// Include the class file
	require ( FOX_PATH_CORE . "/admin/" . $tree_name . '/ajax.dialogs.php' );

	// Instantiate the class, then call the method, passing the params array
	$cls = new $class_name();
	$result = $cls->{$method_name}($_POST['params']);

	// Return response
	if(!$unit_test){

		die();	// Required by WP in AJAX mode
	}
	else {
		return $result;
	}
	

 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_admin_getDialog', 'fox_ajax_admin_getDialog');


/**
 * Processes data sent back via modal dialog forms on fox admin pages
 *
 * @version 1.0
 * @since 1.0
 *
 * @return bool/int/string/array $echo | Response varies with form and the module that owns it
 */

 function fox_ajax_admin_putDialog($unit_test=false) {

	// Block non-admin users from running this function
	if( !is_super_admin() ){
		    die;
	}

	$san = new FOX_sanitize();

	// Grab the tree, class, and method names from the $_POST array and sanitize them
	
	try {		
		$tree_name = $san->classOrFunction($_POST['tree_name'], $ctrl=null);	
	}
	catch (FOX_exception $child) {
	    
		if(!$unit_test){
		    
			$result =  __METHOD__ . " called with bad or missing tree name. ";
			$result .= "Error: " . $child->data['data']['error'];			
			echo $result;
			die;
		}
		else {
			return false;
		}	    	    
	}
	
	try {		
		$class_name = $san->classOrFunction($_POST['class_name'], $ctrl=null);	
	}
	catch (FOX_exception $child) {
	    
		if(!$unit_test){
		    
			$result =  __METHOD__ . " called with bad or missing class name. ";
			$result .= "Error: " . $child->data['data']['error'];			
			echo $result;
			die;
		}
		else {
			return false;
		}	    	    
	}	
	
	try {		
		$method_name = $san->classOrFunction($_POST['method_name'], $ctrl=null);	
	}
	catch (FOX_exception $child) {
	    
		if(!$unit_test){
		    
			$result =  __METHOD__ . " called with bad or missing method name. ";
			$result .= "Error: " . $child->data['data']['error'];			
			echo $result;
			die;
		}
		else {
			return false;
		}	    	    
	}	
	
	// Include the class file
	require ( FOX_PATH_CORE . "/admin/" . $tree_name . '/ajax.dialogs.php' );
	require ( FOX_PATH_CORE . "/admin/" . $tree_name . '/ajax.handlers.php' );

	// Instantiate the class, then call the method, passing the params array
	$cls = new $class_name();
	$result = $cls->{$method_name}($_POST['params']);

	// Return response
	if(!$unit_test){

		die();	// Required by WP in AJAX mode
	}
	else {
		return $result;
	}


 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_admin_putDialog', 'fox_ajax_admin_putDialog');


/**
 * Checks if a WP page is in use by FoxFire or BuddyPress
 *
 * @version 1.0
 * @since 1.0
 *
 * @return bool/int/string/array $echo | Response varies with form and the module that owns it
 */

 function fox_ajax_admin_checkPageStatus($unit_test=false) {


	// Block non-admin users from running this function
	if( !is_super_admin() ){
		    die;
	}

	$san = new FOX_sanitize();

	// Grab variables from the $_POST array and sanitize them
	$page_id = $san->int($_POST['page_id'], $ctrl=null, $page_ok, $page_error);
	
	try {		
		$page_id = $san->int($_POST['page_id'], $ctrl=null);
	}
	catch (FOX_exception $child) {
	    
		if(!$unit_test){
		    
			$result =  __METHOD__ . " called with bad or missing page_id. ";
			$result .= "Error: " . $child->data['data']['error'];			
			echo $result;
			die;
		}
		else {
			return false;
		}	    	    
	}	

	$cls = new FOX_bp();
	$page_data = $cls->getPageOwner($page_id);
	
	$result = json_encode($page_data);

	// Return response
	if(!$unit_test){
		echo $result;
		die();	// Required by WP in AJAX mode
	}
	else {
		return $result;
	}


 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_admin_checkPageStatus', 'fox_ajax_admin_checkPageStatus');


/**
 * Checks if a slug is in use by FoxFire
 *
 * @version 1.0
 * @since 1.0
 *
 * @return bool/int/string/array $echo | Response varies with form and the module that owns it
 */

 function fox_ajax_admin_checkSlugStatus($unit_test=false) {


	// Block non-admin users from running this function
	if( !is_super_admin() ){
		    die;
	}

	$san = new FOX_sanitize();

	try {		
		$slug = $san->slug($_POST['slug'], $ctrl=null);
	}
	catch (FOX_exception $child) {
	    
		if(!$unit_test){
		    
			$result =  __METHOD__ . " called with bad or missing slug. ";
			$result .= "Error: " . $child->data['data']['error'];			
			echo $result;
			die;
		}
		else {
			return false;
		}	    	    
	}	

	
	try {		
		$location = $san->slug($_POST['location'], $ctrl=null);
	}
	catch (FOX_exception $child) {
	    
		if(!$unit_test){
		    
			$result =  __METHOD__ . " called with bad or missing location. ";
			$result .= "Error: " . $child->data['data']['error'];			
			echo $result;
			die;
		}
		else {
			return false;
		}	    	    
	}

	$cls = new FOX_bp();
	$page_data = $cls->getSlugOwner($location, $slug);

	$result = json_encode($page_data);


	// Return response
	if(!$unit_test){
		echo $result;
		die();	// Required by WP in AJAX mode
	}
	else {
		return $result;
	}


 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_admin_checkSlugStatus', 'fox_ajax_admin_checkSlugStatus');


/**
 * Runs unit tests on the server
 *
 * @version 1.0
 * @since 1.0
 *
 * @return bool/int/string/array $echo | Response varies with form and the module that owns it
 */

 function fox_ajax_admin_selfTest($unit_test=false) {


	// Block non-admin users from running this function
	if( !is_super_admin() ){
		    die;
	}

	$result = FOX_uTest::run();

	// Return response
	if(!$unit_test){
		echo $result;
		die();	// Required by WP in AJAX mode
	}
	else {
		return $result;
	}


 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_admin_selfTest', 'fox_ajax_admin_selfTest');
 
 
/**
 * Runs unit tests on the server
 *
 * @version 1.0
 * @since 1.0
 *
 * @return bool/int/string/array $echo | Response varies with form and the module that owns it
 */

 function fox_ajax_testData() {

	// Return response echo 

	$cls = new FOX_wp();
	$cls->setRequestStatus(200);
	
	header('Content-Type: text/javascript');
  
	define("MAX_PRICE", 100.0); // $100.00
	define("MAX_PRICE_CHANGE", 0.02); // +/- 2%

	echo '[';

	$q = trim($_GET['q']);
	
	if ($q) {
	    $symbols = explode(' ', $q);

	    for ($i=0; $i<count($symbols); $i++) {
	    $price = lcg_value() * MAX_PRICE;
	    $change = $price * MAX_PRICE_CHANGE * (lcg_value() * 2.0 - 1.0);

	    echo '{';
	    echo "\"symbol\":\"$symbols[$i]\",";
	    echo "\"price\":$price,";
	    echo "\"change\":$change";
	    echo '}';

	    if ($i < (count($symbols) - 1)) {
		echo ',';
	    }
	    }
	}

	echo ']';

	die();	// Required by WP in AJAX mode

	
 } // WP action name is "wp_ajax_" + "formActionName"
 add_action('wp_ajax_fox_ajax_testData', 'fox_ajax_testData'); 

?>